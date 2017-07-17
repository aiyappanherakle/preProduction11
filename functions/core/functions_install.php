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
* Function to print a character via javascript as a progress bar.
*
* @param	string		string
* @param        string          character to output while working
* @param        string          span id reference
*/
function print_progress_begin($str = '', $char = '.', $id = 'progressspan')
{
	flush();
	?>
	<p><?php echo $str; ?><br /><br />(<span style="color:#000; font-weight:bold" id="<?php echo $id; ?>"><?php echo $char; ?></span>)</p>
	<script type="text/javascript">
	<!--
	function print_progress()
	{
		<?php echo $id; ?>.innerText = <?php echo $id; ?>.innerText + "<?php echo $char; ?>";
		timer = setTimeout("print_progress();", 75);
	}
	if (document.all)
	{
		print_progress();
	}
	//-->
	</script>
	<?php
	flush();
}

/**
* Function to stop the characters via javascript to act like the progress bar is finished.
*/
function print_progress_end()
{
	flush();
	?>
	<script type="text/javascript">
	<!--
	if (document.all)
	{
		clearTimeout(timer);
	}
	//-->
	</script>
	<?php flush();
}

function convert_all_tables_collation($collate = 'utf8_general_ci', $charset = 'utf8')
{
        global $ilance;
        
	print_progress_begin('<b>Converting database, tables and fields to charset: ' . $charset . ' collation: ' . $collate . '</b>, please wait.', '.', 'progressspanutf8');
	
	$ilance->db->query("ALTER DATABASE `" . DB_DATABASE . "` DEFAULT CHARACTER SET $charset COLLATE $collate");
	
        $sql = $ilance->db->query("SHOW TABLES");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($tables = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        foreach ($tables AS $key => $value)
                        {
                                if (!empty($value))
                                {
                                        // #### SET TABLES TO UTF-8
                                        $ilance->db->query("ALTER TABLE $value COLLATE $collate");
                                        //echo "ALTER TABLE $value COLLATE $collate<br />";
                                        
                                        $sql2 = $ilance->db->query("SHOW FULL FIELDS FROM `$value`");
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                while ($row = $ilance->db->fetch_array($sql2, DB_ASSOC))
                                                {
                                                        // Is the field allowed to be null?
                                                        if ($row['Null'] == 'YES')
                                                        {
                                                                $nullable = 'NULL';
                                                        }
                                                        else
                                                        {
                                                                $nullable = 'NOT NULL';
                                                        }
                                                        
                                                        // Does the field default to null, a string, or nothing?
                                                        if ($row['Default'] != '')
                                                        {
                                                                $default = "DEFAULT '" . $ilance->db->escape_string($row['Default']) . "'";
                                                        }
                                                        else
                                                        {
                                                                $default = "DEFAULT ''";
                                                        }
                                                        
                                                        if (preg_match("/\bvarchar\b/i", $row['Type']) OR preg_match("/\bchar\b/i", $row['Type']) OR preg_match("/\benum\b/i", $row['Type']) OR preg_match("/\bmediumtext\b/i", $row['Type']) OR preg_match("/\btinytext\b/i", $row['Type']) OR preg_match("/\blongtext\b/i", $row['Type']) OR preg_match("/\btext\b/i", $row['Type']))
                                                        {
                                                                // #### SET TABLE FIELDS TO UTF-8
                                                                $field = $ilance->db->escape_string($row['Field']);
                                                                $ilance->db->query("ALTER TABLE `$value` CHANGE `$field` `$field` $row[Type] CHARACTER SET $charset COLLATE $collate $nullable $default");
                                                                //echo "ALTER TABLE `$value` CHANGE `$field` `$field` $row[Type] CHARACTER SET $charset COLLATE $collate $nullable $default<br />";
                                                        }
                                                }
                                        }
                                }
                        }
                }
        }
	
	// conversion complete.. next action
	print_progress_end();
	
	return "<li>All database tables and fields are now set to charset: utf8 collation: utf8_general_ci</li>";
}

/**
* Function for importing or updating language phrases in the database.
* This function will only insert new phrases and will not update existing ones.
*
* @param	integer		per page value
* @param        integer         start from phrase number
*/
function import_language_phrases($perpage = 10000, $fromphrase = 0)
{
	global $ilance;
	
	print_progress_begin('<b>Importing stock phrases from xml</b>, please wait.', '.', 'progressspan');
	
	$data = array();
	$xml = file_get_contents(DIR_SERVER_ROOT . 'install/xml/master-phrases-english.xml');
	
	if ($xml == false)
	{
		return;
	}
	
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
		$ilance->common = construct_object('api.common');
		$ilance->xml = construct_object('api.xml');
		
		$result = $ilance->xml->process_lang_xml($data, $xml_encoding);
		$installedlanguages = array();
		
		$langquery = $ilance->db->query("
			SELECT languageid
			FROM " . DB_PREFIX . "language
			WHERE languagecode = '" . $ilance->db->escape_string($result['lang_code']) . "'
		");
		if ($ilance->db->num_rows($langquery) == 0) 
		{
			print_progress_end();
			
			return "<li>We're sorry.  Language pack uploading requires the actual language to already exist within the database before you upload any new language packages.  Please retry your action using a language pack that already exists (or you can simply create the new language than retry your upload action)</li>";
		}
		
		// update language from xml data file
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
		
		$lfn = 'text_' . mb_substr($result['lang_code'], 0, 3);
		
		$phrasearray = $result['phrasearray'];
		$phrasecounttotal = count($phrasearray);
		$phraseperpg = 10000;
		$added = $updated = 0;
		
		for ($i = 0; $i < $phrasecounttotal; $i++)
		{
			$query = $ilance->db->query("
				SELECT phraseid
				FROM " . DB_PREFIX . "language_phrases
				WHERE varname = '" . trim($ilance->db->escape_string($phrasearray[$i][1])) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($query) == 0)
			{
				$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
				
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "language_phrases 
					(phrasegroup, varname, ismaster, text_original, $lfn) 
					VALUES (
					'" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
					'" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
					'1',
					'" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
					'" . $ilance->db->escape_string($phrasearray[$i][2]) . "')
				");
				
				$added++;
			}
			else
			{
				// update only the original setting value (so user can still use revert phrase to get latest)
				// additionally >> make sure the phrase group in the xml file is same otherwise move db variable to group in xml
				// just in case ILance developers moved a previous phrase into the "global" phrasegroup
				
				$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
				
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "language_phrases
					SET text_original = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
					$lfn = IF(isupdated = 0, '" . $ilance->db->escape_string($phrasearray[$i][2]) . "', $lfn),
					phrasegroup = IF(phrasegroup != '" . $ilance->db->escape_string($phrasearray[$i][0]) . "', '" . $ilance->db->escape_string($phrasearray[$i][0]) . "', phrasegroup)
					WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
				");
				$updated++;
			}
		}
		
		// finally we can update untranslated phrases from the import so they are not blank (change them if blank to the master phrase so it can be translated)
		update_untranslated_phrases_to_master();		
		
		// import complete.. next action
		print_progress_end();
		
		return "<li>$phrasecounttotal total phrases: newly added: $added, updated: $updated</li>";
	}
	else 
	{
		print_progress_end();
		
		$error_string = xml_error_string($error_code);
		return "<li>We're sorry.  There was an error with the formatting of the xml language package file [$error_string].  Please fix the problem and retry your action.</li>";
	}	
}

/**
* Function for importing or updating email templates in the database.
* This function will only insert new email templates and will not update existing ones.
*/
function import_email_templates($overwrite = true)
{
	global $ilance;
	
	print_progress_begin('<b>Importing stock email templates from xml</b>, please wait.', '.', 'progressspan2');
	
	$data = array();
	$xml = file_get_contents(DIR_SERVER_ROOT . 'install/xml/master-emails-english.xml');
	
	if ($xml == false)
	{
		return;
	}
	
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
		$ilance->common = construct_object('api.common');
		$ilance->xml = construct_object('api.xml');
		
		$result = $ilance->xml->process_email_xml($data, $xml_encoding);
		
		$query = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
			WHERE languagecode = '" . $ilance->db->escape_string($result['langcode']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($query) == 0)
		{
			print_progress_end();
			
			return "<li>We're sorry.  Your marketplace needs to already have the language <strong>" . ucfirst($result['langcode']) . "</strong> created in your system before you can import new email templates.  Please retry your action using a language that already exists</li>";
		}
		
		// move on updating each email template
		$phrasearray = $result['emailarray'];
		
		$lfn1 = 'subject_' . mb_substr($result['langcode'], 0, 3);
		$lfn2 = 'message_' . mb_substr($result['langcode'], 0, 3);
		
		$docount = count($phrasearray);
		$added = $updated = 0;
		
		for ($i = 0; $i < $docount; $i++)
		{
			$product = isset($phrasearray[$i][5]) ? $phrasearray[$i][5] : 'ilance';
			
			if (!empty($phrasearray[$i][0]) AND !empty($phrasearray[$i][1]) AND !empty($phrasearray[$i][2]) AND !empty($phrasearray[$i][3]) AND !empty($phrasearray[$i][4]))
			{
				// does email template exist?
				$sql = $ilance->db->query("
					SELECT id
					FROM " . DB_PREFIX . "email
					WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "'
				");
				if ($ilance->db->num_rows($sql) == 0)
				{
					$phrasearray[$i][0] = ilance_htmlentities($phrasearray[$i][0]);
					$phrasearray[$i][1] = ilance_htmlentities($phrasearray[$i][1]);
					$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
					
					// add new email template
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "email 
						(id, varname, subject_original, message_original, $lfn1, $lfn2, name, type, product, cansend, departmentid) 
						VALUES (
						NULL,
						'" . $ilance->db->escape_string($phrasearray[$i][4]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][1]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
						'" . $ilance->db->escape_string($phrasearray[$i][1]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][2]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][0]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][3]) . "',
						'" . $ilance->db->escape_string($product) . "',
						'" . intval($phrasearray[$i][6]) . "',
						'" . intval($phrasearray[$i][7]) . "')
					");
					
					$added++;
				}
				else
				{
					$phrasearray[$i][1] = ilance_htmlentities($phrasearray[$i][1]);
					$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
					
					$extraquery = '';
					if ($overwrite)
					{
						$extraquery .= $lfn1 . " = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',";
						$extraquery .= $lfn2 . " = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',";
					}
					
					// update only the original setting value (so user can still use revert template option to get latest template)
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "email
						SET subject_original = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
						message_original = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
						$extraquery
						name = '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
						product = '" . $ilance->db->escape_string($product) . "'
						WHERE varname = '" . trim($ilance->db->escape_string($phrasearray[$i][4])) . "'
					");
					
					$updated++;
				}
			}
		}
		
		print_progress_end();
		
		$extrainfo = '';
		if ($overwrite)
		{
			$extrainfo .= ' (overwritten from xml)';	
		}
		
		return '<li>' . $docount . ' ' . ucfirst($result['langcode']) . ' email templates, newly added: ' . $added . ', updated: ' . $updated . $extrainfo . '</li>';
	}
	else 
	{
		print_progress_end();
		$error_string = xml_error_string($error_code);
		
		return "<li>We're sorry.  There was an error with the formatting of the language xml package file [$error_string].  Please fix the problem and retry your action.</li>";
	}
}

/**
* Function for importing or updating template styles into the database.
* This function will only insert new email templates and will not update existing ones.
*/
function import_templates()
{
	global $ilance;
	
	print_progress_begin('<b>Importing stock CSS from xml</b>, please wait.', '.', 'progressspan3');
	
	// we expect the latest xml for the css definations for this release upgrade or fresh install!
	$xml = file_get_contents(DIR_SERVER_ROOT . 'install/xml/master-style.xml');
	
	if ($xml == false)
	{
		return;
	}
	
	$data = array();
	
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
		$ilance->xml = construct_object('api.xml');
		
		$result = $ilance->xml->process_style_xml($data, $xml_encoding);
		
		if ($result['ilversion'] != $ilance->config['ilversion'])
		{
			print_progress_end();
			return "<li>The version of the this template/style package <strong>" . $result['ilversion'] . "</strong> is different than the installed version of ILance <strong>".$ilance->config['ilversion']."</strong>.  The operation has aborted due to a version conflict.</li>";
		}
		
		$notice = '';
		
		$query = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "styles
			WHERE name = '" . $ilance->db->escape_string($result['name']) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($query) == 0)
		{
			// doesn't exist.. insert new style from xml
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "styles
				(styleid, name, visible, sort)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($result['name']) . "',
				'1',
				'100')
			", 0, null, __FILE__, __LINE__);
			
			$newstyleid = $ilance->db->insert_id();
			
			// set the updated XML to default
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . intval($newstyleid) . "'
				WHERE name = 'defaultstyle'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			// move onto importing each template
			$templatearray = $result['templatearray'];
			$templatecount = count($templatearray);
			$added = $updated = 0;
			
			for ($i = 0; $i < $templatecount; $i++)
			{
				// ensure "template" is not empty
				if (isset($templatearray[$i][0]) AND !empty($templatearray[$i][0]))
				{
					// add new css template
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "templates
						(tid, name, description, type, status, styleid, createdate, original, content, product, sort)
						VALUES(
						NULL,
						'" . $ilance->db->escape_string($templatearray[$i][0]) . "',
						'" . $ilance->db->escape_string($templatearray[$i][1]) . "',
						'" . $ilance->db->escape_string($templatearray[$i][2]) . "',
						'1',
						'" . intval($newstyleid) . "',
						NOW(),
						'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
						'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
						'" . $ilance->db->escape_string($templatearray[$i][3]) . "',
						'" . intval($templatearray[$i][4]) . "')
					", 0, null, __FILE__, __LINE__);
					
					$added++;
				}
			}
			
			print_progress_end();
			
			return "<li>$templatecount CSS templates, added: $added, updated: $updated</li>";
		}
		else
		{
			// xml style exists in db already .. lets update templates instead
				
			// fetch style id based on the style name being imported (hopefully we have a match!)
			$styleid = $ilance->db->fetch_field(DB_PREFIX . "styles", "name = '" . trim($ilance->db->escape_string($result['name'])) . "'", "styleid");
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "styles
				SET visible = '1'
				WHERE name = '" . trim($ilance->db->escape_string($result['name'])) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);

			// move onto updating each template
			
			$templatearray = $result['templatearray'];
			$templatecount = count($templatearray);
			$added = $updated = 0;
			
			for ($i = 0; $i < $templatecount; $i++)
			{
				// ensure the template is not empty
				if (isset($templatearray[$i][0]) AND !empty($templatearray[$i][0]))
				{
					// does css template exist?
					$sql = $ilance->db->query("
						SELECT tid
						FROM " . DB_PREFIX . "templates
						WHERE name = '" . trim($ilance->db->escape_string($templatearray[$i][0])) . "'
							AND type = '" . $ilance->db->escape_string($templatearray[$i][2]) . "'
							AND styleid = '" . $styleid . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) == 0)
					{
						// does not exist - add new css template
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "templates
							(tid, name, description, type, status, styleid, updatedate, original, content, product, sort)
							VALUES(
							NULL,
							'" . $ilance->db->escape_string($templatearray[$i][0]) . "',
							'" . $ilance->db->escape_string($templatearray[$i][1]) . "',
							'" . $ilance->db->escape_string($templatearray[$i][2]) . "',
							'1',
							'" . intval($styleid) . "',
							NOW(),
							'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
							'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
							'" . $ilance->db->escape_string($templatearray[$i][3]) . "',
							'" . intval($templatearray[$i][4]) . "')
						", 0, null, __FILE__, __LINE__);
						
						$added++;
					}
					else
					{
						// exists - update only the original setting value (so user can still use revert template option to get latest template)
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "templates
							SET original = '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
							content = '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
							type = '" . $ilance->db->escape_string($templatearray[$i][2]) . "',
							product = '" . $ilance->db->escape_string($templatearray[$i][3]) . "',
							sort = '" . intval($templatearray[$i][4]) . "',
							status = '1'
							WHERE name = '" . $ilance->db->escape_string($templatearray[$i][0]) . "'
								AND type = '" . $ilance->db->escape_string($templatearray[$i][2]) . "'
								AND styleid = '" . intval($styleid) . "'
						", 0, null, __FILE__, __LINE__);
						
						$updated++;
					}
				}
			}
			
			// set the new or updated style as the default style to ensure the latest upgraded templates make use of the newest release css efforts
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . intval($styleid) . "'
				WHERE name = 'defaultstyle'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			print_progress_end();
			
			return "<li>$templatecount CSS templates, newly added: $added, updated: $updated</li>";
		}
	}
	else
        {
		print_progress_end();
		$error_string = xml_error_string($error_code);
		
		return "<li>We're sorry.  There was an error with the formatting of the template package file [$error_string].  Please fix the problem and retry your action.</li>";
	}
}

/**
* Function for importing new master categories for a fresh installation of ILance
*/
function import_master_categories()
{
	global $ilance;
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "categories VALUES (NULL, 0, '', 1, 'Web Design', 'Web Design Service and Solutions', 1, 0, 1, 1, 1, 0, 'default', '0', '0', '', 'service', '', 0, '0.00', '0.00', 0, 0, 'lowest', '0', '0', '0', '', '', '', 1, 0, 0, 0)", 0, null, __FILE__, __LINE__);
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "categories VALUES (NULL, 0, '', 1, 'Programming', 'Programming Solutions', 1, 0, 0, 0, 0, 0, 'default', '0', '0', '', 'service', '', 0, '0.00', '0.00', 0, 0, 'lowest', '0', '0', '0', '', '', '', 1, 0, 0, 0)", 0, null, __FILE__, __LINE__);
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "categories VALUES (NULL, 0, '', 1, 'Computers', 'Buy Computers from our top sellers', 1, 0, 1, 0, 1, 0, '', '0', '0', '', 'product', '', 0, '0.00', '0.00', 0, 0, 'lowest', '0', '1', '0', '', '', '', 1, 0, 0, 0)", 0, null, __FILE__, __LINE__);
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "categories VALUES (NULL, 0, '', 1, 'Arts and Collectables', 'Buy arts and collectables from our top sellers', 1, 0, 1, 0, 1, 0, '', '0', '0', '', 'product', '', 0, '0.00', '0.00', 0, 0, 'lowest', '0', '1', '0', '', '', '', 1, 0, 0, 0)", 0, null, __FILE__, __LINE__);
	
	$ilance->categories = construct_object('api.categories');
	$ilance->categories->set_levels();
	$ilance->categories->rebuild_category_tree(0, 1, 'service', 'eng');
	$ilance->categories->rebuild_category_tree(0, 1, 'product', 'eng');
	$ilance->categories->rebuild_category_geometry_install();
	rebuild_spatial_category_indexes(false);
}

/**
* Function to converts an adjacency list to a modified preorder tree traversal
* http://articles.sitepoint.com/article/hierarchical-data-database/3
* start as: rebuild_category_tree(1, 1);
*
* @param	integer 	starting parentid (default 0)
* @param        integer         starting left (default 1)
*/
function rebuild_category_tree($parentid = 0, $left = 1)
{
	global $ilance;
	
	// the right value of this node is the left value + 1   
	$right = ($left + 1);
       
	// get all children of this node   
	$result = $ilance->db->query("
		SELECT cid
		FROM " . DB_PREFIX . "categories
		WHERE parentid = '" . intval($parentid) . "'
	");   
	while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
	{   
		// recursive execution of this function for each child of this node   
		// $right is the current right value, which is incremented by the rebuild_tree function   
		$right = rebuild_category_tree($row['cid'], $right);   
	}   
       
	// we've got the left value, and now that we've processed   
	// the children of this node we also know the right value   
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET lft = '" . intval($left) . "', rgt = '" . intval($right) . "'
		WHERE cid = '" . intval($parentid) . "'
	");
	
	// return the right value of this node + 1   
	return ($right + 1);
}

/**
* Function to build a spatial geometry index for the category system in 3.2.0
* http://stackoverflow.com/questions/1743894/mysql-optimizing-finding-super-node-in-nested-set-tree
*/
function rebuild_spatial_category_indexes($html = false)
{
	global $ilance;
	
	if ($html)
	{
		print_progress_begin('<b>Rebuilding spatial data for category table</b>, please wait.', '.', 'progressspan341');
	}
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories SET `sets` = LineString(Point(-1, lft), Point(1, rgt));
	");
	
	$ilance->db->query("
		ALTER TABLE " . DB_PREFIX . "categories MODIFY `sets` LINESTRING NOT NULL
	");
	
	$ilance->db->query("
		CREATE SPATIAL INDEX sx_categories_sets ON " . DB_PREFIX . "categories (sets)
	");
	
	if ($html)
	{
		print_progress_end();
	}
}

/**
* Function to print the installation menu header
*/
function print_install_header()
{
	$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html dir="ltr" lang="us">
<head>
<title>Installation System</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="-1">
<meta http-equiv="Cache-Control" content="no-cache">
<meta name="robot" content="noindex, nofollow">
<style id="css" type="text/css">
<!--
body
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 9pt;
	background-color: #fff;
	margin: 25px 25px 25px 25px;
	padding: 0px 25px 0px 25px;
}
table, td, th, p, li
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 9pt;
	color: #000000;
}
p, div
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 9pt;
	color: #000000;
}
.page
{
	background-color: #fff;
	color: #000000;
}
a:link
{
	color: #113456;
}
a:visited
{
	color: #113456;
}
a:hover
{
	color: #C00;
}
a:active
{
	color: #C00;
}
.highlight
{
	background: #6D8CB3;
	color: #FFFFFF;
}
.header
{
    font-family: Century Gothic, Lucida Grande, Trebuchet MS, Verdana, Sans-Serif;
    font-size: 18px;
    letter-spacing: -1px;
    font-weight: bold;
    margin: 10px 0 7px 0;
    color: #000000;
}
.panel
{
	background: #f1f1f1;
	color: #000000;
	padding: 10px;
	border: 2px outset;
}
.panelbackground
{
	background: #dedede;
	color: #000000;
}
.fieldset legend
{
	padding: 1px;
	font-size: 9pt;
	font-weight: bold;
	color: #000000;
}
.smaller
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 11px;
	color: #000000;
}
div.bluehlite
{
	padding-right: 4px;
	border-top: #5a7edc 1px solid;
	padding-left: 4px;
	padding-bottom: 4px;
	margin: 5px auto;
	padding-top: 4px;
	border-bottom: #5a7edc 1px solid;
	background-color: #fcfdff;
}
div.greenhlite
{
	padding-right: 4px;
	border-top: #83DB5A 1px solid;
	padding-left: 4px;
	padding-bottom: 4px;
	margin: 5px auto;
	padding-top: 4px;
	border-bottom: #83DB5A 1px solid;
	background-color: #fcfffa;
}
div.yellowhlite
{
	padding-right: 4px;
	border-top: #D9CE5B 1px solid;
	padding-left: 4px;
	padding-bottom: 4px;
	margin: 5px auto;
	padding-top: 4px;
	border-bottom: #D9CE5B 1px solid;
	background-color: #fffefa;
}
div.redhlite
{
	padding-right: 4px;
	border-top: #d95b5b 1px solid;
	padding-left: 4px;
	padding-bottom: 4px;
	margin: 5px auto;
	padding-top: 4px;
	border-bottom: #d95b5b 1px solid;
	background-color: #fffafa;
}
div.purplehlite
{
	padding-right: 4px;
	border-top: #d95bb7 1px solid;
	padding-left: 4px;
	padding-bottom: 4px;
	margin: 5px auto;
	padding-top: 4px;
	border-bottom: #d95bb7 1px solid;
	background-color: #fff7fd;
}
div.smaller
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 11px;
	color: #000000;
}
.buttons
{
	font-size: 12px;
    color: #333333;
    font-family: Arial, Helvetica, Verdana, sans-serif;
	font-weight: bold;
}
.buttons_smaller
{
	font-size: 10px;
    color : #333333;
    font-family: Arial, Helvetica, Verdana, sans-serif;
	font-weight: bold;
}
.input
{
   font: 10pt Verdana, Arial, Helvetica, sans-serif;
}
.textarea
{
	border: 1px inset;
	padding-left: 6px;
	font-size: 12px;
	font-weight: bold;
	width: 191px;
	color: #444444;
	padding-top: 4px;
	font-family: Arial, Helvetica, Verdana, sans-serif;
	height: 77px;
	background-color: #ffffff;
}
.pulldown
{
	font-size: 13px;
	width: 198px;
	color: #444444;
	font-family: Arial, Helvetica, Verdana, sans-serif;
	height: 24px;
}
//-->
</style>
</head>
<body>

<!-- content table -->
<div align="center">
<div class="page" style="width:790px; text-align:left">
<div style="padding:0px 25px 0px 25px"><img src="../images/default/logo.gif" border="0" alt="" />';
	
	return $html;
}

/**
* Function to print the installation menu footer
*/
function print_install_footer()
{
	$html = '<!-- / body content -->
	</div>
	</div>
	</div>
	<!-- / content area table -->
    
	<br />
	<div align="center">
	    <div class="smaller" align="center">
	    <!-- Do not remove copyright notice without branding removal -->
	    Powered by: ILance&reg; Version ' . VERSION . '<br />
	    Copyright &copy;2002 - ' . date('Y') . ', ILance Inc.
	    <!-- Do not remove copyright notice without branding removal -->
	     <br />
	    </div>
	    <br />
	</div>
	</body>
	</html>';
    
	return $html;
}

/**
* Function to construct our encryption system keys during installation
*
* @param	integer		length of key
*/
function createkey($length = 50)
{
	$alpha = '-ABCDEFGHIJ=KLMNOP-QRSTUVWXYZ-abcdefgh-ijklmnopq-rstuvwxy-z1234567890';
	
	$ran_string = '';
	for ($i = 0; $i < $length; $i++)
	{
  		$ran_string .= $alpha[rand(0,61)];
	}
	
	return $ran_string;
}

/**
* Function to create the latest fresh database schema for the version of ILance being installed.
* This function will attempt to DROP all tables before creating new ones.  This function should only
* be used during the process of a fresh installation.
*/
function create_db_schema()
{
	global $ilance;
	
	$ilance->db->query("ALTER DATABASE `" . DB_DATABASE . "` DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "");
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "abuse_reports");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "abuse_reports (
		`abuseid` INT(5) NOT NULL AUTO_INCREMENT,
		`regarding` MEDIUMTEXT,
		`username` MEDIUMTEXT,
		`email` MEDIUMTEXT,
		`itemid` INT(5) NOT NULL DEFAULT '0',
		`abusetype` ENUM('listing','bid','portfolio','profile','feedback','pmb') NOT NULL default 'listing',
		`type` VARCHAR(100) NOT NULL default '',
		`status` INT(1) NOT NULL DEFAULT '1',
		`dateadded` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (`abuseid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "abuse_reports</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "admincp_news");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "admincp_news (
		`newsid` INT(5) NOT NULL AUTO_INCREMENT,
		`content` MEDIUMTEXT,
		`datetime` DATETIME NOT NULL,
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY (`newsid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "admincp_news</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "attachment");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "attachment (
		`attachid` INT(100) UNSIGNED NOT NULL AUTO_INCREMENT,
		`attachtype` ENUM('profile','portfolio','project','itemphoto','bid','pmb','ws','kb','ads','digital','slideshow','stores','storesitemphoto','storesdigital') NOT NULL default 'profile',
		`user_id` INT(10) UNSIGNED NOT NULL default '0',
		`portfolio_id` INT(100) NOT NULL default '0',
		`project_id` INT(100) NOT NULL default '0',
		`pmb_id` INT(100) NOT NULL default '0',
		`category_id` INT(20) NOT NULL default '0',
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`thumbnail_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`filename` VARCHAR(100) NOT NULL default '',
		`filedata` LONGBLOB,
		`thumbnail_filedata` LONGBLOB,
		`filetype` VARCHAR(50) NOT NULL default '',
		`visible` INT(1) UNSIGNED NOT NULL default '0',
		`counter` SMALLINT(5) UNSIGNED NOT NULL default '0',
		`filesize` INT(10) UNSIGNED NOT NULL default '0',
		`thumbnail_filesize` INT(10) UNSIGNED NOT NULL default '0',
		`filehash` VARCHAR(32) NOT NULL default '',
		`ipaddress` VARCHAR(50) NOT NULL default '',
		`tblfolder_ref` INT(100) NOT NULL default '0',
		`exifdata` MEDIUMTEXT,
		`invoiceid` INT(10) NOT NULL default '0',
		`isexternal` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`attachid`),
		KEY filehash (`filehash`),
		INDEX (`user_id`),
		INDEX (`portfolio_id`),
		INDEX (`project_id`),
		INDEX (`category_id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "attachment</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "attachment_folder");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "attachment_folder (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(255) default NULL,
		`comments` MEDIUMTEXT,
		`p_id` INT(100) default NULL,
		`project_id` INT(10) default NULL,
		`buyer_id` INT(10) default NULL,
		`seller_id` INT(10) default NULL,
		`folder_size` INT(10) default NULL,
		`folder_type` INT(10) default NULL,
		`create_date` DATE default NULL,
		PRIMARY KEY  (`id`),
		INDEX (`name`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "attachment_folder</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "audit");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "audit (
		`logid` INT(10) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`script` VARCHAR(200) NOT NULL default '',
		`cmd` VARCHAR(250) NOT NULL default '',
		`subcmd` VARCHAR(250) NOT NULL default '',
		`do` VARCHAR(250) NOT NULL default '',
		`action` VARCHAR(250) NOT NULL default '',
		`otherinfo` MEDIUMTEXT,
		`datetime` INT(11) NOT NULL default '0',
		`ipaddress` VARCHAR(50) NOT NULL default '',
		PRIMARY KEY  (`logid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "audit</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bankaccounts");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bankaccounts (
		`bank_id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`beneficiary_account_name` VARCHAR(100) NOT NULL default '',
		`destination_currency_id` INT(100) NOT NULL default '0',
		`beneficiary_bank_name` VARCHAR(100) NOT NULL default '',
		`beneficiary_account_number` VARCHAR(100) NOT NULL default '',
		`beneficiary_bank_routing_number_swift` VARCHAR(100) NOT NULL default '',
		`bank_account_type` VARCHAR(100) NOT NULL default '',
		`beneficiary_bank_address_1` VARCHAR(200) NOT NULL default '',
		`beneficiary_bank_address_2` VARCHAR(200) default NULL,
		`beneficiary_bank_city` VARCHAR(100) NOT NULL default '',
		`beneficiary_bank_state` VARCHAR(100) NOT NULL default '',
		`beneficiary_bank_zipcode` VARCHAR(25) NOT NULL default '',
		`beneficiary_bank_country_id` INT(100) NOT NULL default '0',
		`wire_bin_type` ENUM('SWIFT','BLZ','ABA/ROUTING NUMBER','OTHER') default 'SWIFT',
		PRIMARY KEY  (`bank_id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bankaccounts</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bid_fields");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bid_fields (
		`fieldid` INT(10) NOT NULL AUTO_INCREMENT,
		`question_eng` MEDIUMTEXT,
		`description_eng` MEDIUMTEXT,
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','date') NOT NULL default 'text',
		`multiplechoice` MEDIUMTEXT,
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`canremove` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`fieldid`),
		INDEX (`inputtype`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bid_fields</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bid_fields_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bid_fields_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`fieldid` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`bid_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`answerid`),
		INDEX (`fieldid`),
		INDEX (`project_id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bid_fields_answers</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "budget");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "budget (
		`budgetid` INT(5) NOT NULL AUTO_INCREMENT,
		`budgetgroup` VARCHAR(250) NOT NULL default '',
		`title` VARCHAR(200) NOT NULL default '',
		`fieldname` VARCHAR(50) NOT NULL default '',
		`budgetfrom` DECIMAL(10,2) NOT NULL default '0',
		`budgetto` DECIMAL(10,2) NOT NULL default '0',
		`insertiongroup` VARCHAR(250) NOT NULL default '',
		`sort` INT(5) NOT NULL default '0',
		PRIMARY KEY  (`budgetid`),
		INDEX (`budgetgroup`),
		INDEX (`title`),
		INDEX (`fieldname`),
		INDEX (`insertiongroup`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "budget</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "budget
		(`budgetid`, `budgetgroup`, `title`, `fieldname`, `budgetfrom`, `budgetto`, `sort`)
		VALUES
		(1, 'default', 'Large Project', 'large', 1000.00, 100000.00, 10),
		(2, 'default', 'Medium Project', 'medium', 500.00, 1000.00, 20),
		(3, 'default', 'Small Project', 'small', 100.00, 500.00, 30),
		(4, 'default', 'Minor Task', 'minor', 1.00, 100.00, 40)
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default budgets ranges</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "budget_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "budget_groups (
		`groupid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`description` MEDIUMTEXT,
		PRIMARY KEY  (`groupid`),
		INDEX ( `groupname` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "budget_groups</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "budget_groups
		(`groupid`, `groupname`, `description`)
		VALUES
		(1, 'default', 'Default service budget group that holds a list of pulldown budget options')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default budget groups</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "buynow_orders");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "buynow_orders (
		`orderid` INT(10) NOT NULL AUTO_INCREMENT,
		`project_id` INT(10) NOT NULL default '0',
		`buyer_id` INT(10) NOT NULL default '0',
		`owner_id` INT(10) NOT NULL default '0',
		`invoiceid` INT(10) NOT NULL default '0',
		`attachid` INT(10) NOT NULL default '0',
		`qty` INT(5) NOT NULL default '1',
		`amount` FLOAT(10,2) NOT NULL default '0.00',
		`escrowfee` FLOAT(10,2) NOT NULL default '0.00',
		`escrowfeebuyer` FLOAT(10,2) NOT NULL default '0.00',
		`fvf` FLOAT(10,2) NOT NULL default '0.00',
		`fvfbuyer` FLOAT(10,2) NOT NULL default '0.00',
		`isescrowfeepaid` INT(1) NOT NULL default '0',
		`isescrowfeebuyerpaid` INT(1) NOT NULL default '0',
		`isfvfpaid` INT(1) NOT NULL default '0',
		`isfvfbuyerpaid` INT(1) NOT NULL default '0',
		`escrowfeeinvoiceid` INT(10) NOT NULL default '0',
		`escrowfeebuyerinvoiceid` INT(10) NOT NULL default '0',
		`fvfinvoiceid` INT(10) NOT NULL default '0',
		`fvfbuyerinvoiceid` INT(10) NOT NULL default '0',
		`ship_required` INT(1) NOT NULL default '1',
		`ship_location` MEDIUMTEXT,
		`orderdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`canceldate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`arrivedate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`paiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`winnermarkedaspaid` INT(1) NOT NULL default '0',
		`winnermarkedaspaiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`winnermarkedaspaidmethod` MEDIUMTEXT,
		`buyerpaymethod` VARCHAR(250) NOT NULL default '',
		`buyershipcost` FLOAT(10,2) NOT NULL default '0.00',
		`buyershipperid` INT(5) NOT NULL default '0',
		`sellermarkedasshipped` INT(1) NOT NULL default '0',
		`sellermarkedasshippeddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`buyerfeedback` INT(1) NOT NULL default '0',
		`sellerfeedback` INT(1) NOT NULL default '0',
		`status` ENUM('paid','cancelled','pending_delivery','delivered','fraud','offline','offline_delivered') NOT NULL default 'paid',
		PRIMARY KEY  (`orderid`),
		INDEX (`project_id`),
		INDEX (`buyer_id`),
		INDEX (`owner_id`),
		INDEX (`attachid`),
		INDEX (`invoiceid`),
		INDEX (`status`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "buynow_orders</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bulk_sessions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bulk_sessions (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`user_id` INT(4) NOT NULL default '0',
		`dateupload` datetime,
		`items` INT(5) NOT NULL default '0',
		`itemsuploaded` INT(5) default '0',
		PRIMARY KEY (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bulk_sessions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bulk_tmp");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bulk_tmp (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`project_title` MEDIUMTEXT NOT NULL default '',
		`description` MEDIUMTEXT,
		`startprice` FLOAT(10,2) NOT NULL default '0.00',
		`buynow_price` FLOAT(10,2) NOT NULL default '0.00',
		`reserve_price` FLOAT(10,2) NOT NULL default '0.00',
		`buynow_qty` INT(10) NOT NULL default '0',
		`project_details` ENUM('public','invite_only','realtime','unique','penny') NOT NULL default 'public',
		`filtered_auctiontype` ENUM('regular','fixed') NOT NULL default 'regular',
		`cid` INT(10) NOT NULL default '0',
		`sample` MEDIUMTEXT,
		`currency` VARCHAR(250) NOT NULL default '',
		`correct` INT(2) NOT NULL default '0',
		`user_id` INT(4) NOT NULL default '0',
		`rfpid` INT(15) NOT NULL default '0',
		`sample_uploaded` INT(2) NOT NULL default '0',
		`bulk_id` INT(10) NOT NULL,
		PRIMARY KEY (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bulk_tmp</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "calendar");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "calendar (
		`calendarid` INT(5) NOT NULL AUTO_INCREMENT,
		`userid` INT(5) NOT NULL default '0',
		`dateline` date NOT NULL,
		`comment` MEDIUMTEXT,
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`calendarid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "calendar</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "categories");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "categories (
		`cid` INT(100) NOT NULL AUTO_INCREMENT,
		`parentid` INT(100) NOT NULL default '0',
		`sets` LINESTRING NOT NULL,
		`level` INT(5) NOT NULL default '1',
		`title_eng` MEDIUMTEXT,
		`description_eng` MEDIUMTEXT,
		`canpost` INT(1) NOT NULL default '1',
		`views` INT(100) NOT NULL default '0',
		`xml` INT(1) NOT NULL default '0',
		`portfolio` INT(1) NOT NULL default '0',
		`newsletter` INT(1) NOT NULL default '0',
		`auctioncount` INT(10) NOT NULL default '0',
		`budgetgroup` VARCHAR(250) NOT NULL default '',
		`insertiongroup` VARCHAR(250) NOT NULL default '',
		`finalvaluegroup` VARCHAR(250) NOT NULL default '',
		`incrementgroup` VARCHAR(250) NOT NULL default '',
		`cattype` ENUM('service','product') NOT NULL default 'service',
		`bidamounttypes` MEDIUMTEXT,
		`usefixedfees` INT(1) NOT NULL default '0',
		`fixedfeeamount` FLOAT(10,2) NOT NULL default '0.00',
		`nondisclosefeeamount` FLOAT(10,2) NOT NULL default '0.00',
		`multipleaward` INT(1) NOT NULL default '0',
		`bidgrouping` INT(1) NOT NULL default '0',
		`bidgroupdisplay` ENUM('lowest','highest') NOT NULL default 'lowest',
		`useproxybid` INT(1) NOT NULL default '0',
		`usereserveprice` INT(1) NOT NULL default '1',
		`useantisnipe` INT(1) NOT NULL default '0',
		`bidfields` MEDIUMTEXT,
		`catimage` VARCHAR(250) NOT NULL default '',
		`keywords` MEDIUMTEXT,
		`visible` INT(1) NOT NULL default '1',
		`sort` INT(3) NOT NULL default '0',
		`lft` INT(10) NOT NULL,
		`rgt` INT(10) NOT NULL,
		PRIMARY KEY  (`cid`),
		INDEX (`parentid`),
		INDEX (`level`),
		INDEX (`cattype`),
		INDEX (`bidgroupdisplay`),
		INDEX (`budgetgroup`),
		INDEX (`insertiongroup`),
		INDEX (`finalvaluegroup`),
		INDEX (`incrementgroup`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "categories</li>";
	
	import_master_categories();
	
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default categories (Service: Web Design & Programming; Product: Computers & Arts and Collectables)</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "charities");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "charities (
		`charityid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupid` INT(10) NOT NULL default '0',
		`title` MEDIUMTEXT NOT NULL,
		`description` MEDIUMTEXT NOT NULL,
		`url` VARCHAR(250) NOT NULL,
		`donations` INT(5) NOT NULL default '0',
		`earnings` FLOAT(10,2) NOT NULL default '0.00',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY (`charityid`),
		INDEX ( `groupid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "charities</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "configuration");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "configuration (
		`name` VARCHAR(250) NOT NULL default '',
		`description` MEDIUMTEXT,
		`value` MEDIUMTEXT,
		`configgroup` VARCHAR(250) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pass','pulldown') NOT NULL default 'yesno',
		`inputcode` MEDIUMTEXT,
		`inputname` VARCHAR(250) NOT NULL default '',
		`help` MEDIUMTEXT,
		`sort` INT(5) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`name`),
		INDEX ( `configgroup` ),
		INDEX ( `inputtype` ),
		INDEX ( `inputname` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "configuration</li>";
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maintenance_mode', 'Maintenance mode', '0', 'maintenance', 'yesno', '', '', 'If you would like to shut down the entire marketplace temporarily (to resolve a problem or to upgrade to a newer version) you should enable this option.  When enabled a custom message will be displayed to the user viewing the marketplace.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maintenance_excludeips', 'Exclude IP addresses from maintenance mode', '111.111.111.111, 222.222.222.222', 'maintenance', 'textarea', '', '', 'This setting is useful if you have enabled maintenance mode and wish to allow access to specific IP addresses only.  Please separate IP addresses using 1 comma and 1 space: 1.1.1.1, 2.2.2.2, 3.3.3.3, etc.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maintenance_excludeurls', 'Exclude specific php scripts from maintenance mode', 'redirect" . ILMIME . "', 'maintenance', 'textarea', '', '', 'Please separate PHP scripts using 1 comma and 1 space: script1.php, script2.php, script3.php, etc.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maintenance_message', 'Maintenance mode display message', 'Marketplace currently in maintenance mode and is unavailable.', 'maintenance', 'textarea', '', '', 'This message will be seen by users when maintenance mode is enabled.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_highlightactive', 'Enable highlight service auction feature?', '1', 'serviceupsell_highlight', 'yesno', '', '', '', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_highlightfees', 'Enable highlight upsell listing fees?', '1', 'serviceupsell_highlight', 'yesno', '', '', '', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_highlightcolor', 'Highlight background color CSS class', 'featured_highlight', 'serviceupsell_highlight', 'text', '', '', '', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_highlightfee', 'Highlight listing enhancement fee amount', '3.00', 'serviceupsell_highlight', 'int', '', '', '', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistactive', 'Would you like to enable the auto-relist listing feature?', '1', 'serviceupsell_autorelist', 'yesno', '', '', 'When enabled this setting will let a buyer choose to have their listing auto-relist if no bids are placed until the countdown expires.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistfees', 'Can users pay to auto-relist their listing?', '1', 'serviceupsell_autorelist', 'yesno', '', '', 'When enabled this setting will let a buyer have the ability to pay to auto-relist their listing through the use of auto-relist feature.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistfee', 'How much does it cost to use the auto-relist feature?', '3.75', 'serviceupsell_autorelist', 'int', '', '', 'This setting works only when you have enabled the ability for users to pay to auto-relist their listing.  For example, enter 5.00 if you would like to charge five dollars.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistmaxdays', 'How many days will the project be relisted for?', '7', 'serviceupsell_autorelist', 'int', '', '', 'This setting works only when you have enabled auto-relist.  For example, enter 7 if you would like to auto-relist listings for 7 days.  After this 7 day period (for example) users will have to post a new project listing.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_boldactive', 'Would you like to enable the bold listing title option?', '1', 'productupsell_bold', 'yesno', '', '', 'When enabled this setting will let a user see the bold listing title option to enhance their listing when creating a new auction listing.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_boldfees', 'Can users pay to bold the title of their listing?', '1', 'productupsell_bold', 'yesno', '', '', 'When enabled this setting will let a user have the ability to pay to enhance their listing through the use of bold listing feature.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_boldfee', 'How much does it cost to use the bold listing feature?', '3.75', 'productupsell_bold', 'int', '', '', 'This setting works only when you have enabled the ability for users to pay to bold their listing.  For example, enter 5.00 if you would like to charge five dollars.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featuredactive', 'Can users feature their auction listing on the front homepage?', '1', 'productupsell_featured', 'yesno', '', '', 'If you would like to give users the ability to feature their auction listing on the homepage when creating a new listing please enable this setting.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featuredfees', 'Would you like to charge a fee to feature auction listings on the homepage?', '1', 'productupsell_featured', 'yesno', '', '', 'If you would like to give users the ability to pay you for featuring their listing when creating a new auction please enable this setting.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featuredfee', 'Homepage Featured listing fee amount', '2.75', 'productupsell_featured', 'int', '', '', 'This setting works only when you are charging members for featuring their listing on the homepage of the marketplace.  The amount you enter in this field can only be a fixed dollar amount.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_highlightactive', 'Enable highlight product auction feature?', '1', 'productupsell_highlight', 'yesno', '', '', '', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_highlightcolor', 'Highlight background color CSS class', 'featured_highlight', 'productupsell_highlight', 'text', '', '', '', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_highlightfee', 'Fixed Amount', '3.00', 'productupsell_highlight', 'int', '', '', '', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_highlightfees', 'Enable highlight upsell listing fees?', '1', 'productupsell_highlight', 'yesno', '', '', '', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistactive', 'Would you like to enable the auto-relist listing feature?', '1', 'productupsell_autorelist', 'yesno', '', '', 'When enabled this setting will let a seller choose to have their listing auto-relist if no bids are placed until the countdown expires.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistfees', 'Can users pay to auto-relist their listing?', '1', 'productupsell_autorelist', 'yesno', '', '', 'When enabled this setting will let a seller have the ability to pay to auto-relist their listing through the use of auto-relist feature.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistfee', 'How much does it cost to use the auto-relist feature?', '3.75', 'productupsell_autorelist', 'int', '', '', 'This setting works only when you have enabled the ability for users to pay to auto-relist their listing.  For example, enter 5.00 if you would like to charge five dollars.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistmaxdays', 'How many days will the item be relisted for?', '7', 'productupsell_autorelist', 'int', '', '', 'This setting works only when you have enabled auto-relist.  For example, enter 7 if you would like to auto-relist listings for 7 days.  After this 7 day period (for example) users will have to post a new listing.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productaward_pmbafterend', 'Would you like to enable communication via PMB after the product award process is finished?', '1', 'productaward_pmb', 'yesno', '', '', 'This setting allows you to enable or disable private message board communication after a product buyer wins an item through the marketplace.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productaward_mediashareafterend', 'Would you like to enable sharing of digital media via mediashare after a product award process is finished?', '1', 'productaward_mediashare', 'yesno', '', '', 'This setting allows you to enable or disable mediashare communication after a product buyer wins an item through the marketplace.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioupsell_featuredactive', 'Can users pay to feature their portfolio items?', '1', 'portfolioupsell', 'yesno', '', '', 'If you would like to give users the ability to pay you for featuring their portfolio items from their portfolio menu please enable this setting.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioupsell_featuredfee', 'How much does it cost to feature portfolio items?', '5.00', 'portfolioupsell', 'int', '', '', 'This setting can only be used if users can pay to feature their portfolio items.  Enter the amount it will cost a user to feature their portfolio item.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioupsell_featureditemname', 'Transaction description that will appear on the new users transaction history', 'Featured Portfolio Status', 'portfolioupsell', 'text', '', '', 'This feature allows you to stamp the transaction description to your own phrase.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_thumbsperpage', 'Number of thumbnails to display on each page within portfolios?', '9', 'portfoliodisplay', 'int', '', '', 'This setting allows you to define how many portfolio images or items will be displayed on the page at once.  The default setting is 9.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_imagetypes', 'Portfolio display image extension types', '.gif, .jpg, .png, .jpeg', 'portfoliodisplay', 'textarea', '', '', 'This setting allows you to specify which image format extensions are allowed to be displayed as a graphical image within the portfolio section.  This setting requires that the portfolio system to be enabled.  Please separate each extension using 1 comma and 1 space.  Example: .gif, .jpg, .png.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_turingimage', 'Would you like to enable registration security image?', '1', 'registrationdisplay', 'yesno', '', '', 'When enabled a graphical security code embedded into an image will be displayed.  The user registering would need to type in the characters found within the security image to continue registration.  This feature prevents search engines and other robots from registering and ensures that a real human is present.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationupsell_bonusactive', 'Would you like to enable new user registration bonus?', '0', 'registrationupsell', 'yesno', '', '', 'If you would like to give newly registered users a signup bonus for marketing purposes please enable this setting.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_phoneformat', 'Phone display format on registration form?', 'US', 'registrationdisplay', 'int', '', '', 'This setting is especially useful for marketplaces that are not hosted in US or Canada.  This feature accepts one of two answers: <strong>US</strong> or <strong>OTHER</strong>.  When <strong>US</strong> is used, a phone number form will be presented like [x]-[xxx]-[xxx]-[xxxx], whereas using <strong>OTHER</strong> will present 1 form input box for foreign phone number formats.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_quickregistration', 'Would you like to enable the Quick Registration feature for new users?', '0', 'registrationdisplay', 'yesno', '', '', 'The quick registration setting allows users to quickly register to the marketplace though the use of AJAX.  Note: quick registration should only be used if you experience a low registration rate on a day to day basis.  When quick registration is enabled users will not be building their full profile and must update their personal information after they have registered.  Quick registration can be found on the log-in menu.', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationupsell_amount', 'Signup registration bonus amount to give new members', '5.00', 'registrationupsell', 'int', '', '', 'When this setting is enabled the amount entered will be credited to the new users online account balance.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationupsell_bonusitemname', 'Bonus item name that will appear on the new users transaction history', 'New Account Registration Bonus', 'registrationupsell', 'text', '', '', 'When new registration account bonus is enabled this feature allows you to stamp the new registration signup bonus transaction description to your own phrase.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('genderactive', 'Would you like to ask members their gender?', '0', 'registrationdisplay', 'yesno', '', '', 'For marketing purposes you may wish to ask members what their gender is during registration.  If you enable this, users will have the option of selecting their gender.  Additionally, if this option is enabled users can update their gender preference from their personal profile menu (not public profile).', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('referalsystem_active', 'Enable referal system?', '0', 'referalsystem', 'yesno', '', '', 'If you would like to enable the Referral System you should set this option to yes.  When enabled the user will see their referral activity from their main dashboard menu when they first sign-in.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('referalsystem_payout', 'Payout amount to customers whom refer *valid* members?', '5.00', 'referalsystem', 'int', '', '', 'This value will not automatically credit a users account balance by itself.  This amount will determine the value to show when viewing your referral activity allowing you to quickly click Credit Account for any referrals you would like to pay out.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachment_dbstorage', 'Would you like to save attachments directly to the database?', '1', 'attachmentsystem', 'yesno', '', '', 'This setting should be one of the first settings you should be deciding upon prior to launching in a production environment.  If you would like to store attachments within the database please enable this setting.  By default, attachments are saved in the database.<br /><br />Pros: Backups made easy (no need to backup physical files)<br />Cons: May increase database server resulting in slower access rates over time.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachment_moderationdisabled', 'Can members upload attachments without being moderated by staff?', '1', 'attachmentmoderation', 'yesno', '', '', 'Depending on your market niche, you may need to enable attachment moderation.  If you feel no need to verify uploaded images, files and other media before they are publically visible in the marketplace please enable this setting. When this setting is disabled (verifying all attachments), it should be noted that you need to visit the moderate tab from the attachment manager to validate all pending uploads or users will never see them.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachment_mediasharemoderationdisabled', 'Can members upload attachments within mediashare without being moderated by staff?', '1', 'attachmentmoderation', 'yesno', '', '', 'Mediashare is usually a much more secure interface for users who are awarded projects the ability to transfer files and media back and forth to their buyers.  If you feel no need to verify uploaded mediashare files and other media before they are publically visible in the marketplace please enable this setting.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_profileextensions', 'Profile attachment extensions allowed [.gif, .jpg]', '.gif, .jpg, .png', 'attachmentlimit_profileextensions', 'textarea', '', '', '', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_defaultextensions', 'Default attachment extensions allowed [.pdf, .zip]', '.psd, .doc, .txt, .pdf, .jpg, .gif, .png, .bmp, .zip, .gz, .tar, .rar, .csv, .xls', 'attachmentlimit_defaultextensions', 'textarea', '', '', '', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfolioextensions', 'Portfolio attachment extensions allowed [.doc, .gif]', '.psd, .doc, .txt, .pdf, .jpg, .gif, .png, .bmp, .zip, .csv, .xls', 'attachmentlimit_portfolioextensions', 'textarea', '', '', '', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_profilemaxwidth', 'Maximum profile attachment [WIDTH in px]', '320', 'attachmentlimit_profileextensions', 'int', '', '', '', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_profilemaxheight', 'Maximum profile attachment [HEIGHT in px]', '240', 'attachmentlimit_profileextensions', 'int', '', '', '', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_profilemaxsize', 'Maximum profile attachment [FILESIZE in bytes]', '1500000', 'attachmentlimit_profileextensions', 'int', '', '', '', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_projectmaxwidth', 'Maximum auction attachment [WIDTH in px]', '1024', 'attachmentlimit_defaultextensions', 'int', '', '', '', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_projectmaxheight', 'Maximum auction attachment [HEIGHT in px]', '768', 'attachmentlimit_defaultextensions', 'int', '', '', '', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_projectmaxsize', 'Maximum auction attachment [FILESIZE in bytes]', '1500000', 'attachmentlimit_defaultextensions', 'int', '', '', '', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliomaxwidth', 'Maximum portfolio attachment [WIDTH in px]', '1024', 'attachmentlimit_portfolioextensions', 'int', '', '', '', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliomaxheight', 'Maximum portfolio attachment [HEIGHT in px]', '768', 'attachmentlimit_portfolioextensions', 'int', '', '', '', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliomaxsize', 'Maximum portfolio attachment [FILESIZE in bytes]', '1500000', 'attachmentlimit_portfolioextensions', 'int', '', '', '', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliothumbwidth', 'Maximum portfolio thumbnail attachment [WIDTH in px]', '100', 'attachmentlimit_portfolioextensions', 'int', '', '', '', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliothumbheight', 'Maximum portfolio thumbnail attachment [HEIGHT in px]', '100', 'attachmentlimit_portfolioextensions', 'int', '', '', '', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_bidmaxwidth', 'Maximum bid attachment [WIDTH in px]', '1024', 'attachmentlimit_bidsettings', 'int', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_bidmaxheight', 'Maximum bid attachment [HEIGHT in px]', '768', 'attachmentlimit_bidsettings', 'int', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_bidmaxsize', 'Maximum bid attachment [FILESIZE in bytes]', '1500000', 'attachmentlimit_bidsettings', 'int', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_pmbmaxwidth', 'Maximum PMB attachment [WIDTH in px]', '1024', 'attachmentlimit_pmbsettings', 'int', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_pmbmaxheight', 'Maximum PMB attachment [HEIGHT in px]', '768', 'attachmentlimit_pmbsettings', 'int', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_pmbmaxsize', 'Maximum PMB attachment [FILESIZE in bytes]', '1500000', 'attachmentlimit_pmbsettings', 'int', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_mediasharemaxwidth', 'Maximum workspace attachment [WIDTH in px]', '1024', 'attachmentlimit_workspacesettings', 'int', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_mediasharemaxheight', 'Maximum workspace attachment [HEIGHT in px]', '768', 'attachmentlimit_workspacesettings', 'int', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_mediasharemaxsize', 'Maximum workspace attachment [FILESIZE in bytes]', '1500000', 'attachmentlimit_workspacesettings', 'int', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsmaxwidth', 'Maximum search result list view thumbnail [WIDTH in px]', '96', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsmaxheight', 'Maximum search result list view thumbnail [HEIGHT in px]', '72', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsgallerymaxwidth', 'Maximum search result gallery view thumbnail [WIDTH in px]', '150', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsgallerymaxheight', 'Maximum search result gallery view thumbnail [HEIGHT in px]', '150', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultssnapshotmaxwidth', 'Maximum search result snapshot view thumbnail [WIDTH in px]', '110', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultssnapshotmaxheight', 'Maximum search result snapshot view thumbnail [HEIGHT in px]', '110', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_thumbnailmaxwidth', 'Maximum default thumbnail attachment [WIDTH in px]', '96', 'attachmentlimit_defaultextensions', 'int', '', '', '', 18, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_thumbnailmaxheight', 'Maximum default thumbnail attachment [HEIGHT in px]', '72', 'attachmentlimit_defaultextensions', 'int', '', '', '', 19, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_defaultcountry', 'Default country name to display on registration form?', 'Canada', 'registrationdisplay', 'text', '', '', 'When users are viewing the registration form for the first time, which Country would you like displayed by default?', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_defaultstate', 'Default state name to display on registration form?', 'Ontario', 'registrationdisplay', 'text', '', '', 'When users are viewing the registration form for the first time, which major State or Province would you like displayed by default?', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_payercancancelfunds', 'Can a user that has funded an escrow account cancel and return funds back to their account balance?', '0', 'escrowsystem', 'yesno', '', '', 'Depending on the market niche you are serving, you can let the person paying into an escrow account request a funding cancellation request which would move funds previously paid into an escrow account back to the payers online account balance.  By default this setting is disabled as it can lead to many disputes if abused by the end user.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_payercancancelfundsafterrelease', 'Can a user who releases funds cancel and return those funds back to their account balance?', '0', 'escrowsystem', 'yesno', '', '', 'Depending on your niche, you can let the person paying into an escrow and who has already released funds from that escrow into the receivers account balance force a funding cancellation request which would move funds previously paid and released back to the payers online account balance.  By default this setting is disabled as it can lead to many disputes if abused by the end user.  Fees (if applied) will also be reversed.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_enablep2btransactionfees', 'Would you like to enable provider to buyer invoice generation fees?', '1', 'invoicesystem', 'yesno', '', '', 'When this setting is enabled, you can charge service providers a commission fee based on the invoice amount they are requesting from their trading partner (buyer).  If you do not wish to generate fees for provider generated invoices to buyers through the marketplace please disable this option.', 0, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_transactionidlength', 'Unique transaction character length', '17', 'invoicesystem', 'int', '', '', 'This setting applies to newly created transaction ids.  If you enter a value of 10 then a unique transaction id will be created with this amount of characters.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('moderationsystem_disableauctionmoderation', 'Disable listing moderation?', '0', 'globalauctionsettings', 'yesno', '', '', 'When listing moderation is disabled the listing is posted for the public to see whereas when enabled an email is dispatched for admin to verify before public visibility.', 0, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_emailonfailedlogins', 'Enable email to administrator on customer failed log-in attempts [includes attempted username and password]?', '0', 'globalsecurity', 'yesno', '', '', 'You can choose to inform the administrator of an attempted login failure report which will be sent via email.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_numfailedloginattempts', 'Number of failed log-in attempts before the attempted [username] receives a failed email login report', '5', 'globalsecurity', 'int', '', '', 'For added security and protection, you can define how many login attempts can occur before the actual user connected to the account (username) will receive a failed login email report.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_extensionmime', 'Enter the application extension mime [.php, .asp, etc] used to hide default .php extension', '" . ILMIME . "', 'globalsecuritymime', 'int', '', '', 'This option can mask script extentions like main.php -> main.jsp.  For example: <em>AddType application/x-httpd-php jsp</em> would rewrite (mask) the php handler to .jsp.  If an extension other than <strong>.php</strong> is used, all scripts must be renamed from .php to .jsp.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_emailfilterpmb', 'Enable email address filtering within private messages?', '1', 'globalfilterspmb', 'yesno', '', '', 'This option will attempt to search and replace email addresses when users are posting or viewing private messages. If you would like to hide email addresses within private messages you should enable this setting.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_domainfilterpmb', 'Enable domain name filtering within private messages?', '1', 'globalfilterspmb', 'yesno', '', '', 'This option will attempt to search and replace domain name type urls when users are posting or viewing private messages. If you would like to hide domain names within private messages you should enable this setting.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablepmbspy', 'Enable Private Message BCC dispatch to the administrator?', '1', 'globalfilterspmb', 'yesno', '', '', 'You can enable this option which would dispatch a blind carbon copy of the entire private message to the administrator.  It would be recommended that you inform your community that PMB spy moderation is enabled if you decide to enable this setting.  Some users may feel their privacy would be of concern if enabled.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_emailfilterrfp', 'Enable email address filtering within listed auction descriptions?', '1', 'globalfiltersrfp', 'yesno', '', '', 'This option will attempt to search and replace email addresses when users are posting or viewing listed auction descriptions.  If you would like to hide email addresses within the description of the listing you should enable this setting.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_domainfilterrfp', 'Enable domain name filtering within listed auction descriptions?', '1', 'globalfiltersrfp', 'yesno', '', '', 'This option will attempt to search and replace domain name type urls when users are posting or viewing listed auction descriptions.  If you would like to hide domain names within the description of the listing you should enable this setting.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablerfpcancellation', 'Can members cancel a valid listing they have created (including drafts)?', '1', 'globalfiltersrfp', 'yesno', '', '', 'If you would like to give members the ability to delist their auction from the marketplace (before any bids are placed) you should enable this setting.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_emailfilterbid', 'Enable email address filtering within bid proposals?', '1', 'globalfiltersbid', 'yesno', '', '', 'This option will attempt to search and replace email addresses when users are posting or viewing bid proposals. If you would like to hide email addresses within bid proposals you should enable this setting.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_domainfilterbid', 'Enable domain name filtering within bid proposals?', '1', 'globalfiltersbid', 'yesno', '', '', 'This option will attempt to search and replace domain name type urls when users are posting or viewing bid proposals.  If you would like to hide domain names within bid proposals you should enable this setting.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_vulgarpostfilter', 'Enable vulgarity posting filter?', '1', 'globalfiltersvulgar', 'yesno', '', '', 'When enabled, this feature will detect any user-submitted input within special areas such as posting a new listing, updating a listing, placing a bid to determine if the text contains words you have flagged as vulgar and replaced with predefined values you define.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_vulgarpostfilterlist', 'Vulgar words blacklist', 'fuck, fucker, fucking, fucked, fuckhead, fuk, fuked, fukd, fuckface, shit, shithead, bitch, b!tch, asshole, cunt, whore, lush, faggot, fag, cock, cocksucker, dick, dickhead, dickface, nigger, arse, bastard, slut, dork, wanker, dumbass, arsehole, honkey, pigface', 'globalfiltersvulgar', 'textarea', '', '', 'The format is expected to be separated by 1 comma and 1 space. For example: word1, word2, etc.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_vulgarpostfilterreplace', 'Vulgar word replacement value', '--**--', 'globalfiltersvulgar', 'textarea', '', '', 'This option will let you change any vulgar word that has been flagged and replace it with the string you predefine.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_blockips', 'IP Address Blacklist', '', 'globalfiltersipblacklist', 'textarea', '', '', 'You can blacklist a number of IP Addresses using the blacklist.  The format is expected to be separated by 1 comma and 1 space.  For example: 0.0.0.0, 1.1.1.1, etc', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablecategorycount', 'Enable category listing counters?', '1', 'globalfilterresults', 'yesno', '', '', 'Category listing counters is a great way to show your members how many listings have been posted within a particular category.  In general, a new site should disable category counters until the site becomes active with various users posting active listings within the categories.  As a result a category of Web Design would appear like: Web Design (12).', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxrowsdisplay', 'Maximum number of results to fetch and display for queries throughout the frontend marketplace?', '5', 'globalfilterresults', 'int', '', '', 'This option communicates with your database and will let you define how many results to pull based on specific areas within the frontend of the marketplace.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserver_enabledistanceradius', 'Enable Distance Calculation?', '0', 'globalserverdistanceapi', 'yesno', '', '', 'If you have one or more valid distance calculation database tables (distance_canada, distance_usa, distance_uk or distance_nl) then you can enable this option to be used within the search results area.  Note: users must also update their distance preference from their profile menu if not already enabled.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_officialtimezone', 'Official marketplace time zone', '-5', 'globalserverlocale', 'pulldown', '<select name=\"config[globalserverlocale_officialtimezone]\" style=\"font-family: verdana\"><option value=\"-12\">(UTC -12:00) Baker Island Time</option><option value=\"-11\">(UTC -11:00) Niue Time, Samoa Standard Time</option><option value=\"-10\">(UTC -10:00) Hawaii-Aleutian Standard Time</option><option value=\"-9.5\">(UTC -9:30) Marquesas Islands Time</option><option value=\"-9\">(UTC -9:00) Alaska Standard Time</option><option value=\"-8\">(UTC -8:00) Pacific Standard Time</option><option value=\"-7\">(UTC -7:00) Mountain Standard Time</option><option value=\"-6\">(UTC -6:00) Central Standard Time</option><option value=\"-5\" selected=\"selected\">(UTC -5:00) Eastern Standard Time</option><option value=\"-4\">(UTC -4:00) Atlantic Standard Time</option><option value=\"-3.5\">(UTC -3:30) Newfoundland Standard Time</option><option value=\"-3\">(UTC -3:00) Amazon Standard Time</option><option value=\"-2\">(UTC -2:00) Fernando de Noronha Time, South Georgia Time</option><option value=\"-1\">(UTC -1:00) Azores Standard Time, Eastern Greenland Time</option><option value=\"0\">(UTC) Western European Time, Greenwich Mean Time</option><option value=\"1\">(UTC +1:00) Central European Time, West African Time</option><option value=\"2\">(UTC +2:00) Eastern European Time, Central African Time</option><option value=\"3\">(UTC +3:00) Moscow Standard Time, Eastern African Time</option><option value=\"3.5\">(UTC +3:30) Iran Standard Time</option><option value=\"4\">(UTC +4:00) Gulf Standard Time, Samara Standard Time</option><option value=\"4.5\">(UTC +4:30) Afghanistan Time</option><option value=\"5\">(UTC +5:00) Pakistan Standard Time</option><option value=\"5.5\">(UTC +5:30) Indian Standard Time, Sri Lanka Time</option><option value=\"5.75\">(UTC +5:45) Nepal Time</option><option value=\"6\">(UTC +6:00) Bangladesh Time, Bhutan Time</option><option value=\"6.5\">(UTC +6:30) Cocos Islands Time, Myanmar Time</option><option value=\"7\">(UTC +7:00) Indochina Time, Krasnoyarsk Standard Time</option><option value=\"8\">(UTC +8:00) Chinese Standard Time</option><option value=\"8.75\">(UTC +8:45) Southeastern Western Australia Standard Time</option><option value=\"9\">(UTC +9:00) Japan Standard Time, Korea Standard Time</option><option value=\"9.5\">(UTC +9:30) Australian Central Standard Time</option><option value=\"10\">(UTC +10:00) Australian Eastern Standard Time</option><option value=\"10.5\">(UTC +10:30) Lord Howe Standard Time</option><option value=\"11\">(UTC +11:00) Solomon Island Time, Magadan Standard Time</option><option value=\"11.5\">(UTC +11:30) Norfolk Island Time</option><option value=\"12\">(UTC +12:00) New Zealand Time, Fiji Time</option><option value=\"12.75\">(UTC +12:45) Chatham Islands Time</option><option value=\"13\">(UTC +13:00) Tonga Time, Phoenix Islands Time</option><option value=\"14\">(UTC +14:00) Line Island Time</option></select>', 'timezones', 'This setting controls the timezone in which the marketplace is located.  All listings posted will adhere to this specific timezone.', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_officialtimezonedst', 'Enable daylight savings time consideration?', '0', 'globalserverlocale', 'yesno', '', '', 'This option allows you to offset +1 hour for daylight savings time consideration.  This setting should only be changed if you experience date and times 1 hour behind.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_defaultcurrency', 'Default marketplace currency locale', '1', 'globalserverlocalecurrency', 'pulldown', '<select name=\'config[globalserverlocale_defaultcurrency]\'><option value=\'1\' SELECTED>US DOLLAR (USD)</option><option value=\'3\'>AUSTRALIAN DOLLAR (AUD)</option><option value=\'6\'>BRITISH POUND (GBP)</option><option value=\'7\'>CANADIAN DOLLAR (CAD)</option><option value=\'9\'>CYPRUS POUND (CYP)</option><option value=\'11\'>DANISH KRONE (DKK)</option><option value=\'14\'>EURO (EUR)</option><option value=\'18\'>HONG KONG DOLLAR (HKD)</option><option value=\'22\'>JAPANESE YEN (JPY)</option><option value=\'24\'>MALTESE LIRA (MTL)</option><option value=\'27\'>NEW ZEALAND DOLLAR (NZD)</option><option value=\'28\'>NORWEGIAN KRONE (NOK)</option><option value=\'32\'>RAND (ZAR)</option><option value=\'37\'>SINGAPORE DOLLAR (SGD)</option><option value=\'39\'>SWEDISH KRONA (SEK)</option><option value=\'40\'>SWISS FRANC (CHF)</option><option value=\'41\'>TURKISH LIRA (TRL)</option></select>', 'currencyrates', 'This setting controls the global currency locale your marketplace will be operating under.  This value will be used to format the average bid amounts, buy now price and other areas that display money within in the marketplace.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_defaultcurrencyxml', 'Live XML Currency Data Feed', 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml', 'globalserverlocalecurrency', 'text', '', 'currencyrates', 'This setting allows you to set the live xml currency feed that will be read and parsed daily.  The default feed is <em>http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml</em> which is owned/operated by the European Central Bank (ECB).', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_currencyselector', 'Enable the currency selector when listing auctions?', '0', 'globalserverlocalecurrency', 'yesno', '', 'currencyrates', 'When enabled, this setting will allow users posting listings to define the currency the listing accepts.  Additionally, if bulk upload is enabled, the API chart will allow a special field to include a currency field (example: EUR, CAD, USD, etc)', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_companyname', 'Company name', '" . addslashes($_SESSION['company_name']) . "', 'globalserversettings', 'text', '', '', 'This company name should not be confused with your Site title or Site Name.  For example, ILance Inc. is a company name, ILance Marketplace could be the Site title / name.  Company name will only show on invoices and transactions generated from the company to the users.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_sitename', 'Web site title', '" . addslashes($_SESSION['site_name']) . "', 'globalserversettings', 'text', '', '', 'This setting will be presented on all page titles used for SEO.  The site name/title should not be confused with your Company name. This name will be used within email templates and other areas like meta tags.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_siteaddress', 'Company address', '" . addslashes($_SESSION['site_address']) . "', 'globalserversettings', 'textarea', '', '', 'Your company address should be supplied in this area.  This address will be presented on invoices and related transactions such as (print mode).', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_siteemail', 'Marketplace email address', '" . addslashes($_SESSION['site_email']) . "', 'globalserversettings', 'text', '', '', 'This setting will control what email address will be used when the marketplace is requested to dispatch email to a user.  They will see this email address within the From: area of the email.', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_sitephone', 'Company phone number', '+1.111.111.1111', 'globalserversettings', 'text', '', '', 'This setting is useful and should be provided to users who may request sales or customer support.  At this time, the only place this phone number is displayed is on the print invoice template.', 4, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlanguage_defaultlanguage', 'Official marketplace language id locale', '1', 'language', 'int', '', '', 'This option is especially helpful if you would like to force a default language to be displayed when users connect to the marketplace.  In order for this setting to work, you must already know the language id number you wish to use before you change this setting.  Failure to enter a proper language id number will result with unexpected results.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_productauctionsenabled', 'Enable product auction environment?', '1', 'globalauctionsettings', 'yesno', '', '', 'When product auction logic is enabled sellers can post new items for sale based on auction format and/or fixed price listing.  The winner is determined by the highest bid or a purchase from the listing via buy now.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_serviceauctionsenabled', 'Enable service auction environment?', '1', 'globalauctionsettings', 'yesno', '', '', 'When service auction logic is enabled users can post new service auctions where the person posting the listing can award a single bidder for his/her project.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_pmbpopupwidth', 'Private Message popup width window display size (pixels)', '760', 'globalfilterspmb', 'int', '', '', 'Private messages are generally served within a popup window that is activated by Javascript on a users web browser.  This setting lets you define the width of the popup window when the user has clicked on a private message.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_pmbpopupheight', 'Private Message popup height window display size (pixels)', '350', 'globalfilterspmb', 'int', '', '', 'This setting lets you define the height of the popup window when the user has clicked on a private message.', 7, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_pmbwysiwyg', 'Enable Rich-Text WYSIWYG BBEditor for private messages?', '1', 'globalfilterspmb', 'yesno', '', '', 'The integrated BBEditor provides users with a professional method of posting a valid private message on your marketplace. When enabled, the BBEditor will appear letting the user create vibrant messages.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_pmbattachments', 'Can members send attachments within private messages?', '1', 'globalfilterspmb', 'yesno', '', '', 'If you would like to give users the ability to send and receive file attachments within private message boards then you should enable this setting.', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablebbcode', 'Enable BBCode HTML emulation?', '1', 'globalfilterresults', 'yesno', '', '', 'BBCode is an integrated feature that allows users to post messages using BBCode.  BBCode is a safe method of allowing users to use HTML markup syntax in a safe formatted way.  For example: &lt;b&gt;..&lt;/b&gt; can be used like [b]..[/b] to let the user present the text in bold.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablewysiwyg', 'Enable Rich-Text WYSIWYG BBEditor?', '1', 'globalfilterresults', 'yesno', '', '', 'The integrated BBEditor provides users with a professional method of posting a valid message on your marketplace.  When enabled, the BBEditor will appear during places like posting a new listing and when users place bid proposals.  The BBEditor is fully BBCode-compliant.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_changeauctiontitle', 'Can an member update the title of their listed auction after it has been posted?', '0', 'globalfiltersrfp', 'yesno', '', '', 'Globally you can set the overall outcome if users can change the listing once they have posted it live.  You should enable this option if you do not want users to change the title of their listing after it is posted.', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxcharacterstitle', 'Maximum number of characters of the title on the main page', '0', 'globalfilterresults', 'int', '', '', 'Enter 0 for unlimited characters or any other number to cut off the title', 401, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_clientcpnag', 'Would you like to be reminded when you are viewing the site as Admin in the front end?', '1', 'globalfilterresults', 'yesno', '', '', 'This setting might be a good idea when enabled to remind you that you are viewing the front end (client cp) as an administrative user.  The reminder will let you know that some elements on the front end will be visible to you as an admin user such as sealed bidders being non-sealed when being viewed as an admin vs. regular level access.', 402, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_whitespacestripper', 'Would you like to enable template whitespace stripping? (compresses HTML output)', '0', 'globalfilterresults', 'yesno', '', '', 'This setting will remove all whitespace from the outputted HTML template after the template parser has compiled the template ready for viewing.  For example, uncompressed search results average 150kb and when whitespace stripping is enabled may reduce this to 100kb or less.', 403, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_globaltimeformat', 'Global date and time format display', '%a, %b %d, %Y %I:%M %p', 'globalserverlocale', 'text', '', '', 'This option allows you to set the date and time display format for every date displayed in the marketplace.  The default formatting is <em>%d-%b-%Y %I:%M:%S %p</em> which would represent <em>10-Feb-2008 12:00:00 AM</em>.  This format will be displayed using <em>gmstrftime()</em>.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_yesterdaytodayformat', 'Enable today / yesterday display?', '1', 'globalserverlocale', 'yesno', '', '', 'This setting is used in special places that do not require the full date and time to be shown.  For example, when viewing a private message that was posted 5 minutes ago would be displayed as <em>Posted: 5 minutes ago</em> vs. the full date and time.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_blockregistrationproxies', 'Enable proxy registration blocking?', '0', 'globalsecuritysettings', 'yesno', '', '', 'Proxy blocking is especially useful because when you ban or suspend users they can easily re-register or connect to your marketplace from a different IP.  In most cases, these types of users will attempt to hide behind a proxy service which acts as the connection for the user.  To disable proxy services entirely, enable this option.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_accountsabbrev', 'Company accounting abbreviation identifier', 'IL', 'globalserversettings', 'text', '', '', 'This setting can be used to generate a unique prefix to a users account when they register as a new user.  This is a symbol that is attached to the left of the account number.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_seourls', 'Enable search engine friendly (SEF) URLs?', '0', 'globalseo', 'yesno', '', '', 'By default, SEF URLs are disabled and requires the server to have mod_rewrite enabled.  When enabled, SEF urls provide major search engines the ability to easily index urls in the marketplace increasing exposure to your web site.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('seourls_lowercase', 'Should characters in a SEF URL be all lowercase?', '0', 'globalseo', 'yesno', '', '', 'By default, SEF URLs if enabled will treat the URL characters with uppercase as well as lowercase characters.  This options allows you to force all characters in a SEF URL lowercase.  Once established in a production environment, it would not be recommended to change this option as it may affect search engine rankings when a url change is detected.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_maximumpaymentdays', 'Maximum days before a transaction becomes outstanding (due)', '15', 'invoicesystem', 'int', '', '', 'This setting gives you the chance to provide users with a grace period to sum up for unpaid invoice transaction for actions and events that may be invoked during their session.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_mindepositamount', 'Minimum deposit amount to online account balance', '100', 'invoicesystem', 'int', '', '', 'This setting controls the minimum amount of money a user can deposit to their online account balance from the deposit menu.', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_maxdepositamount', 'Maximum deposit amount to online account balance', '1000', 'invoicesystem', 'int', '', '', 'This setting controls the maximum amount of money a user can deposit to their online account balance from the deposit menu.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_minwithdrawamount', 'Minimum withdraw amount from online account balance', '100', 'invoicesystem', 'int', '', '', 'This setting controls the minimum amount of money a user can withdraw from their online account balance from the withdraw menu.', 7, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_maxwithdrawamount', 'Maximum withdraw amount from online account balance', '1000', 'invoicesystem', 'int', '', '', 'This setting controls the maximum amount of money a user can withdraw from their online account balance from the withdraw menu.', 8, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_thumbsperrow', 'Number of thumbnails to display per row within portfolios?', '3', 'portfoliodisplay', 'int', '', '', 'This feature allows you to set how many thumbnails will be presented on the portfolio page when users are viewing the portfolio section.  The default setting is 3.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('externalcss', 'Would you like to enable externally generated .css style sheets?', '0', 'template', 'yesno', '', '', 'This setting is useful if you would prefer to have the CSS style sheets be loaded externally vs. inline.  The advantage of calling style sheets externally allows the web browser to cache the css vs. inline where it must reload and serve the same css again.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('externalcsstimeout', 'Enter the time to live (in minutes) when the external CSS cache-file will regenerate', '5', 'template', 'int', '', '', 'This setting requires enabling CSS style sheets externally. When enabled, the css style sheets will overwrite itself every x minutes (whatever you define in this setting).  This allows the CSS cache file to stay current with any changes you might be making within the CSS style manager.  Default value is 5 minutes.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('defaultstyle', 'Official marketplace style id presentation to serve', '1', 'template', 'int', '', '', 'This option is especially helpful if you would like to force a default style to be displayed when users connect to the marketplace. In order for this setting to work, you must already know the style id number you wish to use before you change this setting. Failure to enter a proper style id number will result with unexpected results.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_dob', 'Enable date of birth requirement during registration?', '1', 'registrationdisplay', 'yesno', '', '', 'This setting allows you to force users to enter their date of birth during the registration form.', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_showflashcountdown', 'Would you like to enable Flash countdown timer applet?', '0', 'globalauctionsettings', 'yesno', '', '', 'By default the flash time left feature is disabled.  When enabled, users will see a live countdown applet showing realtime time left status for that listing.  Additionally, when enabled, the server will require more processing power which may result in slower access times and overall experience.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_emailverification', 'Would you like users to verify their email during registration?', '1', 'registrationdisplay', 'yesno', '', '', 'When this setting is enabled, the newly registered user will need to view their email inbox for a link within that email.  The user would need to click that link in order to verify their email address.  This setting when enabled ensures a live person is registering.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_emailban', 'Email Address Blacklist', '', 'registrationdisplay', 'textarea', '', '', 'This blacklist will hold all your banned/blacklist email addresses you do not want users to be able to use.  For each email to blacklist, separate by 1 space.  Example: hotmail.com yahoo.com aol.com', 7, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_userban', 'Username Blacklist', '', 'registrationdisplay', 'textarea', '', '', 'This blacklist will hold all your banned/blacklist usernames you do not want users to be able to use.  For each name to blacklist, separate by 1 space.  Example: root r00t admin adm1n.', 8, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_enabled', 'Enable safe escrow?', '0', 'escrowsystem', 'yesno', '', '', 'This setting determines if escrow will be used on your site or not.  When escrow is disabled, outside payment between users will be active letting users conduct their own transactions.  Regardless of escrow being enabled or disabled, the marketplace will still generate commission fees (other than escrow) to be paid in full accordingly (if applicable).  Additionally, when enabled the user posting the auction listing will ultimately decide if they will use escrow for their auction.  This option (when enabled) is available to them.  When enabled escrow funding is between the site owner and the person funding an escrow account for a listing in the marketplace.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserver_distanceformula', 'Distance response formula', '1.60943', 'globalserverdistanceapi', 'text', '', '', 'This option should be used if your members prefer to see distance calculation responses within a metric other than miles.  For example, Canada uses KM so it would be miles * 1.60943 = 1 KM.  The default response is calculated within miles (enter 0 for miles).', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserver_distanceresults', 'Distance response metric symbol', 'KM', 'globalserverdistanceapi', 'text', '', '', 'This option will show the viewer the metric you have chosen to use.  It is a symbol (KM, Miles, etc) which will present itself when showing a calculation response.  For example: 83 miles or 83 KM.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_dobunder18', 'Allow members under the age of 18 to register?', '1', 'registrationdisplay', 'yesno', '', '', 'This setting is an enhancement of the date of birth global option and must be enabled in order to use this feature properly.  This setting will present a message to the user attempting to register denying access only if the user enters an age lower than 18.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_enablesniping', 'Would you like to enable anti-bid sniping?', '0', 'productbid_limits', 'yesno', '', '', 'When enabled, this setting will prevent bid-snipers from being able to submit a bid at the last ending minutes (or seconds) of an product auction event.  As a result, the auction will extend itself automatically based on the settings you define for anti-bid sniping extending. When enabled, you can still enable or disable anti bid sniping on a per-category basis using the product auction category manager.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_snipeduration', 'Enter the duration to extend the auction (value in seconds)', '2', 'productbid_limits', 'int', '', '', 'This setting applies only when the anti-bid sniping feature is enabled.  This feature allows you to define how many seconds to extend the auction event when a bid-sniper has been detected.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_enableproxybid', 'Would you like to enable proxy bidding?', '1', 'productbid_limits', 'yesno', '', '', 'This is a global setting.  When enabled, you can still enable or disable proxy bidding on a per-category basis using the product auction category manager.  Proxy bidding allows product buyers to enter their maximum bid and the marketplace will assign a virtual bidder to compete for him until his maximum bid has reached the limit willing to pay.', 8, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_finalvaluefeesactive', 'Enable final value fees to sellers for auctions that have a winning bidder?', '0', 'productupsell_fees', 'yesno', '', '', 'This is a global setting.  When enabled all pre-defined final value fee groups and values within groups will be activated.  Additionally, if you set this setting to No, this will quickly disable the entire Final Value Fee system until you enable this again.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_escrowcommissionfees', 'Would you like to charge safe escrow commission fees?', '0', 'escrowsystem', 'yesno', '', '', 'This setting is a global option.  When enabled you can define the escrow fee commission logic based on settings below.  When disabled, no fees will be charged to any user for the use of safe escrow and will act like a free value added service.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_registrationnumber', 'Business registration number', '', 'globalserversettings', 'text', '', '', 'For registered businesses, LLC, Incorporated companies, this setting should be filled in.  At this time, the only place this registration number is displayed is on the print invoice template.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_vatregistrationnumber', 'Business VAT registration number', '', 'globalserversettings', 'text', '', '', 'If you have a registered VAT business number please supply it within this field.  This will only be displayed on the print invoice template.', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_moderation', 'Would you like to moderate and verify all new users?', '', 'registrationdisplay', 'yesno', '', '', 'This feature allows you to moderate and verify all new user registrations before the user is able to gain access to the marketplace via login menu.  When enabled, an email will be dispatched to the admin informing of a new user to verify.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_unpaidreminders', 'Enable email dispatch of unpaid invoice reminders to users?', '1', 'invoicesystem', 'yesno', '', '', 'This setting ensures that unpaid invoice reminders will be dispatched to users via email when payment is due and the transaction is still unpaid.', 9, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_resendfrequency', 'Email dispatch frequency for unpaid invoice transactions (in days)', '15', 'invoicesystem', 'int', '', '', 'This setting compliments sending email reminders to users with unpaid invoice transactions.  From this setting you can define the resend frequency (in days) that emails will resend if users have not paid for transactions that are overdue. Default is 15 days.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_daysafterfirstreminder', 'How many days after the unpaid invoice reminder is sent should the first reminder be sent?', '3', 'invoicesystem', 'int', '', '', 'For example, if a user has not paid for a transaction and the first reminder email was sent, how many days after the first email reminder should we continue to dispatch unpaid email invoice notices? (default is 3 days).', 11, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_popups', 'Would you like to enable popup preview within portfolios?', '1', 'portfoliodisplay', 'yesno', '', '', 'This setting allows you to let users mouse-over an image within the portfolio system to be shown a popup larger view of the image.  Note: the popup system will only display file-types that you define for your portfolio display image extension types.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_servicebuyerfixedprice', 'Would you like to charge service buyers a <strong>fixed</strong> commission fee? (enter 0 to disable)', '0', 'escrowsystem', 'int', '', '', 'This setting will only work when safe escrow commission fees are enabled.  This specific setting will let you charge service buyers for securing their funds for a project they have posted and awarded to a service provider. This setting is also based on a fixed-price amount only (format like 5.00).  Note: this setting will take presidence over the percentage logic and cannot be mixed and matched.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_servicebuyerpercentrate', 'Would you like to charge service buyers a <strong>percentage</strong> commission fee? (enter 0.0 to disable)', '0.0', 'escrowsystem', 'int', '', '', 'This setting will only work when safe escrow commission fees are enabled.  This specific setting will let you charge service buyers for securing their funds for a project they have posted and awarded to a service provider. This setting is also based on a percentage value only and is calculated as (awarded bid amount * percentage you enter here / 100) (expected format is 2.5 not 2.5%). Note: this setting will take presidence over the fixed amount logic only if the fixed amount is set to 0 (disabled).', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_providerfixedprice', 'Would you like to charge service providers a <strong>fixed</strong> commission fee? (enter 0 to disable)', '0', 'escrowsystem', 'int', '', '', 'This setting will only work when safe escrow commission fees are enabled. This specific setting will let you charge service providers for receiving their funds for a project they have bid on and become awarded. This setting is also based on a fixed-price amount only (format like 5.00). Note: this setting will take presidence over the percentage logic and cannot be mixed and matched.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_providerpercentrate', 'Would you like to charge service providers a <strong>percentage</strong> commission fee? (enter 0.0 to disable)', '0.0', 'escrowsystem', 'int', '', '', 'This setting will only work when safe escrow commission fees are enabled.  This specific setting will let you charge service providers for receiving their funds for a project they have bid on and become awarded.  This setting is also based on a percentage value only and is calculated like (awarded bid amount * percentage you enter here / 100) (format like 2.5 not 2.5%). Note: this setting will take presidence over the fixed amount logic only if the fixed amount is set to 0 (disabled).', 7, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowmaxwidth', 'Maximum slideshow attachment [WIDTH in px]', '800', 'attachmentlimit_productslideshowsettings', 'int', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowmaxheight', 'Maximum slideshow attachment [HEIGHT in px]', '600', 'attachmentlimit_productslideshowsettings', 'int', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowmaxsize', 'Maximum slideshow attachment [FILESIZE in bytes]', '150000', 'attachmentlimit_productslideshowsettings', 'int', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_digitalfilemaxsize', 'Maximum digital file attachment [FILESIZE in bytes]', '1500000', 'attachmentlimit_productdigitalsettings', 'int', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_insertionfeesactive', 'Would you like to enable insertion fees to sellers that post new product listings?', '1', 'productupsell_fees', 'yesno', '', '', 'This is a global setting.  When enabled all pre-defined insertion fee groups and values within groups will be activated.  Additionally, if you set this setting to No, this will quickly disable the entire Insertion Fee system until you enable this again.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_slideshowcost', 'How much are sellers charged to upload each additional Slideshow Picture to their listing?', '0', 'productupsell_fees', 'int', '', '', 'When sellers list their item they can upload 1 free picture which is showcased in the search results.  If you would like to charge sellers for uploading more pictures (considered a slideshow) then enter the amount per each uploaded picture.  If you do not want to charge sellers for uploading slideshow pictures set this value to 0.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_reservepricecost', 'How much are sellers charged to set a Reserve Price amount on their listing?', '0', 'productupsell_fees', 'int', '', '', 'If you would like to charge sellers for setting a Reserve Price (a bid-price that must be met before the item can sell) on their listing (considered an enhancement) then enter the amount to charge.  If you do not want to charge sellers a fee for setting a Reserve Price amount on their listing set this value to 0.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_buynowcost', 'How much are sellers charged to set a Buy Now Fixed Price amount on their listing?', '0', 'productupsell_fees', 'int', '', '', 'If you would like to charge sellers for setting a Buy Now Fixed Price (a setting that lets buyers instantly purchase items to skip the bidding process) on their listing (considered an enhancement) then enter the amount to charge.  If you do not want to charge sellers a fee for setting a Buy Now Fixed Price amount on their listing set this value to 0.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_videodescriptioncost', 'How much are sellers charged to use Video Description URLs on their listing?', '0', 'productupsell_fees', 'int', '', '', 'If you would like to charge sellers for the use of videos within their listings (considered an enhancement) then enter the amount to charge.  If you do not want to charge sellers a fee for video descriptions set this value to 0.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotoextensions', 'Product auction main photo extensions enabled for uploading [separated by comma and 1 space]', '.jpg, .gif, .png', 'attachmentlimit_productphotosettings', 'textarea', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotomaxwidth', 'Maximum item photo attachment [WIDTH in px]', '1024', 'attachmentlimit_productphotosettings', 'int', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotomaxheight', 'Maximum item photo attachment [HEIGHT in px]', '768', 'attachmentlimit_productphotosettings', 'int', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotomaxsize', 'Maximum item photo attachment [FILESIZE in bytes]', '150000', 'attachmentlimit_productphotosettings', 'int', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_digitalfileextensions', 'Digital file extensions enabled for uploading [separated by comma and 1 space]', '.zip, .rar, .gz, .tar', 'attachmentlimit_productdigitalsettings', 'textarea', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowextensions', 'Slideshow item photo extensions enabled for uploading [separated by comma and 1 space]', '.gif, .jpg, .png', 'attachmentlimit_productslideshowsettings', 'textarea', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_merchantfixedprice', 'Would you like to charge sellers a <strong>fixed</strong> commission fee? (enter 0 to disable)', '0', 'escrowsystem', 'int', '', '', 'This setting will only work when safe escrow commission fees are enabled. This specific setting will let you charge sellers for encouraging bidders to pay sellers via safe escrow they have bid on and won. This setting is also based on a fixed-price amount only (format like 5.00). Note: this setting will take presidence over the percentage logic and cannot be mixed and matched.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_merchantpercentrate', 'Would you like to charge sellers a <strong>percentage</strong> commission fee? (enter 0.0 to disable)', '0.0', 'escrowsystem', 'int', '', '', 'This setting will only work when safe escrow commission fees are enabled. This specific setting will let you charge product sellers for receiving their funds for an auction they have sold via auction or buy now format. This setting is also based on a percentage value only and is calculated like (awarded bid amount * percentage you enter here / 100) (format like 2.5 not 2.5%). Note: this setting will take presidence over the fixed amount logic only if the fixed amount is set to 0 (disabled).', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_bidderfixedprice', 'Would you like to charge product buyers a <strong>fixed</strong> commission fee? (enter 0 to disable)', '0', 'escrowsystem', 'int', '', '', 'This setting will only work when safe escrow commission fees are enabled. This specific setting will let you charge product buyers for securing their funds for an auction item or buy now order they have won or purchased directly from a seller. This setting is also based on a fixed-price amount only (format like 5.00). Note: this setting will take presidence over the percentage logic and cannot be mixed and matched.', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_bidderpercentrate', 'Would you like to charge product buyers a <strong>percentage</strong> commission fee? (enter 0.0 to disable)', '0.0', 'escrowsystem', 'int', '', '', 'This setting will only work when safe escrow commission fees are enabled. This specific setting will let you charge product buyers for securing their funds for an auction item or buy now order they have won or purchased directly from a seller. This setting is also based on a percentage value only and is calculated like (item amount * percentage you enter here / 100) (format like 2.5 not 2.5%). Note: this setting will take presidence over the fixed amount logic only if the fixed amount is set to 0 (disabled).', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_boldactive', 'Would you like to enable the bold listing title option?', '1', 'serviceupsell_bold', 'yesno', '', '', 'When enabled this setting will let a user see the bold listing title option to enhance their listing when creating a new auction listing.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_boldfees', 'Can users pay to bold the title of their listing?', '1', 'serviceupsell_bold', 'yesno', '', '', 'When enabled this setting will let a user have the ability to pay to enhance their listing through the use of bold listing feature.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_boldfee', 'How much does it cost to use the bold listing feature?', '3.75', 'serviceupsell_bold', 'int', '', '', 'This setting works only when you have enabled the ability for users to pay to bold their listing.  For example, enter 5.00 if you would like to charge five dollars.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featuredactive', 'Can users feature their auction listing on the front homepage?', '1', 'serviceupsell_featured', 'yesno', '', '', 'If you would like to give users the ability to feature their auction listing on the homepage when creating a new listing please enable this setting.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featuredfees', 'Would you like to charge a fee to feature auction listings on the homepage?', '1', 'serviceupsell_featured', 'yesno', '', '', 'If you would like to give users the ability to pay you for featuring their listing when creating a new auction please enable this setting.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featuredfee', 'Homepage Featured listing fee amount', '2.75', 'serviceupsell_featured', 'int', '', '', 'This setting works only when you are charging members for featuring their listing on the homepage of the marketplace.  The amount you enter in this field can only be a fixed dollar amount.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_videodescriptioncost', 'How much are buyers charged to use Video Description URLs on their listing?', '0', 'serviceupsell_fees', 'int', '', '', 'If you would like to charge buyers for the use of videos within their listings (considered an enhancement) then enter the amount to charge.  If you do not want to charge buyers a fee for video descriptions set this value to 0.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioupsell_featuredlength', 'How long can a featured portfolio item last? (value in days)', '14', 'portfolioupsell', 'int', '', '', 'This setting allows you to define (in days) how long a featured portfolio item will stay featured for.  Default is 14 days.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featuredlength', 'How many days is a homepage featured listing valid for?', '5', 'productupsell_featured', 'int', '', '', 'If you enable homepage featured listings you can specify in this setting the duration in days a listing will remain featured on homepage of the marketplace for.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featuredlength', 'How many days is a homepage featured listing valid for?', '5', 'serviceupsell_featured', 'int', '', '', 'If you enable homepage featured listings you can specify in this setting the duration in days a listing will remain featured on homepage of the marketplace for.', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_p2bfeesfixed', 'Is the commission fee being generated from a provider to buyer invoice a fixed fee?', '0', 'invoicesystem', 'yesno', '', '', 'This setting only applies if you have enabled provider to buyer invoice generation fees.  If you would like to charge a fixed fee instead of a percentage (based on the invoice amount) please set this option to true.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_p2bfee', 'Enter the value used to calculate provider to buyer commission fees', '0', 'invoicesystem', 'int', '', '', 'If you have enabled provider to buyer invoice generation fee commission please enter the amount to calculate in this field.  If you are charging a fee based on a percentage value please format the value as 0.0.  If you are charging a fixed fee format it like 10.00 (as per example).', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_snipedurationcount', 'How many seconds before the auction finishes can anti-bid snipe execute?', '10', 'productbid_limits', 'int', '', '', 'This setting applies only when the anti-bid sniping feature is enabled.  This feature allows you to define the amount in seconds when the auction event is about to finish to allow anti-bid sniping to execute.  For example, if you enter 60, this would mean that anti-bid sniping would execute if a bid-sniper is placing a bid 60 seconds or less before the event finishes.', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_enabled', 'Would you like to enable the portfolio system?', '1', 'portfoliodisplay', 'yesno', '', '', 'This setting allows you to launch a portfolio area within the marketplace allowing users to upload their designs, media and more for review by guests, visitors and potential clients.  When enabled a Portolio link in the top header of the marketplace will be visible.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_cookiename', 'Enter the default cookie prefix', 'ilance_', 'globalsecuritymime', 'text', '', '', 'When a cookie is created this prefix will identify the core variables used to read and write from the users web browser.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('verificationlength', 'Verified profile answers duration length [enter value in days] the verified icon will be displayed', '365', 'verificationsystem', 'int', '', '', '', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('verificationupdateafter', 'Can members update their verified profile answers after successful verification payment?', '0', 'verificationsystem', 'yesno', '', '', '', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('verificationmoderation', 'Enable profile verification moderation? [admin verify manually via verification manager]?', '1', 'verificationsystem', 'yesno', '', '', '', 3, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxrowsdisplaysubscribers', 'Maximum number of results to fetch and display from the database for customer listings within AdminCP?', '10', 'globalfilterresults', 'int', '', '', 'This option communicates with your database and will let you define how many results to pull based on specific areas within the AdminCP.', 7, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_refresh', 'Would you like to enable the Refresh splash screen when an action has been performed on the marketplace?', '1', 'globalfilterresults', 'yesno', '', '', 'This option will produce a message to the user asking them to please wait while your request has been completed.  When disabled the refresh logic will immediately redirect the user without any message shown.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_cansendpms', 'Can members compose private messages to other members?', '0', 'globalfilterspmb', 'yesno', '', '', 'This feature is a global setting.  If you are positive that you do not want users to compose private messages to one another in the marketplace you should disable this setting.  Additionally, if enabled you can set subscription permissions on various plans which also determines if a user within a specific subscription plan can compose a private message.', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_showlivedepositfees', 'Show realtime deposit gateway fee calculation breakdown in deposit menu?', '0', 'invoicesystem', 'yesno', '', '', 'This setting allows you to let the user see (in realtime) the fees associated with the amount they are entering into the deposit funds form from the deposit menu.  This feature uses Javascript technology and updates itself on each character typed into the deposit field.', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('searchfloodprotect', 'Enable advanced search engine flood protection (can query advanced search every x seconds)?', '0', 'search', 'yesno', '', '', 'The majority of server processing and loading will be from guests, members and search bots looking for specific content using the advanced search system.  Resources are queried from the database (cat, counters, prices, bids, averages, etc) and can sometimes take a bit to process (depending on the query).  By enabling flood protection provides an even balance on the server on a per-session basis.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('searchflooddelay', 'If search engine flood protection is on how many seconds must each user wait between each advanced search request?', '20', 'search', 'int', '', '', 'This option will allow you to specify how many seconds a member or guest must wait before requesting a new search from the advance search menu.  This option is especially useful to reduce the server load.', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('fulltextsearch', 'Would you like to enable Fulltext search?', '1', 'search', 'yesno', '', '', 'Boolean fulltext mode queries became available in MySQL in version 4, and allow expressions to make use of a complex set of boolean rules to let users refine their searches. These queries are very powerful when applied to fulltext searching and sorting of results when enabled.', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('savedsearches', 'Would you like to enable Saved Searches?', '1', 'search', 'yesno', '', '', 'This feature allows logged in members the ability to save their search when searching for products or services.  Additionally, users can subscribe via email so when new matches are met they receive a new email with results.', 40, 1)");
	
	$defaultsearchoptions = 'a:17:{s:7:"perpage";s:2:"10";s:4:"sort";s:2:"01";s:6:"online";s:4:"true";s:15:"displayfeatured";s:4:"true";s:10:"showtimeas";s:6:"static";s:11:"description";s:4:"true";s:8:"proxybit";s:4:"true";s:4:"list";s:4:"list";s:15:"serviceselected";a:6:{i:0;s:5:"title";i:1;s:6:"budget";i:2;s:4:"bids";i:3;s:10:"averagebid";i:4;s:8:"timeleft";i:5;s:3:"sel";}s:15:"productselected";a:7:{i:0;s:6:"sample";i:1;s:5:"title";i:2;s:5:"price";i:3;s:4:"bids";i:4;s:8:"shipping";i:5;s:8:"timeleft";i:6;s:3:"sel";}s:14:"expertselected";a:8:{i:0;s:11:"profilelogo";i:1;s:6:"expert";i:2;s:11:"credentials";i:3;s:11:"rateperhour";i:4;s:8:"earnings";i:5;s:9:"portfolio";i:6;s:7:"country";i:7;s:3:"sel";}s:14:"latestfeedback";s:5:"false";s:8:"username";s:5:"false";s:5:"icons";s:5:"false";s:15:"currencyconvert";s:5:"false";s:10:"hidelisted";s:5:"false";s:11:"hideverbose";s:5:"false";}';
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('searchdefaultcolumns', 'This will contain specific code data used by the advanced search options menu.  Do not manually edit.', '" . $ilance->db->escape_string($defaultsearchoptions) . "', 'search', 'text', '', '', 'This option is used as a datastore for the presentation of the search results columns displayed in the marketplace.  Please do not edit this string.', 30, 0)");
	unset($defaultsearchoptions);
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('current_version', 'Framework version [do not change]', '" . ILANCEVERSION . "', 'globalsecuritymime', 'int', '', '', 'The framework version would only be updated if an upgrade procedure is performed. This value is updated automatically and should never be changed from this area.', 4, 0)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('current_sql_version', 'SQL framework version [do not change]', '" . SQLVERSION . "', 'globalsecuritymime', 'int', '', '', 'The sql framework version would only be updated if an upgrade procedure is performed. This value is updated automatically and should never be changed from this area.', 5, 0)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_bidretract', 'Can a product buyer retract their bid before the listing ends?', '0', 'productbid_limits', 'yesno', '', '', 'This setting allows you to define if a product buyer is able to retract their bid from a listing before the event ends.  When disabled, buyers cannot retract their bids before the listing ends.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_awardbidretract', 'Can a product buyer retract their winning bid?', '0', 'productbid_limits', 'yesno', '', '', 'This setting allows you to define if a product buyer is able to retract their winning bid after a product auction event has completed.  When disabled, buyers cannot retract their winning bids.', 20, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_1_stars_value', 'Minimum rating (out of 5) to show at least 1 star', '1', 'servicerating', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_1_stars_value', 'Maximum rating (out of 5) to show at least 1 star', '1.99', 'servicerating', 'int', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_2_stars_value', 'Minimum rating (out of 5) to show at least 2 stars', '2', 'servicerating', 'int', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_2_stars_value', 'Maximum rating (out of 5) to show at least 2 stars', '2.99', 'servicerating', 'int', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_3_stars_value', 'Minimum rating (out of 5) to show at least 3 stars', '3', 'servicerating', 'int', '', '', '', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_3_stars_value', 'Maximum rating (out of 5) to show at least 3 stars', '3.99', 'servicerating', 'int', '', '', '', 110, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_4_stars_value', 'Minimum rating (out of 5) to show at least 4 stars', '4', 'servicerating', 'int', '', '', '', 120, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_4_stars_value', 'Maximum rating (out of 5) to show at least 4 stars', '4.84', 'servicerating', 'int', '', '', '', 130, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_5_stars_value', 'Minimum rating (out of 5) to show at least 5 stars', '4.85', 'servicerating', 'int', '', '', '', 140, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_5_stars_value', 'Maximum rating (out of 5) to show at least 5 stars', '5', 'servicerating', 'int', '', '', '', 150, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enable_uniquebidding', 'Enable Lowest Unique Bid?', '0', 'globalauctionsettings', 'yesno', '', '', 'Lowest Unique Bidding offers your members the ability to bid on items that staff/admins create within the marketplace.  When enabled, users can bid multiple times on the same listing with the goal to be the lowest unique bidder for that listing.', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_bidretract', 'Can service providers retract their bid proposal before they are awarded by the buyer?', '0', 'servicebid_limits', 'yesno', '', '', 'This setting defines the ability to let pre-awarded service providers (placebid status only) retract (remove) their bid proposal.  If you do not want service providers to retract their bids before a buyer awards them set this feature to disabled.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_awardbidretract', 'Can service providers retract their bid proposal after they have been awarded by the buyer?', '0', 'servicebid_limits', 'yesno', '', '', 'This setting defines the ability to let awarded service providers to retract (remove) their bid proposal.  If you do not want service providers to retract their awarded bids set this feature to disabled.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_awardwaitperiod', 'If a buyer awarded a service provider how long should the marketplace wait for the provider to accept the buyers award (in days)?', '7', 'servicebid_limits', 'int', '', '', 'This setting ensures that a buyers project does not get a deadbeat provider delaying the project.  This setting automatically resets the buyers project to open if there is any time left and respectively declines the providers awarded bid so others can become awarded.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_buyerunaward', 'Can buyers unaward a providers bid proposal after it was awarded?', '0', 'servicebid_limits', 'yesno', '', '', 'This setting defines the ability to let buyers unaward already awarded service providers.  If you do not want service buyers to unaward an already awarded bid they have already comitted to set this feature to disabled.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_payperpost', 'Would you like to enable Pay as you Go?', '0', 'globalauctionsettings', 'yesno', '', '', 'When enabled, any insertion fees incured during the posting of an auction will need to be paid prior to public visibility. All unpaid auctions will be visible from the users pending auction area.', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_showfees', 'Would you like to enable the listing fees table during the posting of a listing?', '1', 'globalauctionsettings', 'yesno', '', '', 'When enabled, this feature will display the insertion fee table for service or product and/or budget based insertion fee table when a member is posting a new listing so fees are visibly shown.', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_endsoondays', 'How many hours until a listing is considered Ending Soon?', '7', 'globalauctionsettings', 'int', '', '', 'You can specify when an ending soon auction should be considered ending soon based on the following: [-1 = any date, 1 = 1 hour, 2 = 2 hours, 3 = 3 hours, 4 = 4 hours, 5 = 5 hours, 6 = 12 hours, 7 = 24 hours, 8 = 2 days, 9 = 3 days, 10 = 4 days, 11 = 5 days, 12 = 6 days, 13 = 7 days, 14 = 2 weeks, 15 = 1 month]', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catmapgenres', 'Would you like to display custom questions underneath categories when viewing the category map?', '0', 'globalcategorysettings', 'yesno', '', '', 'You can choose to enable the viewing of custom auction specifics underneath certain categories that contain item specifics on the category map listings.  If you would like to see them enable this setting.', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_newicondays', 'Enter the amount (in days) auction categories will display a new auction posted icon', '7', 'globalcategorysettings', 'int', '', '', 'When enabled, users will see a new listing icon beside the category representing new listings posted.  This icon is based on the amount of days you specify for this setting.  For example if you enter 3 a new listing icon will show for auctions posted up to 3 days ago as still newly listed.', 110, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catmapdepth', 'Enter the category depth level shown on category maps', '2', 'globalcategorysettings', 'int', '', '', 'On larger category marketplaces you can limit the subcategories to a certain level depth to prevent all categories being shown at once.  This allows larger result sets to be broken down on a category by category basis. For example, if you enter 2 then Level 1 > Level 2 will be shown even if you had 10 levels in this category.', 130, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catquestiondepth', 'How many custom category questions to display until a More link becomes activated?', '3', 'globalcategorysettings', 'int', '', '', 'This setting is useful when one or more particular categories have 5 or more custom category questions in them.  Instead of crowding the page this feature will present the user with a More link based on the value you enter.', 140, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catmapgenredepth', 'How many levels deep will the category questions be visible in the category maps?', '1', 'globalcategorysettings', 'int', '', '', 'This feature allows you to determine how many subcategory levels deep you wish to pull custom category specifics from when users are viewing the category map pages.', 160, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_showcurrentcat', 'Would you like the selected category to show in the left nav?', '1', 'globalcategorysettings', 'yesno', '', '', 'This option controls the ability to show the currently selected category in bold from the left nav showing any subcategories below. Disabling this feature will not show the user the currently selected category at all and will only show subcategories.', 170, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catcutoff', 'How many categories to display until a more option link becomes visble', '10', 'globalcategorysettings', 'int', '', '', 'This feature can really help the look and feel of your category map pages.  If you have many categories, you can define how many of them will show (from top to bottom) before the rest become hidden and a More link becomes visible allowing the user to see the rest.', 180, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_showbackto', 'Would you like to show a (Back To: [cat]) link in the left nav menu search results?', '1', 'globalcategorysettings', 'yesno', '', '', 'This feature simply gives the seacher more control over viewing the category structure from the search results.  This will show a clickable link allowing the user to move back a category if viewing more than level 1 parent categories.', 190, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_popups_width', 'Maximum width to display when viewing portfolio image in popup mode?', '490', 'portfoliodisplay', 'int', '', '', 'This setting lets you define the maximum width of a portfolio image when viewing in popup window.', 5, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_popups_height', 'Maximum height to display when viewing portfolio image in popup mode?', '410', 'portfoliodisplay', 'int', '', '', 'This setting lets you define the maximum height of a portfolio image when viewing in popup window.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_defaultcity', 'Default city name to display on registration form?', 'Toronto', 'registrationdisplay', 'text', '', '', 'When users are viewing the registration form for the first time, which major City would you like displayed by default?', 4, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_feestaxable', 'Would you like to enable tax on escrow commission fees?', '0', 'escrowsystem', 'yesno', '', '', 'This setting will only work when commission fee invoice types are enabled from your tax zone manager within payment modules area.  When enabled all fees generated will include applicable taxes as well (as long as the user being taxed is within the region defined within the tax zone manager).', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('clean_old_log_entries', 'Delete log entries after x days (0 to disable)', '0', 'globalsecuritysettings', 'int', '', '', 'This option is especially useful to keep your database pruned and orgainzed by removing older logs after so many days in which you define.  By default, this option is disabled.', 101, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('showfeaturedlistings', 'Show featured listings on the homepage?', '1', 'globalauctionsettings', 'yesno', '', '', 'When enabled, a result set of featured-status service and/or product listings will be presented on the homepage.', 100, 1)");
        $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('showendingsoonlistings', 'Show ending soon listings on the homepage?', '1', 'globalauctionsettings', 'yesno', '', '', 'When enabled, a result set of ending soon status service and/or product listings will be presented on the homepage.', 110, 1)");
        $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('showlatestlistings', 'Would you like to show latest listings posted on the homepage?', '1', 'globalauctionsettings', 'yesno', '', '', 'When enabled, a live scroller result set of latest listings posted will be presented on the homepage.', 120, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('refreshcsscache', 'Would you like to enable CSS cache-file regeneration?', '1', 'template', 'yesno', '', '', 'This setting requires enabling CSS style sheets externally. When enabled, the css style sheets will overwrite itself every few minutes to stay current with any changes you might be making within the CSS style manager.  If you are working on your own CSS style sheet and never want it to be erased or overwritten please disable this setting.', 2, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('externaljsphrases', 'Would you like to enable external javascript phrase cache file?', '0', 'language', 'yesno', '', '', 'This setting will let you decide if you want to use an external javascript file for holding phrases used in Javascript functions.  When disabled, Javascript phrases will be stored inline with the page document and will not load an external cache file.  In most cases, an external cache file results in quicker load times.', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorymapcache', 'Would you like to enable category template caching?', '0', 'globalcategorysettings', 'yesno', '', '', 'This feature will create html templates based on your category system within the ./cache/ speeding up the category maps and pulldown menus when displayed.  This feature will drastically help improve the speed of your marketplace if you have a lot of categories and many users browsing your site per day.', 300, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorymapcachetimeout', 'Enter time to live (in minutes) for category template caching regeneration', '30', 'globalcategorysettings', 'int', '', '', 'This option will tell the caching engine to rebuild and regenerate the cached category map templates within the server cache folder every x minutes.  This is useful if you have many categories and more than 1000 unique visitors per day and will reduce the load on the database extremely well.  A regenerated category template will also refresh the category counters if they are enabled within category maps.', 310, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serveroverloadlimit', 'Enter the overload limit on this server before a notice is presented to users informing them to retry the site later (0 to disable)', '0', 'globalsecuritysettings', 'int', '', '', 'You can choose to provide your users will a helpful message informing them to retry the site later based on a server overload feature available on web servers running any flavour of Linux.  This feature does not work on Windows.  To enable, enter your max-load server limit.', 400, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('multilevelpulldown', 'Would you like to show all subcategories within the pulldown menus?', '0', 'globalcategorysettings', 'yesno', '', '', 'This feature should be enabled if your category system is small (ie: less than 50 categories).  If you have over 50 categories and you enable this feature, it will provide your users with a very fast pulldown menu showing only base root categories.', 320, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enableskills', 'Would you like to enable the Skills system?', '1', 'skills', 'yesno', '', '', 'This option will allow users to make use of the skills system.  They can opt into various skill categories and can be searched based on skills from the advanced search menu.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablepopulartags', 'Would you like to display the popular search tags on the main menu?', '1', 'globalfilterresults', 'yesno', '', '', 'The popular search tags feature produces realtime keyword search tags queried within the marketplace.', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('resetpopulartags', 'Would you like to reset popular tags every month?', '1', 'globalfilterresults', 'yesno', '', '', 'To prevent abuse and keyword stuffing by users you can reset popular tags on a monthly basis.', 100, 1)");
        $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('populartagcount', 'How many searches does it take before a tag becomes popular?', '30', 'globalfilterresults', 'int', '', '', 'This setting lets you define how many times a keyword tag needs to be searched by users in the system before it becomes popular and displayed within the popular search tag area.', 200, 1)");
        $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('populartaglimit', 'How many popular keyword to display at once?', '50', 'globalfilterresults', 'int', '', '', 'This setting lets you define how many actual popular keyword tags will be displayed at any given time when users are viewing the popular keyword tags.', 300, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('showadmincpnews', 'Would you like to enable News from ILance on the Dashboard?', '1', 'globalfilterresults', 'yesno', '', '', 'This setting will only pull latest news from ILance and when news is available a tab will be displayed on your main admin control panel dashboard letting you view the latest news from Team ILance.', 400, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('template_metatitle', 'Template Meta Tag Page Title', 'Bid on new and used electronics, clothing, automobiles and more', 'metatags', 'textarea', '', '', 'Your meta tag page title should contain a global slogan or statement regarding the overall purpose of the marketplace, offering or business.  This page title will appear on the main marketplace landing page.', 400, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('template_metadescription', 'Template Meta Tag Description', '24 x 7 Auction Marketplace - Post your listings today', 'metatags', 'textarea', '', '', 'Your meta tag description should contain information about your marketplace, offering and other aspects of your business.  This meta description will appear everywhere except for instances where a custom description would apply (viewing a listing, searching the marketplace, category maps, etc).', 500, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('template_metakeywords', 'Template Meta Tag Keywords', 'auction, reverse, marketplace, jobs, post a job, easy, providers, buy now, e-commerce', 'metatags', 'textarea', '', '', 'Your meta tag keywords should contain global keywords that directly relate to your offering or business.  These keywords will appear everywhere except for instances where a custom keyword would apply (searching the marketplace, category maps, etc).', 600, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablenonprofits', 'Would you like to enable the Nonprofit Charity system?', '0', 'nonprofits', 'yesno', '', '', 'This option will allow sellers to choose a donation percentage and a nonprofit orgainzation. When enabled, the seller can choose if they will donate or not during the posting of their listing.  This feature is optional and is not forced upon your sellers.', 700, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkupload', 'Would you like to enable Bulk Uploading?', '0', 'globalfiltersrfp', 'yesno', '', '', 'When enabled, sellers can choose to post their new items for sale via Bulk Upload.  This requires the seller to upload a .csv (comma separated value) file with pre-filled in values.  For example, in one upload session a seller could potentially upload 1000 listings in a matter of seconds.', 6, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkuploadlimit', 'Maximum number of listings per Bulk Upload session?', '1000', 'globalfiltersrfp', 'int', '', '', 'This setting works only when Bulk Uploading is enabled.  This setting will define how many listings can be uploaded by any given user at any given time on a per bulk-import basis (meaning they can upload another xxx listings in a new bulk upload session).', 7, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicecatschema', 'Search Engine Friendly Service Category Listing Schema', '{HTTP_SERVER}{IDENTIFIER}/{CID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'textarea', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly service category listing url schema.', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productcatschema', 'Search Engine Friendly Product Category Listing Schema', '{HTTP_SERVER}{IDENTIFIER}/{CID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'textarea', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly product category listing url schema.', 200, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicecatidentifier', 'Service Category URL Identifier', 'Projects', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for service categories.  Default is Projects.  Example output: domain.com/projects', 300, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productcatidentifier', 'Product Category URL Identifier', 'Items', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for product categories.  Default is Items.  Example output: domain.com/items', 400, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicecatmapidentifier', 'Service Category Map URL Identifier', 'Categories/Projects', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for service category maps.  Default is Categories/Projects.  Example output: domain.com/categories/projects', 500, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productcatmapidentifier', 'Product Category Map URL Identifier', 'Categories/Items', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for product category maps.  Default is Categories/Items.  Example output: domain.com/categories/items', 600, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categoryidentifier', 'Main Category URL Identifier', 'Categories', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for the main category index map.  Default is Categories.  Example output: domain.com/categories', 700, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('listingsidentifier', 'Main Listings URL Identifier', 'Listings', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for the main listings index map.  Default is Listings.  Example output: domain.com/listings', 800, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicelistingschema', 'Search Engine Friendly Service Auction Listing Schema', '{HTTP_SERVER}{IDENTIFIER}/{ID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'textarea', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly service auction listing url schema.', 900, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productlistingschema', 'Search Engine Friendly Product Auction Listing Schema', '{HTTP_SERVER}{IDENTIFIER}/{ID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'textarea', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly product auction listing url schema.', 1000, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicelistingidentifier', 'Service Auction URL Identifier', 'Project', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for service auction listings.  Default is Project.  Example output: domain.com/project', 1100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productlistingidentifier', 'Product Auction URL Identifier', 'Item', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for product auction listings.  Default is Item.  Example output: domain.com/item', 1200, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('expertslistingidentifier', 'Experts Category URL Identifier', 'Experts', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for expert category listings.  Default is Experts.  Example output: domain.com/experts', 1300, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('memberslistingidentifier', 'Members Profile/Feedback URL Identifier', 'Members', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for viewing members including feedback history and profile detail specifics.  Default is Members.  Example output: domain.com/members', 1400, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioslistingidentifier', 'Portfolios URL Identifier', 'Portfolios', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for viewing portfolios. Default is Portfolios.  Example output: domain.com/portfolios', 1500, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorylinkheaderpopup', 'Would you like to display a pop-out menu when a users mouse hovers over Categories link from the top nav menu?', '0', 'globalcategorysettings', 'yesno', '', '', 'This setting can ultimately lead to bandwidth saving where root categories can be quickly seen and clicked on without additional page loading from anywhere within the marketplace', 1600, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorylinkheaderpopuptype', 'If the category pop-out menu is active enter the category type to show (service or product)', 'product', 'globalcategorysettings', 'text', '', '', 'Since the category pop-out menu cannot show both category systems in a single pop-out please enter either service or product in the field to show one category type or another when the users mouse hovers over the category link within the top nav header menu.  Default value is product.', 1700, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorymainsingleleftnavcount', 'Would you like to display category listing counters on the main marketplace menu in the left nav?', '0', 'globalcategorysettings', 'yesno', '', '', 'You can enable or disable main menu category listing counters within the left nav menu.  Default is disabled.', 1800, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maxshipservices', 'Maximum number of ship services allowed for item listings?', '5', 'shippingsettings', 'int', '', '', 'Default is 5 shipping services.', 1900, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('shippingapi', 'Would you like to enable the Research Shipping Rates API?', '0', 'shippingsettings', 'yesno', '', '', 'When enabled, a link will be presented beside the shipping services pulldown when sellers are posting an item.  This feature allows sellers to research shipping rates based on the integrated shipping calculator.  You will need to define your shipping api user and password details in your config.php file.  Currently supported carriers include FedEx, UPS and USPS.', 1910, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enableauctiontab', 'Would you like to enable the auction format tab when sellers post an item?', '1', 'globalauctionsettings', 'yesno', '', '', 'When enabled, sellers can click on the auction tab from their selling format section allowing buyers to place bids on their listings.  When disabled, the auction format tab will not be visible when selling an item.', 130, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablefixedpricetab', 'Would you like to enable the fixed priced tab when sellers post an item?', '1', 'globalauctionsettings', 'yesno', '', '', 'When enabled, sellers can click on the fixed price tab from their selling format section allowing buyers to purchase their items directly from the item listing page. When disabled, the fixed price tab will not be visible when selling an item.', 140, 1)");
	
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default configuration settings . .</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "configuration_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "configuration_groups (
		`parentgroupname` VARCHAR(250) NOT NULL default '',
		`groupname` VARCHAR(250) NOT NULL default '',
		`description` MEDIUMTEXT,
		`help` MEDIUMTEXT,
		`sort` INT(5) NOT NULL default '0',
		PRIMARY KEY  (`groupname`),
		INDEX ( `parentgroupname` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "configuration_groups</li>";
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('maintenance', 'maintenance', 'Marketplace Maintenance Mode Configuration Settings', '', '10')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('servicerating', 'servicerating', 'Service Rating Formula Logic', '', '20')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_bold', 'Bold Upsell Listing Features', '', '50')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_featured', 'Featured Homepage Upsell Listing Features', '', '60')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_highlight', 'Highlight Upsell Listing Features', '', '70')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_autorelist', 'Auto-Relist Upsell Listing Features', '', '80')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_bold', 'Bold Upsell Listing Features', '', '50')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_featured', 'Featured Homepage Upsell Listing Features', '', '60')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_highlight', 'Highlight Upsell Listing Features', '', '70')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_autorelist', 'Auto-Relist Upsell Listing Features', '', '80')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productaward', 'productaward_pmb', 'Product Auction PMB Award Process and Settings', '', '80')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productaward', 'productaward_mediashare', 'Product Auction Mediashare Award Process and Settings', '', '90')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productbid', 'productbid_limits', 'Product Bidding Limits and Settings', '', '100')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('portfoliodisplay', 'portfoliodisplay', 'Portfolio settings and display options', '', '110')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('portfolioupsell', 'portfolioupsell', 'Portfolio upsell and listing fee enhancements', '', '120')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('registrationdisplay', 'registrationdisplay', 'Registration settings and display options', '', '130')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('registrationupsell', 'registrationupsell', 'Registration upsell and other enhancements', '', '140')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('referalsystem', 'referalsystem', 'Referal system and settings', '', '150')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentsystem', 'attachmentsystem', 'Attachment system settings', '', '160')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentmoderation', 'attachmentmoderation', 'Attachment Moderation settings', '', '170')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_profileextensions', 'Profile attachment upload settings', '', '180')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_defaultextensions', 'Default attachment upload settings', '', '190')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('escrowsystem', 'escrowsystem', 'Escrow Settings and Configuration', 'You can manage all aspects of the Escrow system from within this area', '200')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_portfolioextensions', 'Portfolio attachment upload settings', '', '210')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('invoicesystem', 'invoicesystem', 'Invoice and Transaction Settings', 'You can manage all aspects of invoices, transactions and email reminder frequency settings from within this area', '220')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserversettings', 'globalserversettings', 'Marketplace company and web site settings', 'From this area you can manage various details about your company including email settings', '230')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserverlocalecurrency', 'globalserverlocalecurrency', 'Global currency locale', '', '240')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserverlocale', 'globalserverlocale', 'Date and Timezone Configuration', 'Manage the date and timezone configuration settings from within this area', '250')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserverdistanceapi', 'globalserverdistanceapi', 'Distance Calculation API', 'You can enable the integrated distance calculation system from within this area', '260')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfilterspmb', 'Private Message Board Settings', 'Manage private message board settings and configuration options from this area', '270')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalsecurity', 'globalsecuritysettings', 'Global security settings', 'Manage various security settings from template encryption to proxy registration blocking within this area', '280')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalsecurity', 'globalsecurity', 'Global login security settings', 'Manage failed login reports and email notification from within this area', '290')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalsecurity', 'globalsecuritymime', 'Global application security settings', 'Global application settings can be managed from this area and should be handled by a programmer', '300')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfiltersrfp', 'Auction Listing and Privacy Filters', 'Manage options to hide email addresses and/or domain names when viewing or creating auction listings', '310')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfiltersbid', 'Bid Proposal Privacy Filters', 'Manage options to hide email addresses and/or domain names when placing or viewing bid proposals', '320')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfiltersvulgar', 'Vulgar Words Blacklist', 'Manage a list of blacklisted words and phrases that should be filtered within the marketplace', '330')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfiltersipblacklist', 'IP Address Blacklist', 'Manage a list of blacklisted IP addresses that are never allowed access to the marketplace', '340')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilterresults', 'globalfilterresults', 'Marketplace Results and Display Settings', 'Manage core marketplace settings like WYSIWYG BBEditor and BBCode emulation to category counters from within this area', '350')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalauctionsettings', 'globalauctionsettings', 'Global Marketplace Settings', '', '360')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('connections', 'connections', 'Marketplace Connections / Whos Online Settings', '', '380')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('template', 'template', 'Template and Cosmetic Settings', '', '390')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_fees', 'Forward Auction Fees and Other Related Costs', '', '400')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('verificationsystem', 'verificationsystem', 'Verification System Settings', '', '410')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('search', 'search', 'Search Engine Settings', 'Manage search engine configuration options and settings within this area', '420')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('servicebid', 'servicebid_limits', 'Service Bidding Settings', '', '430')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('language', 'language', 'Language Settings', '', '440')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('skills', 'skills', 'Skills Options and Settings', 'Skills provide members the ability to precisely define their expertise via skill categories that you create.  End users can use the advanced search system to narrow down specific expertise as required.', '450')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('metatags', 'metatags', 'Meta Tag Settings', 'Manage search engine meta tag data within this area', '460')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('nonprofits', 'nonprofits', 'Nonprofit Settings', 'Manage Nonprofits and other related settings', '470')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalseo', 'globalseo', 'Search Engine Optimization Manager', 'Manage SEO settings and define various URL schemas', '480')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_fees', 'Reverse Auction Fees and Other Related Costs', '', '490')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalcategorysettings', 'globalcategorysettings', 'Global Category Settings', '', '500')");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_productphotosettings', 'Product Auction Picture Settings', '', '220')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_productslideshowsettings', 'Product Auction Slideshow Picture Settings', '', '230')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_productdigitalsettings', 'Product Auction Digital File Attachment Settings', '', '240')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_searchresultsettings', 'Search Results Picture and Thumbnail Settings', '', '250')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_bidsettings', 'Service Auction Bid Attachment Settings', '', '260')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_pmbsettings', 'Private Message Board (PMB) Attachment Settings', '', '270')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_workspacesettings', 'Mediashare / Workspace Attachment Settings', '', '280')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingsettings', 'shippingsettings', 'Shipping Settings', 'Manage shipping services, settings and other aspects of shipping from here', '290')");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default configuration groups . .</strong></li></ul>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "creditcards");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "creditcards (
		`cc_id` INT(100) NOT NULL AUTO_INCREMENT,
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_updated` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`user_id` INT(100) NOT NULL default '0',
		`creditcard_number` VARCHAR(250) NOT NULL default '',
		`creditcard_expiry` VARCHAR(10) NOT NULL default '',
		`cvv2` VARCHAR(30) NOT NULL default '',
		`name_on_card` VARCHAR(100) NOT NULL default '',
		`phone_of_cardowner` VARCHAR(50) NOT NULL default '',
		`email_of_cardowner` VARCHAR(75) NOT NULL default '',
		`card_billing_address1` VARCHAR(200) NOT NULL default '',
		`card_billing_address2` VARCHAR(200) default NULL,
		`card_city` VARCHAR(100) NOT NULL default '',
		`card_state` VARCHAR(100) NOT NULL default '',
		`card_postalzip` VARCHAR(50) NOT NULL default '',
		`card_country` VARCHAR(100) NOT NULL default '',
		`creditcard_status` VARCHAR(200) NOT NULL default '',
		`default_card` VARCHAR(5) NOT NULL default '',
		`creditcard_type` VARCHAR(10) NOT NULL default '',
		`authorized` VARCHAR(5) NOT NULL default '',
		`auth_amount1` FLOAT(10,2) NOT NULL default '0.00',
		`auth_amount2` FLOAT(10,2) NOT NULL default '0.00',
		`attempt_num` VARCHAR(10) default NULL,
		`trans1_id` VARCHAR(150) NOT NULL default '',
		`trans2_id` VARCHAR(150) NOT NULL default '',
		PRIMARY KEY  (`cc_id`),
		INDEX ( `user_id` ),
		INDEX ( `creditcard_number` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "creditcards</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cron");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "cron (
		`cronid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		`nextrun` INT UNSIGNED NOT NULL DEFAULT '0',
		`weekday` SMALLINT NOT NULL DEFAULT '0',
		`day` SMALLINT NOT NULL DEFAULT '0',
		`hour` SMALLINT NOT NULL DEFAULT '0',
		`minute` VARCHAR(100) NOT NULL DEFAULT '',
		`filename` CHAR(50) NOT NULL DEFAULT '',
		`loglevel` SMALLINT NOT NULL DEFAULT '0',
		`active` SMALLINT NOT NULL DEFAULT '1',
		`varname` VARCHAR(100) NOT NULL DEFAULT '',
		`product` VARCHAR(200) NOT NULL DEFAULT '',
		PRIMARY KEY (cronid),
		KEY nextrun (nextrun),
		UNIQUE KEY (varname),
		INDEX (`product`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "cron</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "cron
		(nextrun, weekday, day, hour, minute, filename, loglevel, varname, product)
		VALUES
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.subscriptions.php', 1, 'subscriptions', 'ilance'),
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.rfp.php',	      1, 'rfp', 'ilance'),
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:30;}', 'cron.reminders.php',     1, 'reminders', 'ilance'),
		(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.currency.php',      1, 'currency', 'ilance'),
		(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.dailyreports.php',  0, 'dailyreports', 'ilance'),
		(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.dailyrfp.php',      1, 'dailyrfp', 'ilance'),
		(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.creditcards.php',   1, 'creditcards', 'ilance'),
		(1053271600, -1,  1, -1, 'a:1:{i:0;i:0;}',  'cron.monthly.php',       1, 'monthly', 'ilance'),
		(1053271600, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.watchlist.php',     1, 'watchlist', 'ilance'),
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:30;}', 'cron.bulk_photos.php',   1, 'bulk_photos', 'ilance')
	");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Building default cron job tasks . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cronlog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "cronlog (
		cronlogid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		varname VARCHAR(100) NOT NULL DEFAULT '',
		dateline INT UNSIGNED NOT NULL DEFAULT '0',
		description MEDIUMTEXT,
		PRIMARY KEY (cronlogid),
		KEY (varname),
		INDEX (`dateline`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "cronlog</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "currency");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "currency (
		`currency_id` INT(100) NOT NULL AUTO_INCREMENT,
		`currency_abbrev` VARCHAR(10) NOT NULL default '',
		`currency_name` VARCHAR(50) NOT NULL default '',
		`rate` VARCHAR(10) NOT NULL default '',
		`time` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`isdefault` INT(1) NOT NULL default '0',
		`symbol_left` VARCHAR(20) NOT NULL default '$',
		`symbol_right` VARCHAR(20) NOT NULL default '',
		`decimal_point` VARCHAR(5) NOT NULL default '.',
		`thousands_point` VARCHAR(5) NOT NULL default ',',
		`decimal_places` VARCHAR(5) NOT NULL default '2',
		PRIMARY KEY  (`currency_id`),
		INDEX ( `currency_abbrev` ),
		INDEX ( `currency_name` )
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "currency</li>";
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (1, 'USD', 'US DOLLAR', '1.3216', '2005-03-01 10:17:47', 1, 'US$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (2, 'ISK', 'ICELANDIC KRONA', '78.64', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (3, 'AUD', 'AUSTRALIAN DOLLAR', '1.6763', '2005-03-01 10:17:47', 1, 'AU$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (4, 'BGN', 'BULGARIA LEVA', '1.9559', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (5, 'CZK', 'CZECH KORUNA', '29.955', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (6, 'GBP', 'BRITISH POUND', '0.68790', '2005-03-01 10:17:47', 1, '&pound;', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (7, 'CAD', 'CANADIAN DOLLAR', '1.6306', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (8, 'EEK', 'ESTONIAN KROON', '15.6466', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (9, 'CYP', 'CYPRUS POUND', '0.5834', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (10, 'HUF', 'HUNGARIAN FORINT', '247.20', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (11, 'DKK', 'DANISH KRONE', '7.4420', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (12, 'LTL', 'LITHUANIA LITAS', '3.4528', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (13, 'LVL', 'LATIVA LAT', '0.6960', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (14, 'EUR', 'EURO', '1.0000', '2005-03-01 10:17:47', 1, '&euro; ', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (15, 'PLN', 'POLAND ZLOTYCH', '4.0807', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (18, 'HKD', 'HONG KONG DOLLAR', '10.3082', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (22, 'JPY', 'JAPANESE YEN', '137.90', '2005-03-01 10:17:47', 1, '&yen;', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (24, 'MTL', 'MALTESE LIRA', '0.4311', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (27, 'NZD', 'NEW ZEALAND DOLLAR', '1.8201', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (28, 'NOK', 'NORWEGIAN KRONE', '8.2120', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (32, 'ZAR', 'RAND', '7.7193', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (37, 'SGD', 'SINGAPORE DOLLAR', '2.1448', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (39, 'SEK', 'SWEDISH KRONA', '9.0517', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (40, 'CHF', 'SWISS FRANC', '1.5357', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (41, 'RON', 'ROMANIA NEW LEI', '3.3751', '2007-03-01 10:17:47', 1, 'RON', '', '.', ',', '2')");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default currencies and rates . .</strong></li></ul>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cache");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "cache (
		`title` VARCHAR(255) NOT NULL default '',
		`data` MEDIUMTEXT,
		`datetime` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`title`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "cache</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_au");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_au (
		`ZIPCode` INT(5) NOT NULL,
		`City` MEDIUMTEXT,
		`State` MEDIUMTEXT,
		`Longitude` DOUBLE NOT NULL default '0',
		`Latitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_au</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_be");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_be (
		`ZIPCode` INT(5) NOT NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_au</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_canada");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_canada (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`City` MEDIUMTEXT,
		`Province` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_canada</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_de");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_de (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_de</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_fr");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_fr (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_fr</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_in");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_in (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "");
	flush();
	echo "<li>" . DB_PREFIX . "distance_in</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_it");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_it (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_it</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_jp");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_jp (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_jp</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_pl");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_pl (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_pl</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_sp");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_sp (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_sp</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_uk");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_uk (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_uk</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_usa");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_usa (
		`ZIPCode` CHAR(10) NOT NULL default '',
		`ZIPCodeType` CHAR(5) NOT NULL default '',
		`City` CHAR(50) NOT NULL default '',
		`CityType` CHAR(5) NOT NULL default '',
		`State` CHAR(50) NOT NULL default '',
		`StateCode` CHAR(10) NOT NULL default '',
		`AreaCode` CHAR(10) NOT NULL default '',
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_usa</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_nl");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_nl (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`Latitude` VARCHAR(150) NOT NULL default '0',
		`Longitude` VARCHAR(150) NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_nl</li>";
	
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:red\"><strong>Could not find distance data to import; skipping import . .</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "email");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "email (
		`id` INT(200) UNSIGNED NOT NULL AUTO_INCREMENT,
		`varname` VARCHAR(100) NOT NULL default '',
		`name` VARCHAR(255) NOT NULL default '',
		`subject_original` MEDIUMTEXT,
		`message_original` MEDIUMTEXT,
		`subject_eng` MEDIUMTEXT,
		`message_eng` MEDIUMTEXT,
		`type` ENUM('global','service','product') NOT NULL default 'global',
		`product` VARCHAR(100) NOT NULL default 'ilance',
		`cansend` INT(1) NOT NULL default '1',
		`departmentid` INT(5) NOT NULL default '1',
		PRIMARY KEY  (`id`),
		INDEX ( `varname` ),
		INDEX ( `name` ),
		INDEX ( `type` ),
		INDEX ( `product` ),
		INDEX ( `departmentid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "email</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "emaillog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "emaillog (
		`emaillogid` INT(10) NOT NULL AUTO_INCREMENT,
		`logtype` ENUM('escrow','subscription','subscriptionremind','send2friend','alert','queue','dailyservice','dailyproduct','dailyreport','dailyfavorites','watchlist') NOT NULL default 'alert',
		`user_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`email` VARCHAR(60) NOT NULL default '',
		`subject` VARCHAR(250) NOT NULL default '',
		`body` MEDIUMTEXT,
		`date` DATE NOT NULL default '0000-00-00',
		`sent` ENUM('yes','no') NOT NULL default 'no',
		PRIMARY KEY (`emaillogid`),
		INDEX ( `logtype` ),
		INDEX ( `user_id` ),
		INDEX ( `project_id` ),
		INDEX ( `sent` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "emaillog</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "email_departments");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "email_departments (
		`departmentid` INT(10) NOT NULL AUTO_INCREMENT,
		`title` MEDIUMTEXT,
		`email` VARCHAR(250) NOT NULL default '',
		`canremove` INT(1) NOT NULL default '1',
		PRIMARY KEY (`departmentid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "email_departments</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "email_departments
		(`departmentid`, `title`, `email`, `canremove`)
		VALUES
		(NULL, '" . addslashes($_SESSION['site_name']) . "', '" . addslashes($_SESSION['site_email']) . "', 0)
	");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default email department . .</strong></li></ul>";
	
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "failed_logins");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "failed_logins (
		`id` INT(255) NOT NULL AUTO_INCREMENT,
		`attempted_username` VARCHAR(100) NOT NULL default '',
		`attempted_password` VARCHAR(100) NOT NULL default '',
		`referrer_page` VARCHAR(200) NOT NULL default '',
		`ip_address` VARCHAR(20) NOT NULL default '',
		`datetime_failed` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "failed_logins</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "feedback");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "feedback (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`for_user_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`from_user_id` INT(10) NOT NULL default '0',
		`comments` mediumtext,
		`date_added` datetime NOT NULL default '0000-00-00 00:00:00',
		`response` enum('','positive','neutral','negative') NOT NULL default '',
		`type` enum('','buyer','seller') NOT NULL,
		PRIMARY KEY  (`id`),
		INDEX (`for_user_id`),
		INDEX (`from_user_id`),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "feedback</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "feedback_criteria");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "feedback_criteria (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`title_eng` MEDIUMTEXT,
		`sort` INT(5) NOT NULL,
		PRIMARY KEY  (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "feedback_criteria</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "feedback_criteria
		(`id`, `title_eng`, `sort`)
		VALUES
		(NULL, 'Item as described', 10),
		(NULL, 'Professionalism', 20),
		(NULL, 'Quality', 30),
		(NULL, 'Delivery', 40),
		(NULL, 'Price', 50),
		(NULL, 'Communication', 60),
		(NULL, 'Shipping time', 70);
	");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default feedback rating criteria . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "feedback_ratings");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "feedback_ratings (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`criteria_id` INT(10) NOT NULL default '0',
		`rating` DOUBLE NOT NULL,
		PRIMARY KEY  (`id`),
		INDEX ( `user_id` ),
		INDEX ( `project_id` ),
		INDEX ( `criteria_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "feedback_ratings</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "feedback_response");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "feedback_response (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`feedbackid` INT(10) NOT NULL default '0',
		`for_user_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`from_user_id` INT(10) NOT NULL default '0',
		`comments` mediumtext,
		`date_added` datetime NOT NULL default '0000-00-00 00:00:00',
		`type` enum('','buyer','seller') NOT NULL,
		PRIMARY KEY  (`id`),
		INDEX ( `feedbackid` ),
		INDEX ( `for_user_id` ),
		INDEX ( `project_id` ),
		INDEX ( `from_user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "feedback_response</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "finalvalue");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "finalvalue (
		`tierid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`finalvalue_from` FLOAT(10,2) NOT NULL default '0.00',
		`finalvalue_to` FLOAT(10,2) NOT NULL default '0.00',
		`amountfixed` FLOAT(10,2) NOT NULL default '0.00',
		`amountpercent` VARCHAR(10) NOT NULL default '',
		`state` ENUM('service','product') NOT NULL default 'service',
		`sort` INT(5) NOT NULL default '0',
		PRIMARY KEY  (`tierid`),
		INDEX ( `groupname` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "finalvalue</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "finalvalue
		(`tierid`, `groupname`, `finalvalue_from`, `finalvalue_to`, `amountfixed`, `amountpercent`, `state`, `sort`)
		VALUES
		(1, 'default', '0.01', '250.00', '0', '15.0', 'service', 10),
		(2, 'default', '250.01', '500.00', '0', '10.0', 'service', 20),
		(3, 'default', '500.01', '1000.00', '0', '5.0', 'service', 30),
		(4, 'default', '1000.01', '-1', '0', '1.25', 'service', 40),
		(5, 'default', '0.01', '250.00', '0', '17.0', 'product', 10),
		(6, 'default', '250.01', '500.00', '0', '10.0', 'product', 20),
		(7, 'default', '500.01', '1000.00', '0', '5.0', 'product', 30),
		(8, 'default', '1000.01', '-1', '0', '1.25', 'product', 40)
	");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default final value fees . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "finalvalue_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "finalvalue_groups (
		`groupid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`description` MEDIUMTEXT,
		`state` ENUM('service','product') NOT NULL default 'service',
		KEY `groupid` (`groupid`),
		INDEX ( `groupname` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "finalvalue_groups</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "finalvalue_groups
		(`groupid`, `groupname`, `description`, `state`)
		VALUES
		(1, 'default', 'This service final value group will hold a 4-tier commission fee structure', 'service'),
		(2, 'default', 'This product final value group will hold a 4-tier commission fee structure', 'product')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default final groups . .</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "increments_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "increments_groups (
		`groupid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`description` MEDIUMTEXT,
		PRIMARY KEY  (`groupid`),
		INDEX ( `groupname` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "increments_groups</li>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "increments");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "increments (
		`incrementid` INT( 5 ) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(250) NOT NULL default 'default',
		`increment_from` FLOAT(10,2) NOT NULL default '0.00',
		`increment_to` FLOAT(10,2) NOT NULL default '0.00',
		`amount` FLOAT(10,2) NOT NULL default '0.00',
		`sort` INT(5) NOT NULL default '0',
		`cid` INT(10) NOT NULL default '0',
		PRIMARY KEY  (`incrementid`),
		INDEX ( `groupname` ),
		INDEX ( `cid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "increments</li>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "insertion_fees");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "insertion_fees (
		`insertionid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`insertion_from` FLOAT(10,2) NOT NULL default '0.00',
		`insertion_to` FLOAT(10,2) NOT NULL default '0.00',
		`amount` FLOAT(10,2) NOT NULL default '0.00',
		`sort` INT(5) NOT NULL default '0',
		`state` ENUM('service','product') NOT NULL default 'service',
		PRIMARY KEY  (`insertionid`),
		INDEX ( `groupname` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "insertion_fees</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "insertion_fees
		(`insertionid`, `groupname`, `insertion_from`, `insertion_to`, `amount`, `sort`, `state`)
		VALUES
		(1, 'default', '0', '0', '8.00', 10, 'service'),
		(3, 'default', '0.01', '0.99', '0.20', 10, 'product'),
		(4, 'default', '1.00', '9.99', '0.35', 20, 'product'),
		(5, 'default', '10.00', '24.99', '0.60', 30, 'product'),
		(6, 'default', '25.00', '49.99', '1.20', 40, 'product'),
		(7, 'default', '50.00', '199.00', '2.40', 50, 'product'),
		(8, 'default', '200.00', '499.99', '3.60', 60, 'product'),
		(9, 'default', '500.00', '-1', '4.80', 70, 'product')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default insertion fees . .</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "insertion_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "insertion_groups (
		`groupid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`description` MEDIUMTEXT,
		`state` ENUM('service','product') NOT NULL default 'service',
		PRIMARY KEY  (`groupid`),
		INDEX ( `groupname` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "insertion_groups</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "insertion_groups
		(`groupid`, `groupname`, `description`, `state`)
		VALUES
		(1, 'default', 'Default fixed insertion fees', 'service'),
		(2, 'default', 'Default product insertion fees', 'product')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default insertion groups . .</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "invoicelog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "invoicelog (
		`invoicelogid` INT(200) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`invoiceid` INT(10) NOT NULL default '0',
		`invoicetype` ENUM('storesubscription','subscription','commission','p2b','buynow','credential','debit','credit','escrow') NOT NULL default 'debit',
		`date_sent` DATE NOT NULL default '0000-00-00',
		`date_remind` DATE NOT NULL default '0000-00-00',
		PRIMARY KEY  (`invoicelogid`),
		INDEX ( `user_id` ),
		INDEX ( `invoiceid` ),
		INDEX ( `invoicetype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "invoicelog</li>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "invoices");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "invoices (
		`invoiceid` INT(100) NOT NULL AUTO_INCREMENT,
		`parentid` INT(10) NOT NULL default '0',
		`currency_id` INT(5) NOT NULL default '0',
		`currency_rate` VARCHAR(10) NOT NULL default '0',
		`subscriptionid` INT(10) NOT NULL default '0',
		`projectid` INT(10) NOT NULL default '0',
		`buynowid` INT(10) NOT NULL default '0',
		`user_id` INT(100) NOT NULL default '0',
		`p2b_user_id` INT(10) NOT NULL default '0',
		`p2b_paymethod` MEDIUMTEXT,
		`p2b_markedaspaid` INT(1) NOT NULL default '0',
		`storeid` INT(10) NOT NULL default '0',
		`orderid` INT(10) NOT NULL default '0',
		`description` MEDIUMTEXT,
		`amount` FLOAT(10,2) NOT NULL default '0.00',
		`paid` FLOAT(10,2) default '0.00',
		`totalamount` FLOAT(10,2) NOT NULL default '0.00',
		`istaxable` INT(1) NOT NULL default '0',
		`taxamount` FLOAT(10,2) NOT NULL default '0.00',
		`taxinfo` MEDIUMTEXT,
		`status` ENUM('paid','unpaid','scheduled','complete','cancelled') NOT NULL default 'unpaid',
		`invoicetype` ENUM('store','storesubscription','subscription','commission','p2b','buynow','credential','debit','credit','escrow','refund') NOT NULL default 'subscription',
		`paymethod` ENUM('account','bank','visa','amex','mc','disc','paypal','check','purchaseorder','stormpay','cashu','moneybookers','external') NOT NULL default 'account',
		`paymentgateway` VARCHAR(200) NOT NULL default '',
		`ipaddress` VARCHAR(15) NOT NULL default '0.0.0.0',
		`referer` VARCHAR(255) NOT NULL default '',
		`createdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`duedate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`paiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`custommessage` MEDIUMTEXT,
		`transactionid` VARCHAR(25) NOT NULL default '0',
		`archive` TINYINT(4) NOT NULL default '0',
		`ispurchaseorder` INT(1) NOT NULL default '0',
		`isdeposit` INT(1) NOT NULL default '0',
		`depositcreditamount` FLOAT(10,2) NOT NULL default '0.00',
		`iswithdraw` INT(1) NOT NULL default '0',
		`withdrawinvoiceid` INT(5) NOT NULL default '0',
		`withdrawdebitamount` FLOAT(10,2) NOT NULL default '0.00',
		`isfvf` INT(1) NOT NULL default '0',
		`isif` INT(1) NOT NULL default '0',
		`isportfoliofee` INT(1) NOT NULL default '0',
		`isenhancementfee` INT(1) NOT NULL default '0',
		`isescrowfee` INT(1) NOT NULL default '0',
		`iswithdrawfee` INT(1) NOT NULL default '0',
		`isp2bfee` INT(1) NOT NULL default '0',
		`isdonationfee` INT(1) NOT NULL default '0',
		`ischaritypaid` INT(1) NOT NULL default '0',
		`charityid` INT(5) NOT NULL default '0',
		`isregisterbonus` INT(1) NOT NULL default '0',
		`indispute` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`invoiceid`),
		INDEX ( `parentid` ),
		INDEX ( `currency_id` ),
		INDEX ( `subscriptionid` ),
		INDEX ( `projectid` ),
		INDEX ( `buynowid` ),
		INDEX ( `user_id` ),
		INDEX ( `p2b_user_id` ),
		INDEX ( `orderid` ),
		INDEX ( `status` ),
		INDEX ( `invoicetype` ),
		INDEX ( `paymethod` ),
		INDEX ( `transactionid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "invoices</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "language");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "language (
		`languageid` INT(100) NOT NULL AUTO_INCREMENT,
		`title` VARCHAR(30) NOT NULL default '',
		`languagecode` VARCHAR(10) NOT NULL default '',
		`charset` VARCHAR(100) NOT NULL default '',
		`locale` VARCHAR(20) NOT NULL default 'en_US',
		`author` VARCHAR(100) NOT NULL default 'ilance',
		`textdirection` VARCHAR(3) NOT NULL default 'ltr',
		`languageiso` VARCHAR(10) NOT NULL default 'en',
		`canselect` INT(1) NOT NULL default '1',
		`installdate` DATETIME NOT NULL,
		`replacements` MEDIUMTEXT,
		PRIMARY KEY languageid (`languageid`),
		INDEX ( `title` ),
		INDEX ( `languagecode` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "language</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "language
		(languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements)
		VALUES(
		NULL,
		'English (US)',
		'english',
		'UTF-8',
		'en_US',
		'ilance',
		'ltr',
		'en',
		'1',
		NOW(),
		'')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default language . .</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "language_phrasegroups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "language_phrasegroups (
		`groupname` VARCHAR(100) NOT NULL default '',
		`description` MEDIUMTEXT,
		`product` VARCHAR(250) NOT NULL default 'ilance',
		KEY groupname (`groupname`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "language_phrasegroups</li>";
	flush();
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('accounting', 'Accounting Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('main', 'Global Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('search', 'Search Engine Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('preferences', 'Preferences Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('registration', 'Registration Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('subscription', 'Subscription Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('ipn', 'Payment Handler Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('buying', 'Buying Activities Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('feedback', 'Feedback Activities Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('rfp', 'RFP Activities Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('watchlist', 'Watchlist Activities Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('selling', 'Selling Activity Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('pmb', 'PMB Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('cron', 'Cron Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('portfolio', 'Portfolio Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('administration', 'Administration Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('livebid', 'LiveBid Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('mediashare', 'MediaShare Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('javascript', 'Javascript Phrases', 'ilance')");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default phrase groups . .</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "language_phrases");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "language_phrases (
		`phraseid` INT(100) NOT NULL AUTO_INCREMENT,
		`phrasegroup` MEDIUMTEXT,
		`varname` VARCHAR(250) NOT NULL default '',
		`text_original` MEDIUMTEXT,
		`text_eng` MEDIUMTEXT,
		`baselanguageid` INT(2) NOT NULL default '1',
		`isupdated` INT(1) NOT NULL default '0',
		`ismoved` INT(1) NOT NULL default '0',
		`ismaster` INT(1) NOT NULL default '0',
		PRIMARY KEY (`phraseid`),
		UNIQUE KEY varname (`varname`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "language_phrases</li>";
	
	// create xx_locations_states and import default states
	$dbengine = MYSQL_ENGINE;
	$dbtype = MYSQL_TYPE;
	
	include(DIR_SERVER_ROOT . 'install/functions/locations_schema.php');
	create_locations_schema($dbengine, $dbtype);
	
	// create xx_locations_cities and import default cities
	include(DIR_SERVER_ROOT . 'install/functions/locations_cities_canada.php');
	create_city_locations_schema($dbengine, $dbtype);
	
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "messages");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "messages (
		`messageid` INT(100) NOT NULL AUTO_INCREMENT,
		`project_id` INT(100) NOT NULL default '0',
		`user_id` INT(100) NOT NULL default '0',
		`username` VARCHAR(200) NOT NULL default '',
		`message` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`messageid`),
		INDEX ( `project_id` ),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "messages</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "modules");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "modules (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`modulegroup` VARCHAR(250) NOT NULL default '',
		`parentkey` VARCHAR(100) NOT NULL default '',
		`tab` VARCHAR(250) NOT NULL default '',
		`template` MEDIUMTEXT,
		`subcmd` VARCHAR(250) NOT NULL default '',
		`parentid` INT(2) NOT NULL default '0',
		`sort` INT(2) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		INDEX ( `modulegroup` ),
		INDEX ( `parentkey` ),
		INDEX ( `tab` ),
		INDEX ( `subcmd` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "modules</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "modules_group");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "modules_group (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`modulegroup` VARCHAR(250) NOT NULL default '',
		`modulename` VARCHAR(250) NOT NULL default '',
		`folder` VARCHAR(250) NOT NULL default '',
		`configtable` VARCHAR(50) NOT NULL default '',
		`installcode` MEDIUMTEXT,
		`uninstallcode` MEDIUMTEXT,
		`version` VARCHAR(10) NOT NULL default '1.0.0',
		`versioncheckurl` VARCHAR(250) NOT NULL default '',
		`url` VARCHAR(250) NOT NULL default '',
		`developer` VARCHAR(250) NOT NULL default 'ILance',
		`filestructure` MEDIUMTEXT,
		`installdate` DATETIME NOT NULL,
		`upgradedate` DATETIME NOT NULL,
		PRIMARY KEY  (`id`),
		INDEX ( `modulegroup` ),
		INDEX ( `modulename` ),
		INDEX ( `folder` ),
		INDEX ( `configtable` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "modules_groups</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "motd");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "motd (
		`motdid` INT(5) NOT NULL AUTO_INCREMENT,
		`content` MEDIUMTEXT,
		`date` DATE NOT NULL,
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY (`motdid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "admincp_news</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "payment_configuration");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "payment_configuration (
		`id` INT(5) NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(250) NOT NULL default '',
		`description` MEDIUMTEXT,
		`value` MEDIUMTEXT,
		`configgroup` VARCHAR(250) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pass','pulldown') NOT NULL default 'yesno',
		`inputcode` MEDIUMTEXT,
		`inputname` VARCHAR(250) NOT NULL default '',
		`help` MEDIUMTEXT,
		`sort` INT(5) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`id`),
		INDEX ( `name` ),
		INDEX ( `configgroup` ),
		INDEX ( `inputtype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "payment_configuration</li>";
    
	// WIRE SETTINGS GROUP
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Wire Transfer', 'wiretransfer', 'text', '', '', '', 10, 1)");
	
	// DEFAULT GATEWAY SETTINGS GROUP
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'use_internal_gateway', 'Please select your credit card payment gateway [or disable]', 'none', 'defaultgateway', 'pulldown', '<select name=\"config[use_internal_gateway]\" style=\"font-family: verdana\"><option value=\"authnet\">Authorize.Net</option><option value=\"bluepay\">BluePay</option><option value=\"plug_n_pay\">PlugNPay</option><option value=\"psigate\">PSIGate</option><option value=\"eway\">eWAY</option><option value=\"none\" selected=\"selected\">Disable Credit Card Support</option></select>', 'defaultgateway', 'This setting ultimately informs the marketplace that you are allowing users to fund their online account balance using a credit card based on the selected merchant gateway.  If you would like to disable credit card support select disable credit card support from the pulldown menu.', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'save_credit_cards', 'Would you like to save credit cards in the database?', '0', 'defaultgateway', 'yesno', '', '', 'Depending on the environment which you plan on doing business this option ultimately decides if a member can add a credit card from the accounting menu for later use without having to re-type the credit card information each time a deposit is made.  Note: it may be prohibited to save credit cards in your database for your local country, state, province or region.  Please use this setting with caution.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'creditcard_authentication', 'Enable card holder debit process (2 amounts both < 2.00)?', '1', 'defaultgateway', 'yesno', '', '', 'This setting forces users that have added a credit card to their account to complete a debit process where two transactions for two amounts both under two dollars is debited from the card.  The user then has x attempts to verify the amounts to validate the newly added credit card.', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'max_cc_verify_attempts', 'Maximum input attempts a member has during the authencity of the verify process?', '5', 'defaultgateway', 'int', '', '', 'This setting lets you decide how many input attempts the person adding the credit card has to actually get the two amounts debited correct.', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'admin_cc_auth_expired_days', 'Maximum days a card will remain in the DB [with unfinished authentication]?', '60', 'defaultgateway', 'int', '', '', 'This setting defines the number of days until a credit card that has started but has not completed the card holder debit process authorization will be removed from the database.', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'admin_cc_expired_days', 'Maximum days a card will remain in the DB [without attempting CC authentication process]?', '120', 'defaultgateway', 'int', '', '', 'This setting defines the number of days until a credit card that was added but never started the card holder debit process authorization will be removed from the database.', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cron_refund_on_max_cc_auth_days', 'Auto-refund (to card) auth amounts [if max days for authentication exceed]?', '0', 'defaultgateway', 'yesno', '', '', 'This setting will physically refund the two authentication amounts used for the card holder debit process (if enabled) back to the credit card holder when the max days of unfinished authentcation has been met.  This feature is executed from the automation system via cron.creditcards.php.', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund_on_max_cc_attempts', 'Auto-refund (to card) auth amounts [if max attempts fail then fund online account balance]?', '0', 'defaultgateway', 'yesno', '', '', 'This setting will physically refund the two authentication amounts used for the card holder debit process (if enabled) back to the credit card holder when the user attempting to get the two debit amounts correct fails the max input attempts.  When disabled, the two authentication amounts are credited to the users account balance.', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'advanced_email_filter', 'Enable email filter security [checks email MX domain record]?', '1', 'defaultgateway', 'yesno', '', '', 'When this feature is enabled, a more in depth email address security check occurs when a new credit card is added.  This option physically connects to the mail server of the email address entered to double check for authenticity. Beware: many new ccTLDs are created monthly (ie: .mobi, .tel, .me) and the function powering this feature may not support all ccTLDs and may return a false positive.  Consider beta.', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'multi_creditcard_support', 'Enable multi-credit card support [customers can add more than one credit card]?', '0', 'defaultgateway', 'yesno', '', '', 'This setting ultimately allows you to let members add more than one credit card profile to their account.  Each card added will be a new payment method option within the pulldown menu from the deposit funds menu.', 100, 1)");
	
	// AUTHORIZE.NET
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Authorize.Net', 'authnet', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Authorize.Net username', 'testing', 'authnet', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_password', 'Authorize.Net password', 'testing', 'authnet', 'pass', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_key', 'Authorize.Net transaction key', '', 'authnet', 'pass', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'Authorize.Net transaction usage fee 1 [value in percentage; i.e: 0.029]', '0.029', 'authnet', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'Authorize.Net transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'authnet', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'Authorize.Net credit card authentication process capture mode [auth|charge|capture]?', 'charge', 'authnet', 'text', '', '', '', 70, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'Authorize.Net credit card authentication process refund mode [process|void|credit]?', 'credit', 'authnet', 'text', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'authnet', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authnet_enabled', 'Enable Authorize.Net gateway module?', '1', 'authnet', 'yesno', '', '', '', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authnet_subscriptions', 'Enable Authorize.Net Recurring Subscriptions? (used in subscription menu)', '0', 'authnet', 'yesno', '', '', '', 110, 1)");
	
	// BLUEPAY
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'BluePay', 'bluepay', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'BluePay username', 'testing', 'bluepay', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_password', 'BluePay password', 'testing', 'bluepay', 'pass', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_key', 'BluePay transaction key', '', 'bluepay', 'pass', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'BluePay transaction usage fee 1 [value in percentage; i.e: 0.029]', '0.029', 'bluepay', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'BluePay transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'bluepay', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'BluePay credit card authentication process capture mode [auth|charge|capture]?', 'charge', 'bluepay', 'text', '', '', '', 70, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'BluePay credit card authentication process refund mode [process|void|credit]?', 'credit', 'bluepay', 'text', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'bluepay', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_enabled', 'Enable BluePay gateway module?', '1', 'bluepay', 'yesno', '', '', '', 100, 1)");
	
	// PLUGNPAY
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'PlugNPay', 'plug_n_pay', 'text', '', '', '', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Enter your PlugNPay username', 'pnpdemo', 'plug_n_pay', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_password', 'Enter your PlugNPay password', 'pnpdemo', 'plug_n_pay', 'pass', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_key', 'PlugNPay transaction key [supplied by plugnpay.com]', '', 'plug_n_pay', 'pass', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'PlugNPay transaction usage fee 1 [value in percentage; i.e: 0.029]', '0.029', 'plug_n_pay', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'PlugNPay transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'plug_n_pay', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'PlugNPay credit card authentication process capture mode [auth|charge]?', 'charge', 'plug_n_pay', 'text', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'PlugNPay credit card authentication process refund mode [process|void|credit]?', 'credit', 'plug_n_pay', 'text', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'plug_n_pay', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'plug_n_pay_enabled', 'Enable PlugNPay Gateway module?', '1', 'plug_n_pay', 'yesno', '', '', '', 100, 1)");
	
	// PSIGATE
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'PSIGate', 'psigate', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Enter your PSIGate StoreID', 'teststore', 'psigate', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_password', 'Enter your PSIGate passphrase', 'psigate1234', 'psigate', 'pass', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'PSIGate transaction usage fee [value in percentage; i.e: 0.029]', '0.029', 'psigate', 'int', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'PSIGate transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'psigate', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'PSIGate credit card authentication process capture mode [charge]?', 'charge', 'psigate', 'text', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'PSIGate credit card authentication process refund mode [credit]?', 'credit', 'psigate', 'text', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'psigate', 'yesno', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'psigate_enabled', 'Enable PSIGate gateway module?', '0', 'psigate', 'yesno', '', '', '', 90, 1)");
	
	// EWAY
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'eWAY', 'eway', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Enter your eWAY ClientID', '87654321', 'eway', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'eWAY transaction usage fee [value in percentage; i.e: 0.029]', '0.029', 'eway', 'int', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'eWAY transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'eway', 'int', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'eWAY credit card authentication process capture mode [charge]?', 'charge', 'eway', 'text', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'eWAY credit card authentication process refund mode [credit]?', 'credit', 'eway', 'text', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'eway', 'yesno', '', '', '', 70, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'eway_enabled', 'Enable eWAY gateway module?', '0', 'eway', 'yesno', '', '', '', 80, 1)");
	
	// PAYPAL
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Paypal', 'paypal', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_business_email', 'Enter your Paypal email address', 'payments@yourdomain.com', 'paypal', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_master_currency', 'Enter the currency used in Paypal transactions', 'USD', 'paypal', 'int', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'paypal', 'yesno', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'paypal', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'paypal', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'paypal', 'int', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_deposit_echeck_active', 'Enable e-check deposit support [will show e-check support in payment pulldown]?', '1', 'paypal', 'yesno', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_deposit_echeck_fee', 'Enter deposit via e-check usage fee amount [value is fixed dollar]', '5.00', 'paypal', 'int', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_withdraw_active', 'Allow members to request withdrawals using this gateway?', '1', 'paypal', 'yesno', '', '', '', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_active', 'Allow members to deposit funds using this gateway?', '1', 'paypal', 'yesno', '', '', '', 110, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_subscriptions', 'Enable Paypal Recurring Subscriptions? (used in subscription menu)', '0', 'paypal', 'yesno', '', '', '', 120, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'paypal', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses PayPal as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 130, 1)");
	
	// STORMPAY
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Stormpay', 'stormpay', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_business_email', 'Enter your Stormpay email address', 'payments@yourdomain.com', 'stormpay', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_secret_code', 'Enter the secret passphrase code [must be set at stormpay.com]', 'mypassphrase', 'stormpay', 'text', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_md5_digest', 'Enable MD5 hash encryption feature? [must be set at stormpay.com]', '1', 'stormpay', 'yesno', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_master_currency', 'Enter the currency used in Stormpay transactions', 'USD', 'stormpay', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'stormpay', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'stormpay', 'int', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_active', 'Allow members to deposit funds using this gateway?', '1', 'stormpay', 'yesno', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_subscriptions', 'Enable Stormpay Recurring Subscriptions? (used in subscription menu)', '0', 'stormpay', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'stormpay', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses StormPay as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 100, 1)");
	
	// CASHU
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'CashU', 'cashu', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_business_email', 'Enter your CashU Merchant ID', 'payments@yourdomain.com', 'cashu', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_secret_code', 'Enter the secret passphrase code [must be set at cashu.com]', 'mypassphrase', 'cashu', 'text', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_master_currency', 'Enter the currency used in CashU transactions', 'USD', 'cashu', 'int', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'cashu', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'cashu', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_active', 'Allow members to deposit funds using this gateway?', '1', 'cashu', 'yesno', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_testmode', 'Put this payment module in test mode only?', '0', 'cashu', 'yesno', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'cashu', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses CashU as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 90, 1)");
	
	// MONEYBOOKERS
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'MoneyBookers', 'moneybookers', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_business_email', 'Enter your MoneyBookers email address', 'payments@yourdomain.com', 'moneybookers', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_secret_code', 'Enter the secret passphrase code [must be set at moneybookers.com]', 'mypassphrase', 'moneybookers', 'text', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_md5_digest', 'Enable MD5 hash encryption feature? [must be set at moneybookers.com]', '1', 'moneybookers', 'yesno', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_master_currency', 'Enter the currency used in MoneyBookers transactions', 'USD', 'moneybookers', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'moneybookers', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'moneybookers', 'int', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_active', 'Allow members to deposit funds using this gateway?', '1', 'moneybookers', 'yesno', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_subscriptions', 'Enable MoneyBookers Recurring Subscriptions? (used in subscription menu)', '0', 'moneybookers', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'moneybookers', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses MoneyBookers as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 100, 1)");
	
	// CHEQUE
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'checkpayout_support', 'Enable check or money order requests [via withdraw funds menu]?', '1', 'check', 'yesno', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'check_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'check', 'yesno', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'check_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'check', 'int', '', '', '', 30, 1)");
	
	// BANK
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'enable_bank_deposit_support', 'Enable the ability for customers to add a new bank deposit account [for withdraw payment requests]?', '1', 'bank', 'yesno', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'multi_bankaccount_support', 'Enable multi-bank deposit account support [customers can add more than one deposit account]?', '1', 'bank', 'yesno', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bank_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'bank', 'yesno', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bank_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'bank', 'int', '', '', '', 40, 1)");
	
	// KEYS
	$key1 = createkey(50);
	$key2 = createkey(50);
	$key3 = createkey(50);
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'key1', 'Enter encryption layer 1 key', '".$ilance->db->escape_string($key1)."', 'keys', 'textarea', '', '', 'Once this value has been set it should never be changed.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'key2', 'Enter encryption layer 2 key', '".$ilance->db->escape_string($key2)."', 'keys', 'textarea', '', '', 'Once this value has been set it should never be changed.', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'key3', 'Enter encryption layer 3 key', '".$ilance->db->escape_string($key3)."', 'keys', 'textarea', '', '', 'Once this value has been set it should never be changed.', 30, 1)");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing payment configuration settings . .</strong></li></ul>";
	
	unset($key1, $key2, $key3);
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "payment_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "payment_groups (
		`parentgroupname` VARCHAR(250) NOT NULL default '',
		`groupname` VARCHAR(250) NOT NULL default '',
		`description` MEDIUMTEXT,
		`help` MEDIUMTEXT,
		`moduletype` VARCHAR(250) NOT NULL default '',
		PRIMARY KEY  (`groupname`),
		INDEX ( `moduletype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "payment_groups</li>";
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('defaultgateway', 'defaultgateway', 'Credit Card Gateway Settings and Configuration', '', '')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('paypal', 'paypal', 'Paypal IPN Gateway Configuration', '', 'ipn')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('stormpay', 'stormpay', 'StormPay IPN Gateway Configuration', '', 'ipn')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('cashu', 'cashu', 'CashU IPN Gateway Configuration', '', 'ipn')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('moneybookers', 'moneybookers', 'MoneyBookers IPN Gateway Configuration', '', 'ipn')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('authnet', 'authnet', 'Authorize.Net Gateway Configuration', '', 'gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('bluepay', 'bluepay', 'BluePay Gateway Configuration', '', 'gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('check', 'check', 'Check / Money Order Payment Configuration', '', 'local')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('bank', 'bank', 'Bank Payment Configuration', '', 'local')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('keys', 'keys', 'Global Encryption Key Configuration and Settings', '', '')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('plug_n_pay', 'plug_n_pay', 'PlugNPay Gateway Configuration', '', 'gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('psigate', 'psigate', 'PSIGate Gateway Configuration', '', 'gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('eway', 'eway', 'eWAY Gateway Configuration', '', 'gateway')");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing payment configuration groups . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "payment_methods");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "payment_methods (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`title` MEDIUMTEXT,
		`sort` INT(5) NOT NULL,
		PRIMARY KEY  (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "payment_methods</li>";
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_see_description_for_my_accepted_payment_methods', 10)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_master_card', 20)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_visa', 30)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_money_order', 40)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_personal_check', 50)");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported payment methods . .</strong></li></ul>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "pmb");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "pmb (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`project_id` INT(100) NOT NULL default '0',
		`event_id` INT(100) NOT NULL default '0',
		`datetime` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`message` MEDIUMTEXT,
		`subject` VARCHAR(200) NOT NULL default 'No Subject',
		PRIMARY KEY  (`id`),
		INDEX ( `project_id` ),
		INDEX ( `event_id` ),
		INDEX ( `subject` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "pmb</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "pmb_alerts");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "pmb_alerts (
		`id` INT(100) NOT NULL default '0',
		`event_id` INT(100) NOT NULL default '0',
		`project_id` INT(100) NOT NULL default '0',
		`from_id` INT(100) NOT NULL default '0',
		`to_id` INT(100) NOT NULL default '0',
		`isadmin` INT(1) NOT NULL default '0',
		`from_status` ENUM('new','active','archived','deleted') NOT NULL default 'new',
		`to_status` ENUM('new','active','archived','deleted') NOT NULL default 'new',
		`track_status` ENUM('unread','read') NOT NULL default 'unread',
		`track_dateread` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`track_popup` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		INDEX ( `event_id` ),
		INDEX ( `project_id` ),
		INDEX ( `from_id` ),
		INDEX ( `to_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "pmb_alerts</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "portfolio");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "portfolio (
		`portfolio_id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`caption` VARCHAR(75) NOT NULL default '',
		`description` VARCHAR(100) NOT NULL default '0',
		`category_id` INT(10) NOT NULL default '0',
		`featured` INT(1) NOT NULL default '0',
		`featured_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`featured_invoiceid` INT(5) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`portfolio_id`),
		INDEX (`user_id`),
		INDEX (`category_id`),
		INDEX (`description`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "portfolio</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "product_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "product_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "product_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "product_questions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "product_questions (
		`questionid` INT(10) NOT NULL AUTO_INCREMENT,
		`cid` INT(10) NOT NULL default '0',
		`question_eng` MEDIUMTEXT,
		`description_eng` MEDIUMTEXT,
		`formname` VARCHAR(100) NOT NULL default '',
		`formdefault` VARCHAR(100) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','range','url') NOT NULL default 'text',
		`multiplechoice` MEDIUMTEXT,
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`cansearch` INT(1) NOT NULL default '0',
		`canremove` INT(1) NOT NULL default '1',
		`recursive` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`questionid`),
		INDEX ( `cid` ),
		INDEX ( `formname` ),
		INDEX ( `formdefault` ),
		INDEX ( `inputtype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "product_questions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`user_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '0',
		`isverified` INT(1) NOT NULL default '0',
		`verifyexpiry` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`invoiceid` INT(10) NOT NULL default '0',
		`contactname` VARCHAR(250) NOT NULL default '',
		`contactnumber` VARCHAR(50) NOT NULL default '',
		`contactnotes` MEDIUMTEXT,
		PRIMARY KEY  (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `user_id` ),
		INDEX ( `invoiceid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_answers</li>";

	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_categories");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_categories (
		`user_id` INT(10) NOT NULL default '0',
		`cid` INT(10) NOT NULL default '0',
		KEY `user_id` (`user_id`),
		INDEX (`cid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_categories</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_filter_auction_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_filter_auction_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`user_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`filtertype` ENUM( 'range', 'checkbox', 'pulldown' ) NOT NULL default 'range',
		`date` DATETIME NOT NULL,
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY `answerid` (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `project_id` ),
		INDEX ( `user_id` ),
		INDEX ( `filtertype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_filter_auction_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_groups (
		`groupid` INT(10) NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(250) NOT NULL default '',
		`description` VARCHAR(250) NOT NULL default '',
		`visible` INT(1) NOT NULL default '1',
		`canremove` INT(1) NOT NULL default '1',
		`cid` INT(5) NOT NULL default '0',
		PRIMARY KEY  (`groupid`),
		INDEX ( `name` ),
		INDEX ( `description` ),
		INDEX ( `cid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_groups</li>";
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_groups VALUES (1, 'Education', 'Educational Background', 1, 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_groups VALUES (2, 'Availability', 'Availability', 1, 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_groups VALUES (3, 'Company', 'Company', 1, 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_groups VALUES (4, 'All Profile Categories', 'General', 1, 0, -1)");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default profile groups and sample questions . .</strong></li></ul>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_questions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_questions (
		`questionid` INT(10) NOT NULL AUTO_INCREMENT,
		`groupid` INT(10) NOT NULL default '0',
		`question` MEDIUMTEXT,
		`description` MEDIUMTEXT,
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','range') NOT NULL default 'text',
		`multiplechoice` MEDIUMTEXT,
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`canverify` INT(1) NOT NULL default '1',
		`canremove` INT(1) NOT NULL default '1',
		`verifycost` FLOAT(10,2) NOT NULL default '0.00',
		`isfilter` INT(1) NOT NULL default '0',
		`filtertype` ENUM('pulldown','multiplechoice','range') NOT NULL default 'pulldown',
		`filtercategory` INT(10) NOT NULL default '0',
		PRIMARY KEY  (`questionid`),
		INDEX ( `groupid` ),
		INDEX ( `inputtype` ),
		INDEX ( `filtercategory` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_questions</li>";
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (1, 1, 'Summary Of Expertise', 'Self-summary of expertise', 'textarea', '', 4, 1, 0, 1, 1, '5.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (2, 1, 'Certifications', 'Certifications received within the past 5 years', 'textarea', '', 2, 1, 0, 1, 1, '10.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (3, 1, 'Licenses', 'Licenses or Awards received within the past 5 years', 'textarea', '', 3, 1, 0, 1, 1, '15.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (4, 1, 'Education', 'Educational Background', 'textarea', '', 1, 1, 0, 1, 1, '20.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (5, 2, 'Willing to work on-site', 'Willing to work on-site in your local area?', 'yesno', '', 3, 1, 0, 0, 1, '0.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (6, 2, 'Payment Terms', 'Payment terms', 'textarea', '', 2, 1, 0, 0, 1, '0.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (7, 3, 'Years In Business', 'Total years in business', 'int', '', 2, 1, 0, 1, 1, '10.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (8, 3, 'Number Of Employees', 'Number of employees within company', 'int', '', 1, 1, 0, 1, 1, '5.00', 0, 'pulldown', '0')");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default profile questions . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`project_id` INT(15) NOT NULL default '0',
		`escrow_id` INT(10) NOT NULL default '0',
		`cid` INT(10) NOT NULL default '0',
		`description` MEDIUMTEXT,
		`description_videourl` MEDIUMTEXT,
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_starts` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_end` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`user_id` INT(100) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '0',
		`views` INT(10) NOT NULL default '0',
		`project_title` VARCHAR(250) NOT NULL default '',
		`bids` INT(10) NOT NULL default '0',
		`bidsdeclined` INT(10) NOT NULL default '0',
		`bidsretracted` INT(10) NOT NULL default '0',
		`bidsshortlisted` INT(10) NOT NULL default '0',
		`budgetgroup` VARCHAR(30) NOT NULL default '',
		`additional_info` MEDIUMTEXT,
		`status` ENUM('draft','open','closed','expired','delisted','wait_approval','approval_accepted','frozen','finished','archived') NOT NULL default 'draft',
		`close_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`transfertype` ENUM('userid','email') NOT NULL default 'userid',
		`transfer_to_userid` INT(10) NOT NULL default '0',
		`transfer_from_userid` INT(10) NOT NULL default '0',
		`transfer_to_email` VARCHAR(50) NOT NULL default '',
		`transfer_status` ENUM('','pending','accepted','rejected') NOT NULL default '',
		`transfer_code` VARCHAR(32) NOT NULL default '',
		`project_details` ENUM('public','invite_only','realtime','unique','penny') NOT NULL default 'public',
		`project_type` ENUM('reverse','forward') NOT NULL default 'reverse',
		`project_state` ENUM('service','product') NOT NULL default 'service',
		`bid_details` ENUM('open','sealed','blind','full') NOT NULL default 'open',
		`filter_rating` ENUM('0','1') NOT NULL default '0',
		`filter_country` ENUM('0','1') NOT NULL default '0',
		`filter_state` ENUM('0','1') NOT NULL default '0',
		`filter_city` ENUM('0','1') NOT NULL default '0',
		`filter_zip` ENUM('0','1') NOT NULL default '0',
		`filter_underage` ENUM('0','1') NOT NULL default '0',
		`filter_businessnumber` ENUM('0','1') NOT NULL default '0',
		`filter_bidtype` ENUM('0','1') NOT NULL default '0',
		`filter_budget` ENUM('0','1') NOT NULL default '0',
		`filter_escrow` INT(1) NOT NULL default '0',
		`filter_gateway` INT(1) NOT NULL default '0',
		`filter_offline` INT(1) NOT NULL default '0',
		`filter_publicboard` ENUM('0','1') NOT NULL default '0',
		`filtered_rating` ENUM('1','2','3','4','5') NOT NULL default '1',
		`filtered_country` VARCHAR(50) NOT NULL default '',
		`filtered_state` VARCHAR(50) NOT NULL default '',
		`filtered_city` VARCHAR(20) NOT NULL default '',
		`filtered_zip` VARCHAR(10) NOT NULL default '',
		`filtered_bidtype` ENUM('entire','hourly','daily','weekly','monthly','lot','weight','item') NOT NULL default 'entire',
		`filtered_bidtypecustom` VARCHAR(250) NOT NULL default '',
		`filtered_budgetid` INT(5) NOT NULL default '0',
		`filtered_auctiontype` ENUM('regular','fixed') NOT NULL default 'regular',
		`buynow` INT(1) NOT NULL default '0',
		`buynow_price` FLOAT(10,2) NOT NULL default '0.00',
		`buynow_qty` INT(10) NOT NULL default '0',
		`buynow_purchases` INT(10) NOT NULL default '0',
		`reserve` INT(1) NOT NULL default '0',
		`reserve_price` FLOAT(10,2) NOT NULL default '0.00',
		`featured` INT(1) NOT NULL default '0',
		`featured_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`highlite` INT(1) NOT NULL default '0',
		`bold` INT(1) NOT NULL default '0',
		`autorelist` INT(1) NOT NULL default '0',
		`autorelist_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`startprice` FLOAT(10,2) NOT NULL default '0.00',
		`retailprice` FLOAT(10,2) NOT NULL default '0.00',
		`uniquebidcount` INT(5) NOT NULL default '0',
		`paymethod` MEDIUMTEXT,
		`paymethodoptions` MEDIUMTEXT,
		`paymethodoptionsemail` MEDIUMTEXT,
		`keywords` VARCHAR(250) NOT NULL default '',
		`currentprice` FLOAT(10,2) NOT NULL default '0.00',
		`insertionfee` FLOAT(10,2) NOT NULL default '0.00',
		`fvf` FLOAT(10,2) NOT NULL default '0.00',
		`isfvfpaid` INT(1) NOT NULL default '0',
		`isifpaid` INT(1) NOT NULL default '0',
		`ifinvoiceid` INT(5) NOT NULL default '0',
		`fvfinvoiceid` INT(5) NOT NULL default '0',
		`returnaccepted` INT(1) NOT NULL default '0',
		`returnwithin` ENUM('0','3','7','14','30') NOT NULL default '0',
		`returngivenas` ENUM('none','exchange','credit','moneyback') NOT NULL default 'none',
		`returnshippaidby` ENUM('none','buyer','seller') NOT NULL default 'none',
		`returnpolicy` MEDIUMTEXT,
		`buyerfeedback` INT(1) NOT NULL default '0',
		`sellerfeedback` INT(1) NOT NULL default '0',
		`haswinner` INT(1) NOT NULL default '0',
		`hasbuynowwinner` INT(1) NOT NULL default '0',
		`winner_user_id` INT(5) NOT NULL default '0',
		`donation` INT(1) NOT NULL default '0',
		`charityid` INT(5) NOT NULL default '0',
		`donationpercentage` INT(5) NOT NULL default '0',
		`donermarkedaspaid` INT(1) NOT NULL default '0',
		`donermarkedaspaiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`donationinvoiceid` INT(5) NOT NULL default '0',
		`currencyid` INT(5) NOT NULL default '0',
		`countryid` INT(5) NOT NULL default '0',
		`country` VARCHAR(250) NOT NULL default '',
		`state` VARCHAR(250) NOT NULL default '',
		`city` VARCHAR(250) NOT NULL default '',
		`zipcode` VARCHAR(50) NOT NULL default '',
		`updateid` INT(5) NOT NULL default '1',
		PRIMARY KEY  (`id`),
		INDEX (`project_id`),
		INDEX (`cid`),
		INDEX (`project_title`),
		INDEX (`status`),
		INDEX (`project_details`),
		INDEX (`project_type`),
		INDEX (`project_state`),
		INDEX (`charityid`),
		INDEX (`countryid`),
		INDEX (`zipcode`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_changelog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_changelog (
		`id` INT(5) NOT NULL auto_increment,
		`project_id` INT(5) NOT NULL,
		`datetime` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`changelog` MEDIUMTEXT,
		PRIMARY KEY  (`id`),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_changelog</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_escrow");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_escrow (
		`escrow_id` INT(100) NOT NULL AUTO_INCREMENT,
		`bid_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`invoiceid` INT(100) NOT NULL default '0',
		`project_user_id` INT(100) NOT NULL default '0',
		`user_id` INT(100) NOT NULL default '0',
		`date_awarded` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_paid` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_released` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_cancelled` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`escrowamount` FLOAT(10,2) NOT NULL default '0.00',
		`bidamount` FLOAT(10,2) NOT NULL default '0.00',
		`shipping` FLOAT(10,2) NOT NULL default '0.00',
		`total` FLOAT(10,2) NOT NULL default '0.00',
		`fee` FLOAT(10,2) NOT NULL default '0.00',
		`fee2` FLOAT(10,2) NOT NULL default '0.00',
		`isfeepaid` INT(1) NOT NULL default '0',
		`isfee2paid` INT(1) NOT NULL default '0',
		`feeinvoiceid` INT(5) NOT NULL default '0',
		`fee2invoiceid` INT(5) NOT NULL default '0',
		`qty` INT(5) NOT NULL default '1',
		`buyerfeedback` INT(1) NOT NULL default '0',
		`sellerfeedback` INT(1) NOT NULL default '0',
		`status` ENUM('pending','started','confirmed','finished','cancelled') NOT NULL default 'pending',
		`sellermarkedasshipped` INT(1) NOT NULL default '0',
		`sellermarkedasshippeddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`escrow_id`),
		INDEX ( `bid_id` ),
		INDEX ( `project_id` ),
		INDEX ( `invoiceid` ),
		INDEX ( `project_user_id` ),
		INDEX ( `user_id` ),
		INDEX ( `status` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_escrow</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_shipping");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_shipping (
		`project_id` INT(5) NOT NULL,
		`ship_method` ENUM('flatrate', 'calculated', 'localpickup') NOT NULL default 'localpickup',
		`ship_handlingtime` ENUM('1','2','3','4','5','10','15','30') NOT NULL default '1',
		`ship_handlingfee` FLOAT(10,2) NOT NULL default '0.00',
		`ship_packagetype` VARCHAR(250) NOT NULL default '',
		`ship_length` INT(5) NOT NULL default '0',
		`ship_width` INT(5) NOT NULL default '0',
		`ship_height` INT(5) NOT NULL default '0',
		`ship_weightlbs` INT(5) NOT NULL default '1',
		`ship_weightoz` INT(5) NOT NULL default '0',
		INDEX ( `project_id` ) 
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_shipping</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_shipping_destinations");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_shipping_destinations (
		`destinationid` INT(100) NOT NULL AUTO_INCREMENT,
		`project_id` INT(5) NOT NULL,
		`ship_options_1` VARCHAR(250) NOT NULL default '',
		`ship_service_1` INT(5) NOT NULL default '0',
		`ship_fee_1` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_1` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_1` INT(1) NOT NULL default '0',
		`ship_options_2` VARCHAR(250) NOT NULL default '',
		`ship_service_2` INT(5) NOT NULL default '0',
		`ship_fee_2` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_2` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_2` INT(1) NOT NULL default '0',
		`ship_options_3` VARCHAR(250) NOT NULL default '',
		`ship_service_3` INT(5) NOT NULL default '0',
		`ship_fee_3` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_3` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_3` INT(1) NOT NULL default '0',
		`ship_options_4` VARCHAR(250) NOT NULL default '',
		`ship_service_4` INT(5) NOT NULL default '0',
		`ship_fee_4` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_4` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_4` INT(1) NOT NULL default '0',
		`ship_options_5` VARCHAR(250) NOT NULL default '',
		`ship_service_5` INT(5) NOT NULL default '0',
		`ship_fee_5` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_5` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_5` INT(1) NOT NULL default '0',
		`ship_options_6` VARCHAR(250) NOT NULL default '',
		`ship_service_6` INT(5) NOT NULL default '0',
		`ship_fee_6` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_6` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_6` INT(1) NOT NULL default '0',
		`ship_options_7` VARCHAR(250) NOT NULL default '',
		`ship_service_7` INT(5) NOT NULL default '0',
		`ship_fee_7` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_7` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_7` INT(1) NOT NULL default '0',
		`ship_options_8` VARCHAR(250) NOT NULL default '',
		`ship_service_8` INT(5) NOT NULL default '0',
		`ship_fee_8` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_8` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_8` INT(1) NOT NULL default '0',
		`ship_options_9` VARCHAR(250) NOT NULL default '',
		`ship_service_9` INT(5) NOT NULL default '0',
		`ship_fee_9` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_9` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_9` INT(1) NOT NULL default '0',
		`ship_options_10` VARCHAR(250) NOT NULL default '',
		`ship_service_10` INT(5) NOT NULL default '0',
		`ship_fee_10` FLOAT(10,2) NOT NULL default '0.00',
		`ship_additionalfee_10` FLOAT(10,2) NOT NULL default '0.00',
		`freeshipping_10` INT(1) NOT NULL default '0',
		INDEX ( `destinationid` ),
		INDEX ( `project_id` ) 
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_shipping_destinations</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_shipping_regions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_shipping_regions (
		`project_id` INT(5) NOT NULL,
		`country` VARCHAR(250) NOT NULL default '',
		`countryid` INT(5) NOT NULL default '0',
		`region` VARCHAR(250) NOT NULL default '',
		`row` VARCHAR(250) NOT NULL default '',
		INDEX ( `project_id` ) 
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_shipping_regions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_trackbacks");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_trackbacks (
		`trackbackid` INT(100) NOT NULL AUTO_INCREMENT,
		`project_id` INT(50) NOT NULL default '0',
		`ipaddress` MEDIUMTEXT,
		`url` MEDIUMTEXT,
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY (`trackbackid`),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_trackbacks</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_uniquebids");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_uniquebids (
		`uid` INT(100) NOT NULL AUTO_INCREMENT,
		`project_id` INT(10) NOT NULL default '0',
		`project_user_id` INT(100) NOT NULL default '0',
		`user_id` INT(100) NOT NULL default '0',
		`uniquebid` VARCHAR(10) NOT NULL default '0.00',
		`response` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`status` ENUM('nonunique','unique','lowestunique') NOT NULL default 'nonunique',
		`totalbids` INT(10) NOT NULL default '1',
		`winnermarkedaspaid` INT(1) NOT NULL default '0',
		`winnermarkedaspaiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`winnermarkedaspaidmethod` MEDIUMTEXT,
		`buyerpaymethod` VARCHAR(250) NOT NULL default '',
		`buyershipcost` FLOAT(10,2) NOT NULL default '0.00',
		`buyershipperid` INT(5) NOT NULL default '0',
		`sellermarkedasshipped` INT(1) NOT NULL default '0',
		`sellermarkedasshippeddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`uid`),
		INDEX ( `project_id` ),
		INDEX ( `project_user_id` ),
		INDEX ( `user_id` ),
		INDEX ( `status` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_uniquebids</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_bids");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_bids (
		`bid_id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`project_id` INT(100) NOT NULL default '0',
		`project_user_id` INT(100) NOT NULL default '0',
		`proposal` MEDIUMTEXT,
		`bidamount` FLOAT(10,2) NOT NULL default '0.00',
		`qty` INT(10) NOT NULL default '1',
		`estimate_days` INT(100) NOT NULL default '0',
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_updated` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_awarded` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`bidstatus` ENUM('placed','awarded','declined','choseanother','outbid') NOT NULL default 'placed',
		`bidstate` ENUM('','reviewing','wait_approval','shortlisted','invited','archived','expired','retracted') default '',
		`bidamounttype` ENUM('entire','hourly','daily','weekly','monthly','lot','item','weight') NOT NULL default 'entire',
		`bidcustom` VARCHAR(100) NOT NULL default '',
		`fvf` FLOAT(10,2) NOT NULL default '0.00',
		`state` ENUM('service','product') NOT NULL default 'service',
		`isproxybid` INT(1) NOT NULL default '0',
		`isshortlisted` INT(1) NOT NULL default '0',
		`winnermarkedaspaid` INT(1) NOT NULL default '0',
		`winnermarkedaspaiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`winnermarkedaspaidmethod` MEDIUMTEXT,
		`buyerpaymethod` VARCHAR(250) NOT NULL default '',
		`buyershipcost` FLOAT(10,2) NOT NULL default '0.00',
		`buyershipperid` INT(5) NOT NULL default '0',
		`sellermarkedasshipped` INT(1) NOT NULL default '0',
		`sellermarkedasshippeddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (`bid_id`),
		INDEX (`project_id`),
		INDEX ( `user_id` ),
		INDEX ( `project_user_id` ),
		INDEX ( `bidstatus` ),
		INDEX ( `bidstate` ),
		INDEX ( `bidamounttype` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_bids</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_realtimebids");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_realtimebids (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`bid_id` INT(100) NOT NULL default '0',
		`user_id` INT(100) NOT NULL default '0',
		`project_id` INT(100) NOT NULL default '0',
		`project_user_id` INT(100) NOT NULL default '0',
		`proposal` MEDIUMTEXT,
		`bidamount` FLOAT(10,2) NOT NULL default '0.00',
		`qty` INT(10) NOT NULL default '1',
		`estimate_days` INT(100) NOT NULL default '0',
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_updated` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_awarded` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`bidstatus` ENUM('placed','awarded','declined','choseanother','outbid') NOT NULL default 'placed',
		`bidstate` ENUM('','reviewing','wait_approval','shortlisted','invited','archived','expired','retracted') default '',
		`bidamounttype` ENUM('entire','hourly','daily','weekly','monthly','lot','item','weight') NOT NULL default 'entire',
		`bidcustom` VARCHAR(100) NOT NULL default '',
		`fvf` FLOAT(10,2) NOT NULL default '0.00',
		`state` ENUM('service','product') NOT NULL default 'service',
		`isproxybid` INT(1) NOT NULL default '0',
		`isshortlisted` INT(1) NOT NULL default '0',
		`winnermarkedaspaid` INT(1) NOT NULL default '0',
		`winnermarkedaspaiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`winnermarkedaspaidmethod` MEDIUMTEXT,
		`buyerpaymethod` VARCHAR(250) NOT NULL default '',
		`buyershipcost` FLOAT(10,2) NOT NULL default '0.00',
		`buyershipperid` INT(5) NOT NULL default '0',
		`sellermarkedasshipped` INT(1) NOT NULL default '0',
		`sellermarkedasshippeddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (`id`),
		INDEX (`bid_id`),
		INDEX (`project_id`),
		INDEX ( `user_id` ),
		INDEX ( `project_user_id` ),
		INDEX ( `bidstatus` ),
		INDEX ( `bidstate` ),
		INDEX ( `bidamounttype` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_realtimebids</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_bid_retracts");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_bid_retracts (
		`retractid` INT(10) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`bid_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`reason` MEDIUMTEXT,
		`date` DATE NOT NULL default '0000-00-00',
		PRIMARY KEY  (`retractid`),
		INDEX ( `user_id` ),
		INDEX ( `bid_id` ),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_bid_retracts</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_invitations");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_invitations (
		`id` INT(200) NOT NULL AUTO_INCREMENT,
		`project_id` INT(200) NOT NULL default '0',
		`buyer_user_id` INT(200) NOT NULL default '0',
		`seller_user_id` INT(200) NOT NULL default '0',
		`email` VARCHAR(100) NOT NULL default '',
		`name` VARCHAR(250) NOT NULL default '',
		`invite_message` MEDIUMTEXT,
		`date_of_invite` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_of_bid` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_of_remind` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`bid_placed` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		INDEX ( `project_id` ),
		INDEX ( `buyer_user_id` ),
		INDEX ( `seller_user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_invitations</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_questions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_questions (
		`questionid` INT(10) NOT NULL AUTO_INCREMENT,
		`cid` INT(10) NOT NULL default '0',
		`question_eng` MEDIUMTEXT,
		`description_eng` MEDIUMTEXT,
		`formname` VARCHAR(100) NOT NULL default '',
		`formdefault` VARCHAR(100) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','range','url') NOT NULL default 'text',
		`multiplechoice` MEDIUMTEXT,
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`cansearch` INT(1) NOT NULL default '0',
		`canremove` INT(1) NOT NULL default '1',
		`recursive` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`questionid`),
		INDEX ( `cid` ),
		INDEX ( `inputtype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_questions</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "proxybid");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "proxybid (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`project_id` INT(11) NOT NULL default '0',
		`user_id` INT(11) NOT NULL default '0',
		`maxamount` FLOAT(10,2) NOT NULL default '0.00',
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`id`),
		INDEX ( `project_id` ),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "proxybid</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "referral_clickthroughs");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "referral_clickthroughs (
		`rid` VARCHAR(20) default '',
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`browser` VARCHAR(200) default '',
		`ipaddress` VARCHAR(50) default NULL,
		`referrer` MEDIUMTEXT,
		KEY `rid` (`rid`),
		INDEX ( `ipaddress` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "referral_clickthroughs</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "referral_data");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "referral_data (
		`id` INT(200) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`referred_by` INT(10) NOT NULL default '0',
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`postauction` INT(10) NOT NULL default '0',
		`awardauction` INT(10) NOT NULL default '0',
		`paysubscription` INT(10) NOT NULL default '0',
		`payfvf` INT(10) NOT NULL default '0',
		`payins` INT(10) NOT NULL default '0',
		`paylanceads` INT(10) NOT NULL default '0',
		`payportfolio` INT(10) NOT NULL default '0',
		`paycredentials` INT(10) NOT NULL default '0',
		`payenhancements` INT(10) NOT NULL default '0',
		`invoiceid` INT(10) NOT NULL default '0',
		`paidout` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		INDEX ( `user_id` ),
		INDEX ( `referred_by` ),
		INDEX ( `invoiceid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "referral_data</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "register_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "register_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`user_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "register_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "register_questions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "register_questions (
		`questionid` INT(10) NOT NULL AUTO_INCREMENT,
		`pageid` INT(5) NOT NULL default '0',
		`question_eng` MEDIUMTEXT,
		`description_eng` MEDIUMTEXT,
		`formname` VARCHAR(100) NOT NULL default '',
		`formdefault` VARCHAR(100) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','range') NOT NULL default 'text',
		`multiplechoice` MEDIUMTEXT,
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`profile` INT(1) NOT NULL default '1',
		`cansearch` INT(1) NOT NULL default '0',
		`guests` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`questionid`),
		INDEX ( `pageid` ),
		INDEX ( `inputtype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "register_questions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "rssfeeds");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "rssfeeds (
		`rssid` INT( 10 ) NOT NULL AUTO_INCREMENT ,
		`rssname` VARCHAR( 200 ) NOT NULL default '',
		`rssurl` VARCHAR( 250 ) NOT NULL default '',
		`sort` INT( 50 ) NOT NULL default '0',
		PRIMARY KEY (`rssid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "rssfeeds</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "search");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "search (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`keyword` MEDIUMTEXT,
		`searchmode` MEDIUMTEXT,
		`count` INT(100) NOT NULL default '0',
		PRIMARY KEY  (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "search</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "search_favorites");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "search_favorites (
		`searchid` INT(10) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL,
		`searchoptions` MEDIUMTEXT,
		`searchoptionstext` MEDIUMTEXT,
		`title` VARCHAR(200) NOT NULL,
		`cattype` ENUM('service','product','experts','stores','wantads') NOT NULL default 'service',
		`subscribed` INT(1) NOT NULL default '0',
		`added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`lastsent` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`lastseenids` MEDIUMTEXT,
		PRIMARY KEY  (`searchid`),
		INDEX ( `user_id` ),
		INDEX ( `cattype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "search_favorites</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "search_users");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "search_users (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL,
		`keyword` MEDIUMTEXT,
		`searchmode` MEDIUMTEXT,
		`added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`id`),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "search_users</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "sessions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "sessions (
		`sesskey` VARCHAR(32) NOT NULL default '',
		`expiry` INT(11) NOT NULL default '0',
		`value` MEDIUMTEXT,
		`userid` INT(11) NOT NULL default '0',
		`isuser` INT(1) NOT NULL default '0',
		`isadmin` INT(1) NOT NULL default '0',
		`isrobot` INT(1) NOT NULL default '0',
		`iserror` INT(1) NOT NULL default '0',
                `languageid` INT(1) NOT NULL default '0',
		`styleid` INT(1) NOT NULL default '0',
		`agent` MEDIUMTEXT,
		`ipaddress` VARCHAR(25) NOT NULL default '',
		`url` VARCHAR(250) NOT NULL default '',
		`title` VARCHAR(100) NOT NULL default '',
		`firstclick` VARCHAR(50) NOT NULL default '',
		`lastclick` VARCHAR(50) NOT NULL default '',
		`browser` VARCHAR(50) NOT NULL default 'unknown',
		`token` VARCHAR(32) NOT NULL default '',
		`sesskeyapi` VARCHAR(250) NOT NULL default '',
		`siteid` VARCHAR(20) NOT NULL default '001',
		PRIMARY KEY  (`sesskey`),
		INDEX ( `userid` ),
		INDEX ( `ipaddress` ),
		INDEX ( `token` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "sessions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "shipping_rates_cache");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "shipping_rates_cache (
		`carrier` VARCHAR(250) NOT NULL default '',
		`shipcode` VARCHAR(250) NOT NULL default '',
		`from_country` VARCHAR(250) NOT NULL default '',
		`from_zipcode` VARCHAR(250) NOT NULL default '',
		`to_country` VARCHAR(250) NOT NULL default '',
		`to_zipcode` VARCHAR(250) NOT NULL default '',
		`weight` DOUBLE NOT NULL default '1.0',
		`datetime` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`gatewayresult` MEDIUMTEXT,
		`traffic` INT(5) NOT NULL default '1',
		INDEX ( `carrier` ),
		INDEX ( `from_country` ),
		INDEX ( `from_zipcode` ),
		INDEX ( `to_country` ),
		INDEX ( `to_zipcode` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "shipping_rates_cache</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "skills");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "skills (
		`cid` int(100) NOT NULL auto_increment,
		`parentid` int(100) NOT NULL default '0',
		`level` int(5) NOT NULL default '1',
		`title_eng` mediumtext,
		`description_eng` mediumtext,
		`views` int(100) NOT NULL default '0',
		`keywords` mediumtext,
		`visible` int(1) NOT NULL default '1',
		`sort` int(3) NOT NULL default '0',
		PRIMARY KEY  (`cid`),
		INDEX ( `parentid` ),
		INDEX ( `level` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "skills</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "skills
		(`cid`, `parentid`, `level`, `title_eng`, `description_eng`, `views`, `keywords`, `visible`, `sort`)
		VALUES
		(1, 0, 1, 'Programming', 'Programming', 0, 'Programming', 1, 0),
		(2, 1, 2, 'AJAX', 'AJAX', 0, 'AJAX', 1, 0),
		(3, 1, 2, 'ASP', 'ASP', 0, 'ASP', 1, 0),
		(4, 1, 2, 'ASP.NET+ADO', 'ASP.NET+ADO', 0, 'ASP.NET+ADO', 1, 0),
		(5, 1, 2, 'ActiveX', 'ActiveX', 0, 'ActiveX', 1, 0),
		(6, 1, 2, 'Adobe Flex', 'Adobe Flex', 0, 'Adobe Flex', 1, 0),
		(7, 1, 2, 'Assembler', 'Assembler', 0, 'Assembler', 1, 0),
		(8, 1, 2, 'Borland C++ Builder', 'Borland C++ Builder', 0, 'Borland C++ Builder', 1, 0),
		(9, 1, 2, 'C#/.Net', 'C#/.Net', 0, 'C#/.Net', 1, 0),
		(10, 1, 2, 'C/C++/Unix', 'C/C++/Unix', 0, 'C/C++/Unix', 1, 0),
		(11, 1, 2, 'C/C++/Win32SDK', 'C/C++/Win32SDK', 0, 'C/C++/Win32SDK', 1, 0),
		(12, 1, 2, 'CSS', 'CSS', 0, 'CSS', 1, 0),
		(13, 1, 2, 'CodeWarrior/C++', 'CodeWarrior/C++', 0, 'CodeWarrior/C++', 1, 0),
		(14, 1, 2, 'ColdFusion', 'ColdFusion', 0, 'ColdFusion', 1, 0),
		(15, 1, 2, 'Crystal Reports', 'Crystal Reports', 0, 'Crystal Reports', 1, 0),
		(16, 1, 2, 'Delphi ', 'Delphi ', 0, 'Delphi ', 1, 0),
		(17, 1, 2, 'Delphi/VB', 'Delphi/VB', 0, 'Delphi/VB', 1, 0),
		(18, 1, 2, 'Driver development', 'Driver development', 0, 'Driver development', 1, 0),
		(19, 1, 2, 'Flash/ActionScript', 'Flash/ActionScript', 0, 'Flash/ActionScript', 1, 0),
		(20, 1, 2, 'FoxPro', 'FoxPro', 0, 'FoxPro', 1, 0),
		(21, 1, 2, 'GTK programming', 'GTK programming', 0, 'GTK programming', 1, 0),
		(22, 1, 2, 'Games/Windows', 'Games/Windows', 0, 'Games/Windows', 1, 0),
		(23, 1, 2, 'HTML/DHTML', 'HTML/DHTML', 0, 'HTML/DHTML', 1, 0),
		(24, 1, 2, 'Hyperion', 'Hyperion', 0, 'Hyperion', 1, 0),
		(25, 1, 2, 'IntelliJ IDEA', 'IntelliJ IDEA', 0, 'IntelliJ IDEA', 1, 0),
		(26, 1, 2, 'J2EE', 'J2EE', 0, 'J2EE', 1, 0),
		(27, 1, 2, 'JBoss', 'JBoss', 0, 'JBoss', 1, 0),
		(28, 1, 2, 'JFC', 'JFC', 0, 'JFC', 1, 0),
		(29, 1, 2, 'JSP', 'JSP', 0, 'JSP', 1, 0),
		(30, 1, 2, 'JavaScript', 'JavaScript', 0, 'JavaScript', 1, 0),
		(31, 1, 2, 'Kylix ', 'Kylix ', 0, 'Kylix ', 1, 0),
		(32, 1, 2, 'LaTeX', 'LaTeX', 0, 'LaTeX', 1, 0),
		(33, 1, 2, 'Lingo', 'Lingo', 0, 'Lingo', 1, 0),
		(34, 1, 2, 'Mason', 'Mason', 0, 'Mason', 1, 0),
		(35, 1, 2, 'OCX', 'OCX', 0, 'OCX', 1, 0),
		(36, 1, 2, 'PHP', 'PHP', 0, 'PHP', 1, 0),
		(37, 1, 2, 'PHP/HTML/DHTML', 'PHP/HTML/DHTML', 0, 'PHP/HTML/DHTML', 1, 0),
		(38, 1, 2, 'PHP/IIS/MS SQL', 'PHP/IIS/MS SQL', 0, 'PHP/IIS/MS SQL', 1, 0),
		(39, 1, 2, 'PHP/MySQL', 'PHP/MySQL', 0, 'PHP/MySQL', 1, 0),
		(40, 1, 2, 'Perl', 'Perl', 0, 'Perl', 1, 0),
		(41, 1, 2, 'Python', 'Python', 0, 'Python', 1, 0),
		(42, 1, 2, 'Qt', 'Qt', 0, 'Qt', 1, 0),
		(43, 1, 2, 'Remoting', 'Remoting', 0, 'Remoting', 1, 0),
		(44, 1, 2, 'Resin', 'Resin', 0, 'Resin', 1, 0),
		(45, 1, 2, 'Ruby', 'Ruby', 0, 'Ruby', 1, 0),
		(46, 1, 2, 'SOAP', 'SOAP', 0, 'SOAP', 1, 0),
		(47, 1, 2, 'SatelliteForms', 'SatelliteForms', 0, 'SatelliteForms', 1, 0),
		(48, 1, 2, 'Smarty', 'Smarty', 0, 'Smarty', 1, 0),
		(49, 1, 2, 'Struts', 'Struts', 0, 'Struts', 1, 0),
		(50, 1, 2, 'SyncML', 'SyncML', 0, 'SyncML', 1, 0),
		(51, 1, 2, 'TCP/IP', 'TCP/IP', 0, 'TCP/IP', 1, 0),
		(52, 1, 2, 'Tomcat', 'Tomcat', 0, 'Tomcat', 1, 0),
		(53, 1, 2, 'Unix Shell', 'Unix Shell', 0, 'Unix Shell', 1, 0),
		(54, 1, 2, 'VB/.NET', 'VB/.NET', 0, 'VB/.NET', 1, 0),
		(55, 1, 2, 'VB/Delphi', 'VB/Delphi', 0, 'VB/Delphi', 1, 0),
		(56, 1, 2, 'VB/Delphi/ASP/IIS', 'VB/Delphi/ASP/IIS', 0, 'VB/Delphi/ASP/IIS', 1, 0),
		(57, 1, 2, 'VBA', 'VBA', 0, 'VBA', 1, 0),
		(58, 1, 2, 'Visual Basic ', 'Visual Basic ', 0, 'Visual Basic ', 1, 0),
		(59, 1, 2, 'VoiceXML', 'VoiceXML', 0, 'VoiceXML', 1, 0),
		(60, 1, 2, 'WML/WMLScript', 'WML/WMLScript', 0, 'WML/WMLScript', 1, 0),
		(61, 1, 2, 'WordPress', 'WordPress', 0, 'WordPress', 1, 0),
		(62, 1, 2, 'XML', 'XML', 0, 'XML', 1, 0),
		(63, 1, 2, 'XML-RPC', 'XML-RPC', 0, 'XML-RPC', 1, 0),
		(64, 1, 2, 'XUL', 'XUL', 0, 'XUL', 1, 0),
		(65, 1, 2, 'Zope/Python', 'Zope/Python', 0, 'Zope/Python', 1, 0),
		(66, 0, 1, 'Databases', 'Databases', 0, 'Databases', 1, 0),
		(67, 66, 2, 'Access', 'Access', 0, 'Access', 1, 0),
		(68, 66, 2, 'Cobol', 'Cobol', 0, 'Cobol', 1, 0),
		(69, 66, 2, 'Filemaker Pro ', 'Filemaker Pro ', 0, 'Filemaker Pro ', 1, 0),
		(70, 66, 2, 'Informix', 'Informix', 0, 'Informix', 1, 0),
		(71, 66, 2, 'InterBase', 'InterBase', 0, 'InterBase', 1, 0),
		(72, 66, 2, 'MS-SQL', 'MS-SQL', 0, 'MS-SQL', 1, 0),
		(73, 66, 2, 'MySQL', 'MySQL', 0, 'MySQL', 1, 0),
		(74, 66, 2, 'Oracle DBA', 'Oracle DBA', 0, 'Oracle DBA', 1, 0),
		(75, 66, 2, 'Oracle Forms', 'Oracle Forms', 0, 'Oracle Forms', 1, 0),
		(76, 66, 2, 'Oracle PL/SQL', 'Oracle PL/SQL', 0, 'Oracle PL/SQL', 1, 0),
		(77, 66, 2, 'Oracle Reports', 'Oracle Reports', 0, 'Oracle Reports', 1, 0),
		(78, 66, 2, 'PostgreSQ', 'PostgreSQ', 0, 'PostgreSQ', 1, 0),
		(79, 66, 2, 'SQL', 'SQL', 0, 'SQL', 1, 0),
		(80, 66, 2, 'SQLite', 'SQLite', 0, 'SQLite', 1, 0),
		(81, 66, 2, 'Sybase', 'Sybase', 0, 'Sybase', 1, 0),
		(82, 0, 1, 'Mobile', 'Mobile', 0, 'Mobile', 1, 0),
		(83, 82, 2, 'Blackberry/RIM ', 'Blackberry/RIM ', 0, 'Blackberry/RIM ', 1, 0),
		(84, 82, 2, 'J2ME', 'J2ME', 0, 'J2ME', 1, 0),
		(85, 82, 2, 'PalmOS', 'PalmOS', 0, 'PalmOS', 1, 0),
		(86, 82, 2, 'PocketPC', 'PocketPC', 0, 'PocketPC', 1, 0),
		(87, 82, 2, 'Symbian SDK', 'Symbian SDK', 0, 'Symbian SDK', 1, 0),
		(88, 0, 1, 'Design/Graphics', 'Design/Graphics', 0, 'Design/Graphics', 1, 0),
		(89, 88, 2, '3D Design', '3D Design', 0, '3D Design', 1, 0),
		(90, 88, 2, 'Design/Flash', 'Design/Flash', 0, 'Design/Flash', 1, 0),
		(91, 88, 2, 'Flash/Macromedia', 'Flash/Macromedia', 0, 'Flash/Macromedia', 1, 0),
		(92, 88, 2, 'Graphics', 'Graphics', 0, 'Graphics', 1, 0),
		(93, 88, 2, 'Macromedia Director', 'Macromedia Director', 0, 'Macromedia Director', 1, 0),
		(94, 88, 2, 'Photoshop', 'Photoshop', 0, 'Photoshop', 1, 0),
		(95, 88, 2, 'QNX', 'QNX', 0, 'QNX', 1, 0),
		(96, 88, 2, 'UI Design', 'UI Design', 0, 'UI Design', 1, 0),
		(97, 88, 2, 'Video Streaming', 'Video Streaming', 0, 'Video Streaming', 1, 0),
		(98, 0, 1, 'Systems Admin', 'Systems Admin', 0, 'Systems Admin', 1, 0),
		(99, 98, 2, 'AS/400', 'AS/400', 0, 'AS/400', 1, 0),
		(100, 98, 2, 'LAMP administration ', 'LAMP administration ', 0, 'LAMP administration ', 1, 0),
		(101, 98, 2, 'Mac OS X', 'Mac OS X', 0, 'Mac OS X', 1, 0),
		(102, 98, 2, 'Windows Administration', 'Windows Administration', 0, 'Windows Administration', 1, 0),
		(103, 0, 1, 'Application Servers', 'Application Servers', 0, 'Application Servers', 1, 0),
		(104, 103, 2, 'Asterisk', 'Asterisk', 0, 'Asterisk', 1, 0),
		(105, 103, 2, 'Lotus Domino', 'Lotus Domino', 0, 'Lotus Domino', 1, 0),
		(106, 103, 2, 'Lotus Notes', 'Lotus Notes', 0, 'Lotus Notes', 1, 0),
		(107, 103, 2, 'MS Navision', 'MS Navision', 0, 'MS Navision', 1, 0),
		(108, 103, 2, 'Oracle Application Server', 'Oracle Application Server', 0, 'Oracle Application Server', 1, 0),
		(109, 103, 2, 'OsCommerce', 'OsCommerce', 0, 'OsCommerce', 1, 0),
		(110, 103, 2, 'Web Sphere', 'Web Sphere', 0, 'Web Sphere', 1, 0),
		(111, 103, 2, 'WebLogic', 'WebLogic', 0, 'WebLogic', 1, 0),
		(112, 0, 1, 'Platforms', 'Platforms', 0, 'Platforms', 1, 0),
		(113, 112, 2, 'DotNetNuke', 'DotNetNuke', 0, 'DotNetNuke', 1, 0),
		(114, 112, 2, 'EDI', 'EDI', 0, 'EDI', 1, 0),
		(115, 112, 2, 'Hibernate', 'Hibernate', 0, 'Hibernate', 1, 0),
		(116, 112, 2, 'Joomla', 'Joomla', 0, 'Joomla', 1, 0),
		(117, 112, 2, 'Mambo', 'Mambo', 0, 'Mambo', 1, 0),
		(118, 112, 2, 'Online Payments', 'Online Payments', 0, 'Online Payments', 1, 0),
		(119, 112, 2, 'PowerBuilder', 'PowerBuilder', 0, 'PowerBuilder', 1, 0),
		(120, 112, 2, 'Sharepoint', 'Sharepoint', 0, 'Sharepoint', 1, 0),
		(121, 112, 2, 'Voice/Windows', 'Voice/Windows', 0, 'Voice/Windows', 1, 0),
		(122, 112, 2, 'Wireless', 'Wireless', 0, 'Wireless', 1, 0),
		(123, 112, 2, 'phpNuke', 'phpNuke', 0, 'phpNuke', 1, 0),
		(124, 112, 2, 'postNuke', 'postNuke', 0, 'postNuke', 1, 0),
		(125, 0, 1, 'Concepts', 'Concepts', 0, 'Concepts', 1, 0),
		(126, 125, 2, 'Application Design', 'Application Design', 0, 'Application Design', 1, 0),
		(127, 125, 2, 'Database Modeling', 'Database Modeling', 0, 'Database Modeling', 1, 0),
		(128, 125, 2, 'Systems Programming', 'Systems Programming', 0, 'Systems Programming', 1, 0),
		(129, 125, 2, 'UML', 'UML', 0, 'UML', 1, 0),
		(130, 125, 2, 'VoIP', 'VoIP', 0, 'VoIP', 1, 0),
		(131, 0, 1, 'Other', 'Other', 0, 'Other', 1, 0),
		(132, 131, 2, 'Data Entry', 'Data Entry', 0, 'Data Entry', 1, 0),
		(133, 131, 2, 'Project Management', 'Project Management', 0, 'Project Management', 1, 0),
		(134, 131, 2, 'QA', 'QA', 0, 'QA', 1, 0),
		(135, 131, 2, 'Recruiting', 'Recruiting', 0, 'Recruiting', 1, 0),
		(136, 131, 2, 'SEO', 'SEO', 0, 'SEO', 1, 0),
		(137, 131, 2, 'Search', 'Search', 0, 'Search', 1, 0),
		(138, 131, 2, 'Tech Writer', 'Tech Writer', 0, 'Tech Writer', 1, 0),
		(139, 131, 2, 'Testing', 'Testing', 0, 'Testing', 1, 0)
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported 139 default skills (IT / Tech Related)</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "skills_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "skills_answers (
		`aid` INT(5) NOT NULL AUTO_INCREMENT,
		`cid` INT(5) NOT NULL,
		`user_id` INT(10) NOT NULL,
		PRIMARY KEY  (`aid`),
		INDEX ( `cid` ),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "skills_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "shippers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "shippers (
		`shipperid` INT(5) NOT NULL AUTO_INCREMENT,
		`title` MEDIUMTEXT,
		`shipcode` VARCHAR(250) NOT NULL,
		`domestic` INT(1) NOT NULL default '1',
		`international` INT(1) NOT NULL default '0',
		`carrier` VARCHAR(250) NOT NULL,
		PRIMARY KEY  (`shipperid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "shippers</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "shippers
		(`shipperid`, `title`, `shipcode`, `domestic`, `international`, `carrier`)
		VALUES
		(NULL, 'FedEx Priority Overnight', '01', 1, 0, 'fedex'),
		(NULL, 'FedEx First Class', '06', 1, 0, 'fedex'),
		(NULL, 'FedEx 2-Day Air', '03', 1, 0, 'fedex'),
		(NULL, 'FedEx Standard Overnight', '05', 1, 0, 'fedex'),
		(NULL, 'FedEx Express Saver', '20', 1, 0, 'fedex'),
		(NULL, 'FedEx Home Delivery', '90', 1, 0, 'fedex'),
		(NULL, 'FedEx Ground (1 to 6 business days)', '92', 1, 0, 'fedex'),
		(NULL, 'FedEx International Priority Overnight', '01', 0, 1, 'fedex'),
		(NULL, 'FedEx International First Class', '06', 0, 1, 'fedex'),
		(NULL, 'FedEx International Economy', '03', 0, 1, 'fedex'),
		(NULL, 'FedEx International Home Delivery', '90', 0, 1, 'fedex'),
		(NULL, 'FedEx International Ground', '92', 0, 1, 'fedex'),
		(NULL, 'UPS Ground (1 to 6 business days)', '03', 1, 0, 'ups'),
		(NULL, 'UPS 3-Day Select', '12', 1, 0, 'ups'),
		(NULL, 'UPS 2nd Day Air', '02', 1, 0, 'ups'),
		(NULL, 'UPS Next Day Air Saver', '13', 1, 0, 'ups'),
		(NULL, 'UPS Next Day Air Early AM', '14', 1, 0, 'ups'),
		(NULL, 'UPS Next Day Air', '01', 1, 0, 'ups'),
		(NULL, 'UPS Worldwide Express', '07', 1, 1, 'ups'),
		(NULL, 'UPS Worldwide Expedited', '08', 1, 1, 'ups'),
		(NULL, 'UPS Standard', '11', 1, 0, 'ups'),
		(NULL, 'UPS Next Day Air Saver', '13', 1, 0, 'ups'),
		(NULL, 'UPS Worldwide Express Plus', '54', 1, 1, 'ups'),
		(NULL, 'UPS Express Saver', '65', 1, 0, 'ups'),
		(NULL, 'USPS Express Mail', 'Express', 1, 0, 'usps'),
		(NULL, 'USPS First Class Mail', 'First Class', 1, 0, 'usps'),
		(NULL, 'USPS Priority Mail', 'Priority', 1, 0, 'usps'),
		(NULL, 'USPS Parcel Mail', 'Parcel', 1, 0, 'usps'),
		(NULL, 'USPS Library Mail', 'Library', 1, 0, 'usps'),
		(NULL, 'USPS BPM Mail', 'BPM', 1, 0, 'usps'),
		(NULL, 'USPS Media Mail', 'Media', 1, 0, 'usps')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported 17 various shipment options (UPS, Ground, Standard, etc)</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "stars");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "stars (
		`starid` INT(5) NOT NULL AUTO_INCREMENT,
		`pointsfrom` INT(5) NOT NULL default '0',
		`pointsto` INT(5) NOT NULL default '0',
		`icon` VARCHAR(20) NOT NULL default '',
		PRIMARY KEY (`starid`),
		INDEX ( `pointsfrom` ),
		INDEX ( `pointsto` ),
		INDEX ( `icon` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "stars</li>";
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('1', '0', '49', 'star1.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('2', '50', '99', 'star2.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('3', '100', '499', 'star3.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('4', '500', '999', 'star4.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('5', '1000', '4999', 'star4.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('6', '5000', '10000', 'star5.gif')");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported 6 levels of feedback rating stars</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "styles");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "styles (
		`styleid` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(100) NOT NULL default '',
		`visible` INT(1) NOT NULL default '0',
		`sort` INT(3) NOT NULL default '0',
		PRIMARY KEY `styleid` (`styleid`),
		INDEX ( `name` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "styles</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription (
		`subscriptionid` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
		`title` VARCHAR(100) NOT NULL default '',
		`description` VARCHAR(255) NOT NULL default '',
		`cost` FLOAT(10,2) NOT NULL default '0.00',
		`length` VARCHAR(10) NOT NULL default '1',
		`units` ENUM('D','M','Y') NOT NULL default 'M',
		`subscriptiongroupid` INT(10) NOT NULL default '0',
		`roleid` INT(5) NOT NULL default '-1',
		`active` ENUM('yes','no') NOT NULL default 'no',
		`canremove` INT(1) NOT NULL default '1',
		`visible` INT(1) NOT NULL default '1',
		`icon` VARCHAR(250) NOT NULL default 'images/default/icons/default.gif',
		`migrateto` INT(10) NOT NULL default '0',
		`migratelogic` ENUM('none','waived','unpaid','paid') NOT NULL default 'none',
		PRIMARY KEY  (`subscriptionid`),
		INDEX ( `title` ),
		INDEX ( `subscriptiongroupid` ),
		INDEX ( `roleid` ),
		INDEX ( `active` ),
		INDEX ( `migrateto` ),
		INDEX ( `migratelogic` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription</li>";
    
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "subscription
		VALUES
		(1, 'Default Plan', 'Click view access to see subscription permissions', '0.00', '1', 'Y', '1', '1', 'yes', 0, 1, 'images/default/icons/default.gif', '0', 'none')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default subscription plan (Default Plan)</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscriptionlog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscriptionlog (
		`subscriptionlogid` INT(200) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`date_sent` DATE NOT NULL default '0000-00-00',
		PRIMARY KEY  (`subscriptionlogid`),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscriptionlog</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_group");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_group (
		`subscriptiongroupid` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
		`title` VARCHAR(100) NOT NULL default '',
		`description` VARCHAR(250) NOT NULL default '',
		`canremove` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`subscriptiongroupid`),
		INDEX ( `title` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription_group</li>";
    
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "subscription_group
		VALUES
		(1, 'Default Permissions', 'Default permissions for the default subscription plan', 0)
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default subscription permissions group (Default Permissions)</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_permissions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_permissions (
		`id` INT(5) NOT NULL AUTO_INCREMENT,
		`subscriptiongroupid` INT(2) NOT NULL default '1',
		`accessname` VARCHAR(250) NOT NULL default '',
		`accesstext_eng` MEDIUMTEXT,
		`accessdescription_eng` MEDIUMTEXT,
		`accesstype` ENUM('yesno','int') NOT NULL default 'yesno',
		`value` VARCHAR(250) NOT NULL default '',
		`canremove` INT(1) NOT NULL default '1',
		`original` INT(1) NOT NULL default '0',
		`iscustom` INT(1) NOT NULL default '1',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`id`),
		INDEX ( `subscriptiongroupid` ),
		INDEX ( `accessname` ),
		INDEX ( `accesstype` ),
		INDEX ( `value` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription_permissions</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "subscription_permissions 
		VALUES
		(NULL, 1, 'attachments', 'Attachment Resources', 'Defines if any customer within this subscription group can upload and use the attachment resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'attachlimit', 'Attach Limit', 'Total attachment storage space for any customer within this permission group', 'int', '1500000', 0, 1, 0, 1),
		(NULL, 1, 'uploadlimit', 'Upload Limit', 'Total upload limit per upload session within this permission group', 'int', '1500000', 0, 1, 0, 1),
		(NULL, 1, 'auctiondelists', 'Auction Delists per Month', 'Total amount of auction delists (cancellations) for any customer within this permission group', 'int', '1', 0, 1, 0, 1),
		(NULL, 1, 'bidretracts', 'Bid Retracts per Month', 'Total amount of bid retracts (cancellations) for any customer within this permission group', 'int', '5', 0, 1, 0, 1),
		(NULL, 1, 'bidlimitperday', 'Bid Limit per Day', 'Total amount of bids a customer can place in a 24 hour period', 'int', '15', 0, 1, 0, 1),
		(NULL, 1, 'bidlimitpermonth', 'Bid Limit per Month', 'Total amount of bids a customer can place in a single month', 'int', '15', 0, 1, 0, 1),
		(NULL, 1, 'deposit', 'Deposit Funds Resources', 'Defines if any customer within this subscription group can use the deposit funds resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'withdraw', 'Withdraw Funds Resources', 'Defines if any customer within this subscription group can use the withdraw funds resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'addcreditcard', 'Add Credit Card Resources', 'Defines if any customer within this subscription group can use the add new credit card resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'delcreditcard', 'Remove Credit Card Resources', 'Defines if any customer within this subscription group can use the remove existing credit card resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'usecreditcard', 'Can Use Credit Card Resources', 'Defines if any customer within this subscription group can use the credit card resources', 'yesno', 'yes', 0, 1, 0, 1),		
		(NULL, 1, 'addbankaccount', 'Add Bank Account Resources', 'Defines if any customer within this subscription group can use the add new bank account resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'delbankaccount', 'Remove Bank Account Resources', 'Defines if any customer within this subscription group can use the remove existing bank account resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'usebankaccount', 'Can Use Bank Account Resources', 'Defines if any customer within this subscription group can use the bank account resources', 'yesno', 'yes', 0, 1, 0, 1),		
		(NULL, 1, 'buynow', 'Purchase Now Resources', 'Defines if any customer within this subscription group has permission to buy or sell via purchase now feature', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'searchresults', 'Search Results Resources', 'Defines if any customer within this subscription group has permission to be listed within the search results listings (service professionals)', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'workshare', 'MediaShare Resources', 'Defines if any customer within this subscription group has permissions to use the mediashare resources after a service or product auction award', 'yesno', 'yes', 0, 1, 0, 1),		
		(NULL, 1, 'pmb', 'Private Message Board Resources', 'Defines if any customer within this subscription group can use the private message board resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'pmbtotal', 'Private Messages Limit', 'Total amount of private message boards a customer can create within this subscription group', 'int', '500', 0, 1, 0, 1),
		(NULL, 1, 'maxpmbattachments', 'Maximum PMB Attachments', 'Total number of PMB attachments permitted', 'int', '1', 0, 1, 0, 1),
		(NULL, 1, 'pmbcompose', 'Private Message Composing', 'Defines if any customer within this subscription group can compose new private messages to other registered members without going through the auction event process', 'yesno', 'no', 0, 1, 0, 1),
		(NULL, 1, 'enablecurrencyconversion', 'Currency Conversion Resources', 'Defines if any customer within this subscription group can use the currency conversion resources and have the default marketplace value calculate to their local rates (on the fly)', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'distance', 'Distance Calculation Resources', 'Defines if any customer within this subscription group can use the distance calculation (point-to-point and radius search) resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'createserviceauctions', 'Can Create Service Auctions', 'Can create service auctions for bidding', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'servicebid', 'Place Bids on Service Auctions', 'Defines if any customer within this subscription group can place new bids on service auction resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'generateinvoices', 'Invoice Generation Resources', 'Defines if any customer within this subscription group can use the generate new invoice to customer resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'createproductauctions', 'Can Create Product Auctions', 'Can create product auctions for bidding', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'productbid', 'Place Bids on Product Auctions', 'Defines if any customer within this subscription group can place new bids on product auction resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'addportfolio', 'Add Portfolio Resources', 'Defines if any customer within this subscription group can add and create new portfolio resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'inviteprovider', 'Provider Invitation Resources', 'Defines if any customer within this subscription group can use the invitation to auction resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'addtowatchlist', 'Add Watch List Resources', 'Defines if any customer within this subscription group can use the add new items to watchlist resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'iprestrict', 'IP Restriction Resources', 'Defines if any customer within this subscription group can use the ''restrict my future logins to my static ip address'' resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'maxbidattachments', 'Maximum Bid Attachments', 'Total number of bid attachments permitted', 'int', '5', 0, 1, 0, 1),
		(NULL, 1, 'maxprojectattachments', 'Maximum Service Auction Attachments', 'Total number of service auction attachments permitted', 'int', '5', 0, 1, 0, 1),
		(NULL, 1, 'createserviceprofile', 'Create Service Profile', 'Can create a service selling profile', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'updateprofile', 'Update Profile Resources', 'Defines if any customer within this subscription group can update their existing profile resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'maxprofileattachments', 'Maximum Profile Attachments', 'Total number of profile attachments permitted', 'int', '1', 0, 1, 0, 1),
		(NULL, 1, 'maxprofilegroups', 'Maximum Profile Categories Opt-In', 'Maximum amount of selectable profile categories a user within this subscription can opt-in', 'int', '5', 0, 1, 0, 1),
		(NULL, 1, 'maxportfolioattachments', 'Maximum Portfolio Attachments', 'Total number of portfolio attachments permitted', 'int', '5', 0, 1, 0, 1),
		(NULL, 1, 'fvfexempt', 'Exempt From Final Value Fees', 'Defines if a customer within this subscription group is exempt from Final Value Fees', 'yesno', 'no', 0, 1, 0, 1),
		(NULL, 1, 'insexempt', 'Exempt From Insertion Fees', 'Defines if a customer within this subscription group is exempt from Insertion Fees', 'yesno', 'no', 0, 1, 0, 1),
		(NULL, 1, 'payasgoexempt', 'Exempt From Pay as you Go', 'Defines if a customer within this subscription group is exempt from Pay as you go.  When enabled, the users posted listing will automatically be visible without having to pay before it going live.', 'yesno', 'no', 0, 1, 0, 1),
		(NULL, 1, 'newsletteropt_in', 'Newsletter Resources', 'Defines if any customer within this subscription group can opt-in to any of the available newsletter resources', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'cansealbids', 'Can use Sealed Bidding', 'Defines if any customer within this subscription group can set sealed bidding privacy when listing an auction', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'canblindbids', 'Can use Blind Bidding', 'Defines if any customer within this subscription group can set blind bidding privacy when listing an auction', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'canfullprivacybids', 'Can use Full Bid Privacy (Sealed + Blind)', 'Defines if any customer within this subscription group can set full bidding privacy when listing an auction', 'yesno', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'maxskillscat', 'Maximum Skill Categories Opt-In', 'Maximum amount of selectable skill categories a user within this subscription can opt-in', 'int', '5', 0, 1, 0, 1)
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default subscription permissions (Default Permissions)</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_roles");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_roles (
		`roleid` INT(5) NOT NULL AUTO_INCREMENT,
		`purpose` VARCHAR(250) NOT NULL default '',
		`title` VARCHAR(250) NOT NULL default '',
		`custom` VARCHAR(200) NOT NULL default '',
		`roletype` ENUM('service','product','both') NOT NULL default 'service',
		`roleusertype` ENUM('servicebuyer','serviceprovider','productbuyer','merchantprovider','all') NOT NULL default 'servicebuyer',
		`active` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`roleid`),
		INDEX ( `title` ),
		INDEX ( `roletype` ),
		INDEX ( `roleusertype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "subscription_roles</li>";
	flush();
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "subscription_roles VALUES (1, 'Create service auctions', 'Service Buyer', '[fbscore] [stars] [verified] [subscription]', 'service', 'servicebuyer', 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "subscription_roles VALUES (2, 'Bid on service auctions', 'Service Provider', '[fbscore] [stars] [store] [verified] [subscription]', 'service', 'serviceprovider', 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "subscription_roles VALUES (3, 'Sell your items and merchandise', 'Merchant Provider', '[fbscore] [stars] [store] [verified] [subscription]', 'product', 'merchantprovider', 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "subscription_roles VALUES (4, 'Bid on items and merchandise', 'Product Buyer', '[fbscore] [stars] [verified] [subscription]', 'product', 'productbuyer', 1)");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default subscription roles</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_user");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_user (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`subscriptionid` INT(10) NOT NULL default '1',
		`user_id` INT(100) NOT NULL default '-1',
		`paymethod` ENUM('account','bank','visa','amex','mc','disc','paypal','check','stormpay','cashu','moneybookers') NOT NULL default 'account',
		`startdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`renewdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`autopayment` INT(1) NOT NULL default '1',
		`active` ENUM('yes','no','cancelled') NOT NULL default 'no',
		`cancelled` INT(1) NOT NULL default '0',
		`migrateto` INT(10) NOT NULL default '0',
		`migratelogic` ENUM('none','waived','unpaid','paid') NOT NULL default 'none',
		`recurring` INT(1) NOT NULL default '0',
		`invoiceid` INT(10) NOT NULL default '0',
		`roleid` INT(5) NOT NULL default '-1',
		PRIMARY KEY  (`id`),
		INDEX ( `subscriptionid` ),
		INDEX ( `user_id` ),
		INDEX ( `paymethod` ),
		INDEX ( `active` ),
		INDEX ( `migratelogic` ),
		INDEX ( `invoiceid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription_user</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_user_exempt");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_user_exempt (
		`exemptid` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`accessname` VARCHAR(250) NOT NULL default '',
		`value` VARCHAR(250) NOT NULL default '',
		`exemptfrom` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`exemptto` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`comments` MEDIUMTEXT,
		`invoiceid` INT(10) NOT NULL default '0',
		`active` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`exemptid`),
		INDEX ( `user_id` ),
		INDEX ( `accessname` ),
		INDEX ( `value` ),
		INDEX ( `invoiceid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription_user_exempt</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "taxes");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "taxes (
		`taxid` INT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		`taxlabel` VARCHAR( 255 ) NOT NULL default '',
		`state` VARCHAR( 20 ) NOT NULL default '',
		`countryname` VARCHAR( 50 ) NOT NULL default '',
		`countryid` INT( 10 ) NOT NULL default '0',
		`city` VARCHAR( 255 ) NOT NULL default '',
		`amount` VARCHAR( 10 ) NOT NULL default '0.0',
		`invoicetypes` MEDIUMTEXT,
		`entirecountry` INT( 1 ) NOT NULL default '0',
		PRIMARY KEY ( `taxid` ) ,
		KEY `taxlabel` ( `taxlabel` ) ,
		KEY `state` ( `state` ) ,
		KEY `countryname` ( `countryname` ) ,
		KEY `countryid` ( `countryid` ) ,
		KEY `city` ( `city` ) ,
		KEY `amount` ( `amount` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "taxes</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "templates");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "templates (
		`tid` INT(50) NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(250) NOT NULL default '',
		`description` VARCHAR(250) NOT NULL default '',
		`original` MEDIUMTEXT,
		`content` MEDIUMTEXT,
		`type` ENUM('variable','cssclient','cssadmin','csswysiwyg','csstabs','csscommon') NOT NULL default 'variable',
		`status` INT(1) NOT NULL default '0',
		`createdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`author` VARCHAR(50) NOT NULL default '',
		`request` VARCHAR(250) NOT NULL default '',
		`version` VARCHAR(10) NOT NULL default '1.0',
		`isupdated` INT(1) NOT NULL default '0',
		`updatedate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`styleid` INT(5) NOT NULL default '1',
		`product` VARCHAR(250) NOT NULL default 'ilance',
		`sort` INT(10) NOT NULL default '100',
		PRIMARY KEY  (`tid`),
		INDEX ( `name` ),
		INDEX ( `type` ),
		INDEX ( `styleid` ),
		INDEX ( `product` ),
		INDEX ( `sort` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "templates</li>";
    	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "timezones");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "timezones (
		`timezoneid` VARCHAR(10) NOT NULL,
		`timezone` VARCHAR(200) NOT NULL default '',
		`sort` INT(5) NOT NULL default '0',
		PRIMARY KEY (`timezoneid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "timezones</li>";
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-12, '(UTC -12:00) Baker Island Time', 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-11, '(UTC -11:00) Niue Time, Samoa Standard Time', 2)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-10, '(UTC -10:00) Hawaii-Aleutian Standard Time', 3)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-9.5, '(UTC -9:30) Marquesas Islands Time', 4)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-9, '(UTC -9:00) Alaska Standard Time', 5)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-8, '(UTC -8:00) Pacific Standard Time', 6)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-7, '(UTC -7:00) Mountain Standard Time', 7)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-6, '(UTC -6:00) Central Standard Time', 8)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-5, '(UTC -5:00) Eastern Standard Time', 9)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-4, '(UTC -4:00) Atlantic Standard Time', 10)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-3.5, '(UTC -3:30) Newfoundland Standard Time', 11)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-3, '(UTC -3:00) Amazon Standard Time', 12)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-2, '(UTC -2:00) Fernando de Noronha Time, South Georgia Time', 13)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-1, '(UTC -1:00) Azores Standard Time, Eastern Greenland Time', 14)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (0, '(UTC) Western European Time, Greenwich Mean Time', 15)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (1, '(UTC +1:00) Central European Time, West African Time', 16)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (2, '(UTC +2:00) Eastern European Time, Central African Time', 17)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (3, '(UTC +3:00) Moscow Standard Time, Eastern African Time', 18)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (3.5, '(UTC +3:30) Iran Standard Time', 19)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (4, '(UTC +4:00) Gulf Standard Time, Samara Standard Time', 20)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (4.5, '(UTC +4:30) Afghanistan Time', 21)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (5, '(UTC +5:00) Pakistan Standard Time', 22)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (5.5, '(UTC +5:30) Indian Standard Time, Sri Lanka Time', 23)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (5.75, '(UTC +5:45) Nepal Time', 24)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (6, '(UTC +6:00) Bangladesh Time, Bhutan Time', 25)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (6.5, '(UTC +6:30) Cocos Islands Time, Myanmar Time', 26)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (7, '(UTC +7:00) Indochina Time, Krasnoyarsk Standard Time', 27)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (8, '(UTC +8:00) Chinese Standard Time', 28)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (8.75, '(UTC +8:45) Southeastern Western Australia Standard Time', 29)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (9, '(UTC +9:00) Japan Standard Time, Korea Standard Time', 30)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (9.5, '(UTC +9:30) Australian Central Standard Time', 31)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (10, '(UTC +10:00) Australian Eastern Standard Time', 32)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (10.5, '(UTC +10:30) Lord Howe Standard Time', 33)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (11, '(UTC +11:00) Solomon Island Time, Magadan Standard Time', 34)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (11.5, '(UTC +11:30) Norfolk Island Time', 35)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (12, '(UTC +12:00) New Zealand Time, Fiji Time', 36)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (12.75, '(UTC +12:45) Chatham Islands Time', 37)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (13, '(UTC +13:00) Tonga Time, Phoenix Islands Time', 38)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (14, '(UTC +14:00) Line Island Time', 39)");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default timezones</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "users");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "users (
		`user_id` INT(100) NOT NULL AUTO_INCREMENT,
		`ipaddress` VARCHAR(25) NOT NULL default '',
		`iprestrict` INT(1) NOT NULL default '0',
		`username` VARCHAR(50) NOT NULL default '',
		`password` VARCHAR(32) NOT NULL default '',
		`salt` VARCHAR(5) NOT NULL default '',
		`secretquestion` VARCHAR(200) NOT NULL default '',
		`secretanswer` VARCHAR(32) NOT NULL default '',
		`email` VARCHAR(60) NOT NULL default '',
		`first_name` VARCHAR(100) NOT NULL default '',
		`last_name` VARCHAR(100) NOT NULL default '',
		`address` VARCHAR(200) NOT NULL default '',
		`address2` VARCHAR(200) default NULL,
		`city` VARCHAR(100) NOT NULL default '',
		`state` VARCHAR(100) NOT NULL default '',
		`zip_code` VARCHAR(10) NOT NULL default '',
		`phone` VARCHAR(20) NOT NULL default '',
		`country` INT(10) NOT NULL default '500',
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`subcategories` MEDIUMTEXT,
		`status` ENUM('active','suspended','cancelled','unverified','banned','moderated') NOT NULL default 'active',
		`serviceawards` INT(5) NOT NULL default '0',
		`productawards` INT(5) NOT NULL default '0',
		`servicesold` INT(5) NOT NULL default '0',
		`productsold` INT(5) NOT NULL default '0',
		`rating` DOUBLE NOT NULL default '0.00',
		`score` INT(5) NOT NULL default '0',
		`feedback` DOUBLE NOT NULL default '0',
		`bidstoday` INT(10) NOT NULL default '0',
		`bidsthismonth` INT(10) NOT NULL default '0',
		`auctiondelists` INT(5) NOT NULL default '0',
		`bidretracts` INT(5) NOT NULL default '0',
		`lastseen` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`dob` DATE NOT NULL default '0000-00-00',
		`rid` VARCHAR(10) NOT NULL default '',
		`account_number` VARCHAR(25) NOT NULL default '',
		`available_balance` FLOAT(10,2) NOT NULL default '0.00',
		`total_balance` FLOAT(10,2) NOT NULL default '0.00',
		`income_reported` FLOAT(10,2) NOT NULL default '0.00',
		`income_spent` FLOAT(10,2) NOT NULL default '0.00',
		`startpage` VARCHAR(250) NOT NULL default 'main',
		`styleid` INT(3) NOT NULL,
		`project_distance` INT(1) NOT NULL default '1',
		`currency_calculation` INT(1) NOT NULL default '1',
		`languageid` INT(3) NOT NULL,
		`currencyid` INT(3) NOT NULL,
		`timezoneid` INT(3) NOT NULL,
		`timezone_dst` INT(1) NOT NULL,
		`notifyservices` INT(1) NOT NULL,
		`notifyproducts` INT(1) NOT NULL,
		`notifyservicescats` MEDIUMTEXT,
		`notifyproductscats` MEDIUMTEXT,
		`lastemailservicecats` DATE NOT NULL,
		`lastemailproductcats` DATE NOT NULL,
		`displayprofile` INT(1) NOT NULL,
		`emailnotify` INT(1) NOT NULL,
		`displayfinancials` INT(1) NOT NULL,
		`vatnumber` VARCHAR(250) NOT NULL,
		`regnumber` VARCHAR(250) NOT NULL,
		`dnbnumber` VARCHAR(250) NOT NULL,
		`companyname` VARCHAR(100) NOT NULL,
		`usecompanyname` INT(1) NOT NULL,
		`timeonsite` INT(10) NOT NULL,
		`daysonsite` INT(10) NOT NULL,
		`isadmin` INT(1) NOT NULL default '0',
		`permissions` MEDIUMTEXT,
		`searchoptions` MEDIUMTEXT,
		`rateperhour` FLOAT(10,2) NOT NULL default '0.00',
		`profilevideourl` MEDIUMTEXT,
		`profileintro` MEDIUMTEXT,
		`gender` ENUM('','male','female') NOT NULL default '',
		`freelancing` ENUM('','individual','business') NOT NULL default '',
		`autopayment` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`user_id`),
		INDEX (`username`),
		INDEX (`email`),
		INDEX (`first_name`),
		INDEX (`last_name`),
		INDEX (`zip_code`),
		INDEX (`country`),
		INDEX (`rating`),
		INDEX (`city`),
		INDEX (`state`),
		INDEX (`status`),
		INDEX (`serviceawards`),
		INDEX (`score`),
		INDEX (`gender`),
		INDEX (`freelancing`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "users</li>";
	
	$salt = construct_password_salt(5);
	$password = md5(md5($_SESSION['admin_password']) . $salt);
		
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "users
		(user_id, username, password, salt, email, phone, isadmin, date_added, languageid, currencyid, timezoneid, account_number, styleid, currency_calculation, timezone_dst, notifyservices, notifyproducts, displayprofile, emailnotify, displayfinancials, vatnumber, regnumber, dnbnumber, companyname, usecompanyname, timeonsite, daysonsite)
		VALUES (
		NULL,
		'" . $ilance->db->escape_string($_SESSION['admin_username']) . "',
		'" . $ilance->db->escape_string($password) . "',
		'" . $ilance->db->escape_string($salt) . "',
		'" . $ilance->db->escape_string($_SESSION['admin_email']) . "',
		'1-111-111-1111',
		'1',
		NOW(),
		'1',
		'1',
		'-5',
		'0',
		'1',
		'0',
		'0',
		'0',
		'0',
		'0',
		'1',
		'0',
		'0',
		'0',
		'0',
		'N/A',
		'0',
		'0',
		'0')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default admin user</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "visits");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "visits (
		`vid` INT(100) NOT NULL AUTO_INCREMENT,
		`sesskey` VARCHAR(200) default '',
		`userid` INT(5) default '0',
		`firstdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`lastdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`browser` VARCHAR(200) default '',
		`ipaddress` VARCHAR(50) default '',
		`referrer` MEDIUMTEXT,
		KEY `vid` (`vid`),
		INDEX ( `ipaddress` ),
		INDEX ( `userid` ),
		INDEX ( `sesskey` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "visits</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "watchlist");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "watchlist (
		`watchlistid` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`watching_user_id` INT(10) NOT NULL default '0',
		`watching_project_id` INT(100) NOT NULL default '0',
		`watching_category_id` INT(100) NOT NULL default '0',
		`comment` MEDIUMTEXT,
		`state` ENUM('sprovider','mprovider','buyer','auction','cat','subcat') NOT NULL default 'auction',
		`lowbidnotify` INT(1) NOT NULL default '0',
		`highbidnotify` INT(1) NOT NULL default '0',
		`hourleftnotify` INT(1) NOT NULL default '0',
		`subscribed` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`watchlistid`),
		INDEX ( `user_id` ),
		INDEX ( `watching_user_id` ),
		INDEX ( `watching_project_id` ),
		INDEX ( `watching_category_id` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "watchlist</li>";
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>