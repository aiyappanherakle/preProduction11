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

if (!class_exists('admincp'))
{
	exit;
}

/**
* AdminCP Product Add-On system class to perform the majority install/uninstall
* related product functionality within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class admincp_products extends admincp
{
        /*
        * Function to handle all aspects of installing an official add-on product for the ILance Framework
        *
        * @param       string 	     xml add-on product template data
        * @param       bool          ignore current xml template version
        * @param       boolean       move database phrases to global if they already exist
        * @param       boolean       update email templates in database from uploaded product upgrade xml
        * @param       boolean       after upgrade is completed should we show admin any missing files the addon requires?
        * 
        * @return      bool          Returns true or false on successful installation
        */
        function install($xml = '', $ignoreversion = 0, $movephrases = 0, $updatephrases = 0, $showmissingfiles = 0)
	{
		global $ilance, $myapi, $ilconfig, $phrase, $ilpage;
                
		$data = array();
		
                $xml_encoding = '';
                
		if (MULTIBYTE)
		{
			$xml_encoding = mb_detect_encoding($xml);
		}
                
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
                        
			$result = $ilance->xml->process_addon_xml($data, $xml_encoding);
                        
			// #### product and developer info #####################
			$product = $result['product'];
                        $developer = $result['developer'];
                        
			// #### specific details about product #################
			$versioncheckurl = $product[0][1];
			$url = $product[0][2];
			$version = $product[0][0];
			$minversion = $product[0][3];
			$maxversion = $product[0][4];
			
			// #### minimum version requirements ###################
			if ($ilance->config['ilversion'] < $minversion AND $ignoreversion == 0)
			{
				// addon min. version is no longer supported in this version of ilance
				print_action_failed("The minimum version of the this product <strong>$minversion</strong> does not support this installed version of ILance <strong>".$ilance->config['ilversion']."</strong>.  The operation has aborted due to a version conflict.  Please <a href=\"".$url."\" target=\"_blank\"><strong>contact the vendor: " . $developer . "</strong></a> for an updated version to resolve this issue.<br /><br />", $ilpage['components'].'?cmd=install');
				exit();
			}
			
			// #### maximum version requirements ###################
			if ($ilance->config['ilversion'] > $maxversion AND $ignoreversion == 0)
			{
				// addon max version is no longer supported in this version of ilance
				print_action_failed("The maximum version of the this product <strong>$maxversion</strong> does not support this installed version of ILance <strong>".$ilance->config['ilversion']."</strong>.  The operation has aborted due to a version conflict.  Please <a href=\"".$url."\" target=\"_blank\"><strong>contact the vendor: " . $developer . "</strong></a> for an updated version to resolve this issue.<br /><br />", $ilpage['components'].'?cmd=install');
				exit();
			}
			
			// #### addon data #####################################
			$modulearray = $result['modulearray'];
			$modulegrouparray = $result['modulegroup'];
                        
                        // #### addon product name #############################
                        $productname = $modulearray[0][0];
                        
			// #### addon configuration ############################
			$configgroup = $result['configgroup'];
			$settings = $result['setting'];
			
			// #### addon phrase groups and phrases ################
			$phrasegroups = $result['phrasegroup'];
			$phrasearray = $result['phrasearray'];
                        
                        // #### automation tasks and scheduled events ##########
                        $taskgroups = $result['taskgroup'];
			$taskarray = $result['taskarray'];
                        
			// #### email groups ###################################
                        $emailgroups = $result['emailgroup'];
			
			// #### css elements ###################################
			$cssgroups = $result['cssgroup'];
                        
			// #### addon install and uninstall code tracking ######
			$installcode = isset($result['installcode'])   ? $result['installcode']   : '';
			$uninstallcode = isset($result['uninstallcode']) ? $result['uninstallcode'] : '';
                        $filestructure = isset($result['filestructure']) ? $result['filestructure'] : '';
			
			// #### init some temp vars ############################
                        $filesarray = array();
                        $filealert = '';
			
                        if (!empty($filestructure))
                        {
                                $filesarray = $filestructure;
                                $filestructure = serialize($filestructure);
                        }
                        
                        if ($showmissingfiles AND is_array($filesarray) AND count($filesarray) > 0)
                        {
                                foreach ($filesarray AS $key => $files)
                                {
                                        // $files[0] = md5
                                        // $files[1] = filename
                                        $files[1] = preg_replace("/%functions%/si", DIR_FUNCT_NAME, $files[1]);
                                        $files[1] = preg_replace("/%admincp%/si", DIR_ADMIN_NAME, $files[1]);
                                        $files[1] = preg_replace("/%addons%/si", DIR_ADMIN_ADDONS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%core%/si", DIR_CORE_NAME, $files[1]);
                                        $files[1] = preg_replace("/%cron%/si", DIR_CRON_NAME, $files[1]);
                                        $files[1] = preg_replace("/%cache%/si", DIR_TMP_NAME, $files[1]);
                                        $files[1] = preg_replace("/%api%/si", DIR_API_NAME, $files[1]);
                                        $files[1] = preg_replace("/%xml%/si", DIR_XML_NAME, $files[1]);
                                        $files[1] = preg_replace("/%uploads%/si", DIR_UPLOADS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%attachments%/si", DIR_ATTACHMENTS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%fonts%/si", DIR_FONTS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%sounds%/si", DIR_SOUNDS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%livebid%/si", DIR_LIVEBID_NAME, $files[1]);
                                        
                                        $filealert .= (!file_exists(DIR_SERVER_ROOT . $files[1])) ? '<div>' . DIR_SERVER_ROOT . '<strong>' . $files[1] . '</strong> <span style="color:red">not found</span></div>' : '';
                                }
                        }
                        
			$modcount = count($modulearray);
			if ($modcount > 0)
			{
				for ($i = 0; $i < $modcount; $i++)
				{
					// check if addon group already exists
					$sql = $ilance->db->query("
                                                SELECT modulegroup
                                                FROM " . DB_PREFIX . "modules_group
                                                WHERE modulegroup = '" . $ilance->db->escape_string($modulearray[$i][0]) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						print_action_failed("It appears <strong>" . $modulegrouparray[0][1] . "</strong> has already been installed. Please select an addon not installed or uninstall the existing addon before attempting to re-install.", $ilpage['components'].'?cmd=install');
						exit();
					}
					
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "modules
						(id, modulegroup, parentkey, tab, template, subcmd, parentid, sort)
						VALUES(
						NULL,
						'" . $ilance->db->escape_string($modulearray[$i][0]) . "',
						'" . $ilance->db->escape_string($modulearray[$i][5]) . "',
						'" . $ilance->db->escape_string($modulearray[$i][1]) . "',
						'" . $ilance->db->escape_string($modulearray[$i][6]) . "',
						'" . $ilance->db->escape_string($modulearray[$i][2]) . "',
						'" . intval($modulearray[$i][3]) . "',
						'" . intval($modulearray[$i][4]) . "')
					");
					
					$installed = 1;
				}
				
				// #### determine if the product data was installed
				if (isset($installed) AND $installed)
				{
					$notice = '<div style="padding-bottom:6px"><strong>' . $modulegrouparray[0][1] . '</strong> was installed to your admin control panel.  To remove this product, please use the uninstall manager.</div>';
                                        
					if (eval($installcode) === false)
					{
						// install code failed (maybe none exists)
						// space reserved for future error capture
					}
						
					// #### install module group ###########
					$ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "modules_group
                                                (id, modulegroup, modulename, folder, configtable, installcode, uninstallcode, version, versioncheckurl, url, developer, filestructure, installdate)
                                                VALUES(
                                                NULL,
                                                '" . $ilance->db->escape_string($modulegrouparray[0][0]) . "',
                                                '" . $ilance->db->escape_string($modulegrouparray[0][1]) . "',
                                                '" . $ilance->db->escape_string($modulegrouparray[0][2]) . "',
                                                '" . $ilance->db->escape_string($modulegrouparray[0][3]) . "',
                                                '" . $ilance->db->escape_string($installcode) . "',
                                                '" . $ilance->db->escape_string($uninstallcode) . "',
                                                '" . $ilance->db->escape_string($version) . "',
                                                '" . $ilance->db->escape_string($versioncheckurl) . "',
                                                '" . $ilance->db->escape_string($url) . "',
                                                '" . $ilance->db->escape_string($developer) . "',
                                                '" . $ilance->db->escape_string($filestructure) . "',
                                                '" . DATETIME24H . "')
                                        ", 0, null, __FILE__, __LINE__);
				    
					// #### install module configuration settings
					$settingscount = count($settings);
					if ($settingscount > 0)
					{
						for ($i = 0; $i < $settingscount; $i++)
						{
							if (!empty($settings[$i][2]) AND $settings[$i][4] != '')
							{
								$ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . trim($settings[$i][1])."
                                                                        (id, name, comment, description, value, inputtype, sort)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . $ilance->db->escape_string($settings[$i][2]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][7]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][3]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][4]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][5]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][6]) . "')
                                                                ", 0, null, __FILE__, __LINE__);	
							}
						}
					}
					
					// #### install any scheduled tasks ####
					$taskgroupcount = count($taskgroups);
					if ($taskgroupcount > 0)
					{
						for ($i = 0; $i < $taskgroupcount; $i++)
						{
							// varname, filename, active, loglevel, productname
							if (!empty($taskgroups[$i][0]) AND !empty($taskgroups[$i][1]))
							{
								$taskarraycount = count($taskarray);
								if ($taskarraycount > 0)
								{
									for ($j = 0; $j < $taskarraycount; $j++)
									{
										if ($taskgroups[$i][0] == $taskarray[$j][0])
										{
											// varname, weekday, day, hour, minute
											if (!empty($taskarray[$j][0]) AND !empty($taskarray[$j][1]) AND !empty($taskarray[$j][2]) AND isset($taskarray[$j][3]) AND isset($taskarray[$j][4]))
											{
												$cron['varname'] = $taskgroups[$i][0];
												$cron['filename'] = $taskgroups[$i][1];
												$cron['active'] = $taskgroups[$i][2];
												$cron['loglevel'] = $taskgroups[$i][3];
												$cron['product'] = $taskgroups[$i][4];
												$cron['weekday'] = intval($taskarray[$j][1]);
												$cron['day'] = intval($taskarray[$j][2]);
												$cron['hour'] = intval($taskarray[$j][3]);                                                                                
												$cron['minute'] = explode(',', preg_replace('#[^0-9,-]#i', '', $taskarray[$j][4]));                                                                                
												if (count($cron['minute']) == 0)
												{
													$cron['minute'] = array(0);
												}
												else
												{
													$cron['minute'] = array_map('intval', $cron['minute']);
												}
												
												$ilance->db->query("
													INSERT INTO " . DB_PREFIX . "cron
													(weekday, day, hour, minute, filename, loglevel, active, varname, product)
													VALUES (
													'" . $cron['weekday'] . "',
													'" . $cron['day'] . "',
													'" . $cron['hour'] . "',
													'" . $ilance->db->escape_string(serialize($cron['minute'])) . "',
													'" . $ilance->db->escape_string($cron['filename']) . "',
													'" . intval($cron['loglevel']) . "',
													'" . intval($cron['active']) . "',
													'" . $ilance->db->escape_string($cron['varname']) . "',
													'" . $ilance->db->escape_string($cron['product']) . "')
												");
											}
										}
									}
								}        
							}
						}
					}
					
					// #### install email templates ########
					$emailgroupcount = count($emailgroups);
					if ($emailgroupcount > 0)
					{
						for ($i = 0; $i < $emailgroupcount; $i++)
						{
							// varname, name, subject, type, body
							if (!empty($emailgroups[$i][0]) AND !empty($emailgroups[$i][1]) AND !empty($emailgroups[$i][2]) AND !empty($emailgroups[$i][3]) AND !empty($emailgroups[$i][4]))
							{
								$query2 = $ilance->db->query("
									SELECT languagecode
									FROM " . DB_PREFIX . "language
								");
								if ($ilance->db->num_rows($query2) > 0)
								{
									while ($row = $ilance->db->fetch_array($query2))
									{
										$lfn1 = 'subject_' . mb_substr($row['languagecode'], 0, 3);
										$lfn2 = 'message_' . mb_substr($row['languagecode'], 0, 3);
										
										if ($ilance->db->num_rows($ilance->db->query("SELECT * FROM " . DB_PREFIX . "email WHERE name = '" . $ilance->db->escape_string($emailgroups[$i][1]) . "' LIMIT 1")) == 0)
										{
											// insert new email template
											$ilance->db->query("
												INSERT INTO " . DB_PREFIX . "email
												(`name`)
												VALUES ('" . $ilance->db->escape_string($emailgroups[$i][1]) . "')
											");
														    
											$ilance->db->query("
												UPDATE " . DB_PREFIX . "email 
												SET `subject_original` = '" . $ilance->db->escape_string($emailgroups[$i][2]) . "',
												`message_original` = '" . $ilance->db->escape_string($emailgroups[$i][4]) . "',
												`" . $lfn1 . "` = '" . $ilance->db->escape_string($emailgroups[$i][2]) . "',
												`" . $lfn2 . "` = '" . $ilance->db->escape_string($emailgroups[$i][4]) . "',
												`type` = '" . $ilance->db->escape_string($emailgroups[$i][3]) . "',
												`varname` = '" . $ilance->db->escape_string(trim($emailgroups[$i][0])) . "',
												`product` = '" . $ilance->db->escape_string($productname) . "'
												WHERE `name` = '" . $ilance->db->escape_string($emailgroups[$i][1]) . "'
												LIMIT 1
											");
										}
										else
										{
											// replace or update email template
											$ilance->db->query("
												UPDATE " . DB_PREFIX . "email 
												SET `subject_original` = '" . $ilance->db->escape_string($emailgroups[$i][2]) . "',
												`message_original` = '" . $ilance->db->escape_string($emailgroups[$i][4]) . "',
												`" . $lfn1 . "` = '" . $ilance->db->escape_string($emailgroups[$i][2]) . "',
												`" . $lfn2 . "` = '" . $ilance->db->escape_string($emailgroups[$i][4]) . "',
												`type` = '" . $ilance->db->escape_string($emailgroups[$i][3]) . "',
												`varname` = '" . $ilance->db->escape_string(trim($emailgroups[$i][0])) . "',
												`product` = '" . $ilance->db->escape_string($productname) . "'
												WHERE `name` = '" . $ilance->db->escape_string($emailgroups[$i][1]) . "'
												LIMIT 1
											");
										}
									}
								}
							}
						}
					}
					
					// #### install css templates ##########
					$cssgroupcount = count($cssgroups);
					if ($cssgroupcount > 0)
					{
						for ($i = 0; $i < $cssgroupcount; $i++)
						{                                        
							// 0 csselement, 1 elementdescription, 2 csstype, 3 cssstatus, 4 cssauthor, 5 styleids, 6 csscontent, 7 csssort
							if (!empty($cssgroups[$i][0]) AND !empty($cssgroups[$i][2]) AND !empty($cssgroups[$i][3]) AND !empty($cssgroups[$i][5]) AND !empty($cssgroups[$i][6]))
							{
								if ($ilance->db->num_rows($ilance->db->query("SELECT * FROM " . DB_PREFIX . "templates WHERE name = '" . $ilance->db->escape_string($cssgroups[$i][0]) . "' LIMIT 1")) == 0)
								{
									// #### multiple style ids
									if (strlen($cssgroups[$i][5]) > 1)
									{
										$styletemp = explode(',', $cssgroups[$i][5]);
										foreach ($styletemp AS $cssstyleid)
										{
											if ($cssstyleid > 0)
											{
												$ilance->GPC['css']['background'] = '';
												$ilance->GPC['css']['color'] = '';
												$ilance->GPC['css']['font']['style'] = '';
												$ilance->GPC['css']['font']['size'] = '';
												$ilance->GPC['css']['font']['family'] = '';
												$ilance->GPC['css']['EXTRA'] = trim($cssgroups[$i][6]);
												$ilance->GPC['css']['LINK_NORMAL']['background'] = '';
												$ilance->GPC['css']['LINK_NORMAL']['color'] = '';
												$ilance->GPC['css']['LINK_NORMAL']['text-decoration'] = '';
												$ilance->GPC['css']['LINK_VISITED']['background'] = '';
												$ilance->GPC['css']['LINK_VISITED']['color'] = '';
												$ilance->GPC['css']['LINK_VISITED']['text-decoration'] = '';
												$ilance->GPC['css']['LINK_HOVER']['background'] = '';
												$ilance->GPC['css']['LINK_HOVER']['color'] = '';
												$ilance->GPC['css']['LINK_HOVER']['text-decoration'] = '';
												
												if (isset($ilance->GPC['css']['EXTRA']) AND !empty($ilance->GPC['css']['EXTRA']))
												{
													$extra64 = base64_encode($ilance->GPC['css']['EXTRA']);
													$ilance->GPC['css']['EXTRA'] = $extra64;				
												}
												
												$cssoriginal = serialize($ilance->GPC['css']);
												$csscontent = $cssoriginal;
												
												// #### insert new css template
												$ilance->db->query("
													INSERT INTO " . DB_PREFIX . "templates
													(tid, name, description, original, content, type, status, createdate, author, styleid, product, sort)
													VALUES (
													NULL,
													'" . $ilance->db->escape_string($cssgroups[$i][0]) . "',
													'" . $ilance->db->escape_string($cssgroups[$i][1]) . "',
													'" . $ilance->db->escape_string($cssoriginal) . "',
													'" . $ilance->db->escape_string($csscontent) . "',
													'" . $ilance->db->escape_string($cssgroups[$i][2]) . "',
													'" . intval($cssgroups[$i][3]) . "',
													'" . DATETIME24H . "',
													'" . $ilance->db->escape_string($cssgroups[$i][4]) . "',
													'" . intval($cssstyleid) . "',
													'" . $ilance->db->escape_string($productname) . "',
													'" . intval($cssgroups[$i][7]) . "')
												");	
											}
										}
									}
									
									// #### single style id
									else
									{
										$ilance->GPC['css']['background'] = '';
										$ilance->GPC['css']['color'] = '';
										$ilance->GPC['css']['font']['style'] = '';
										$ilance->GPC['css']['font']['size'] = '';
										$ilance->GPC['css']['font']['family'] = '';
										$ilance->GPC['css']['EXTRA'] = trim($cssgroups[$i][6]);
										$ilance->GPC['css']['LINK_NORMAL']['background'] = '';
										$ilance->GPC['css']['LINK_NORMAL']['color'] = '';
										$ilance->GPC['css']['LINK_NORMAL']['text-decoration'] = '';
										$ilance->GPC['css']['LINK_VISITED']['background'] = '';
										$ilance->GPC['css']['LINK_VISITED']['color'] = '';
										$ilance->GPC['css']['LINK_VISITED']['text-decoration'] = '';
										$ilance->GPC['css']['LINK_HOVER']['background'] = '';
										$ilance->GPC['css']['LINK_HOVER']['color'] = '';
										$ilance->GPC['css']['LINK_HOVER']['text-decoration'] = '';
										
										if (isset($ilance->GPC['css']['EXTRA']) AND !empty($ilance->GPC['css']['EXTRA']))
										{
											$extra64 = base64_encode($ilance->GPC['css']['EXTRA']);
											$ilance->GPC['css']['EXTRA'] = $extra64;				
										}
										
										$cssoriginal = serialize($ilance->GPC['css']);
										$csscontent = $cssoriginal;
										$cssstyleid  = $cssgroups[$i][5];
										
										// #### insert new css template
										$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "templates
											(tid, name, description, original, content, type, status, createdate, author, styleid, product, sort)
											VALUES (
											NULL,
											'" . $ilance->db->escape_string($cssgroups[$i][0]) . "',
											'" . $ilance->db->escape_string($cssgroups[$i][1]) . "',
											'" . $ilance->db->escape_string($cssoriginal) . "',
											'" . $ilance->db->escape_string($csscontent) . "',
											'" . $ilance->db->escape_string($cssgroups[$i][2]) . "',
											'" . intval($cssgroups[$i][3]) . "',
											'" . DATETIME24H . "',
											'" . $ilance->db->escape_string($cssgroups[$i][4]) . "',
											'" . intval($cssstyleid) . "',
											'" . $ilance->db->escape_string($productname) . "',
											'" . intval($cssgroups[$i][7]) . "')
										");	
									}
								}
							}
						}
					}
					
					// #### install phrase groups ##########
					$phrasegroupcount = count($phrasegroups);
					if ($phrasegroupcount > 0)
					{
						for ($i = 0; $i < $phrasegroupcount; $i++)
						{
							// check if phrase group already exists
							$sql = $ilance->db->query("
                                                                SELECT description
                                                                FROM " . DB_PREFIX . "language_phrasegroups
                                                                WHERE groupname = '" . $ilance->db->escape_string($phrasegroups[$i][0]) . "'
									AND product = '" . $ilance->db->escape_string($productname) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql) > 0)
							{
								$pres = $ilance->db->fetch_array($sql);
								
								// remove old phrase group for this addon
								$ilance->db->query("
                                                                        DELETE FROM " . DB_PREFIX . "language_phrasegroups
                                                                        WHERE groupname = '" . $ilance->db->escape_string($phrasegroups[$i][0]) . "'
										AND product = '" . $ilance->db->escape_string($productname) . "'
                                                                        LIMIT 1
                                                                ", 0, null, __FILE__, __LINE__);
								
								// remove old phrases for this addon
								$ilance->db->query("
                                                                        DELETE FROM " . DB_PREFIX . "language_phrases
                                                                        WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroups[$i][0]) . "'
                                                                ", 0, null, __FILE__, __LINE__);
								
								// create new phrase group
								if (!empty($phrasegroups[$i][0]) AND !empty($phrasegroups[$i][1]))
								{
									$ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "language_phrasegroups
                                                                                (groupname, description, product)
                                                                                VALUES(
                                                                                '" . $ilance->db->escape_string($phrasegroups[$i][0]) . "',
                                                                                '" . $ilance->db->escape_string($phrasegroups[$i][1]) . "',
										'" . $ilance->db->escape_string($productname) . "')
                                                                        ", 0, null, __FILE__, __LINE__);
								}
							}
							else
							{
								// create new phrase group
								if (!empty($phrasegroups[$i][0]) AND !empty($phrasegroups[$i][1]))
								{
									$ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "language_phrasegroups
                                                                                (groupname, description, product)
                                                                                VALUES(
                                                                                '" . $ilance->db->escape_string($phrasegroups[$i][0]) . "',
                                                                                '" . $ilance->db->escape_string($phrasegroups[$i][1]) . "',
										'" . $ilance->db->escape_string($productname) . "')
                                                                        ", 0, null, __FILE__, __LINE__);
								}
							}
						}
					}
                                        
					// #### install phrases ################
					$phrasearraycount = count($phrasearray);
					if ($phrasearraycount > 0)
					{
						for ($i = 0; $i < $phrasearraycount; $i++)
						{
                                                        if (!empty($phrasearray[$i][2]))
                                                        {
                                                                $ids = $ids2 = $val = '';
                                                                
                                                                $langs = $ilance->db->query("
                                                                        SELECT languagecode
                                                                        FROM " . DB_PREFIX . "language
                                                                ", 0, null, __FILE__, __LINE__);
                                                                while ($langres = $ilance->db->fetch_array($langs))
                                                                {
                                                                       $ids .= "text_" . mb_substr($langres['languagecode'], 0, 3) . ", ";
                                                                       $val .= "'" . $ilance->db->escape_string($phrasearray[$i][2]) . "', ";
                                                                       $ids2 .= " text_" . mb_substr($langres['languagecode'], 0, 3) . " = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "', ";
                                                                }
                                                        
                                                                // insert new phrase
                                                                if (!empty($phrasearray[$i][1]) AND !empty($phrasearray[$i][2]))
                                                                {
                                                                        // to avoid addons that contain the same phrases or varnames
                                                                        // within the main ilance product, we'll skip insert if exists
                                                                        $exists = $ilance->db->query("
                                                                                SELECT phraseid, phrasegroup
                                                                                FROM " . DB_PREFIX . "language_phrases
                                                                                WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        if ($ilance->db->num_rows($exists) > 0)
                                                                        {
                                                                                $resexists = $ilance->db->fetch_array($exists);
                                                                                
                                                                                if ($movephrases)
                                                                                {
                                                                                        $ilance->db->query("
                                                                                                UPDATE " . DB_PREFIX . "language_phrases
                                                                                                SET phrasegroup = 'main',
                                                                                                ismoved = '1'
                                                                                                WHERE phraseid = '" . $resexists['phraseid'] . "'
                                                                                                        AND phrasegroup != 'main'
                                                                                        ");
                                                                                }
                                                                                
                                                                                if ($updatephrases)
                                                                                {
                                                                                        $ilance->db->query("
                                                                                                UPDATE " . DB_PREFIX . "language_phrases
                                                                                                SET $ids2
                                                                                                isupdated = '1'
                                                                                                WHERE phraseid = '" . $resexists['phraseid'] . "'
                                                                                        ", 0, null, __FILE__, __LINE__);                    
                                                                                }
                                                                        }
                                                                        else
                                                                        {
                                                                                $ilance->db->query("
                                                                                        INSERT INTO " . DB_PREFIX . "language_phrases
                                                                                        (phraseid, phrasegroup, varname, text_original, $ids isupdated)
                                                                                        VALUES(
                                                                                        NULL,
                                                                                        '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
                                                                                        '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
                                                                                        '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
                                                                                        $val
                                                                                        '0')
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                        }
                                                                }        
                                                        }
						}
					}
                                        
					// #### rebuild our language cache #####
					$this->rebuild_language_cache();
                                        
                                        if (!empty($filealert))
                                        {
                                                $notice .= $filealert;
                                                $notice .= '<div style="padding-top:6px">This first step in installing a product to your marketplace is completed.  <strong>Do not forget</strong> to upload the missing files above or the product may fail to work as designed.</div>';
                                        }
                                        
					print_action_success($notice, $ilpage['components']);
					exit();
				}                	
			}
			else 
			{
				print_action_failed("We're sorry.  There was nothing to install (or is not a valid ilance product addon).  Please fix the problem and retry your action.", $ilpage['components'].'?cmd=install');
				exit();
			}
		}
		else
		{
			$error_string = xml_error_string($error_code);
                        
			print_action_failed("We're sorry.  There was an error with the formatting of the xml file [$error_string].  Please fix the problem and retry your action.", $ilpage['components'].'?cmd=install');
			exit();
		}
	}
        
        /*
        * Function to handle all aspects of un-installing an official add-on product for the ILance Framework
        *
        * @param       string 	     add-on product name
        * @param       boolean       after uninstall is completed should we show admin any files needed to remove to complete uninstall?
        * 
        * @return      bool          Returns true or false on successful un-installation
        */
        function uninstall($addon = '', $showfilestructure = 0)
	{
		global $ilance, $ilconfig, $myapi;
                
		$sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "modules_group
                        WHERE modulegroup = '" . $ilance->db->escape_string($ilance->GPC['modulegroup']) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$rgroup = $ilance->db->fetch_array($sql);
                        
			$uninstallcode = $rgroup['uninstallcode'];
			if (eval($uninstallcode) === false)
			{
				// uninstall code failed (maybe none exist)
				// space reserved for future error capture
			}
			
			if (!empty($ilance->GPC['modulegroup']))
			{
				$ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "modules_group
                                        WHERE modulegroup = '" . $ilance->db->escape_string($ilance->GPC['modulegroup']) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
			    
				$ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "modules
                                        WHERE modulegroup = '" . $ilance->db->escape_string($ilance->GPC['modulegroup']) . "'
                                ", 0, null, __FILE__, __LINE__);
			}
			   
                        // #### remove email templates for this product addon
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "email
                                WHERE product = '" . $ilance->db->escape_string($addon) . "'
                        ", 0, null, __FILE__, __LINE__);
			
			// #### remove css templates for this product addon
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "templates
                                WHERE product = '" . $ilance->db->escape_string($addon) . "'
                        ", 0, null, __FILE__, __LINE__);
                            
                        // #### remove automated tasks for this product addon
                        $ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "cron
                                WHERE product = '" . $ilance->db->escape_string($addon) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
			// #### remove language phrase groups for this product addon
			$sql3 = $ilance->db->query("
                                SELECT groupname
                                FROM " . DB_PREFIX . "language_phrasegroups
                                WHERE groupname = '" . $ilance->db->escape_string($addon) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql3) > 0)
			{
				$pgroup = $ilance->db->fetch_array($sql3);
				
				// #### remove the language phrases for this product addon
				$sql4 = $ilance->db->query("
                                        SELECT phraseid
                                        FROM " . DB_PREFIX . "language_phrases
                                        WHERE phrasegroup = '" . $pgroup['groupname'] . "'
                                ", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql4) > 0)
				{
					$ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "language_phrases
                                                WHERE phrasegroup = '" . $pgroup['groupname'] . "'
                                        ", 0, null, __FILE__, __LINE__);
				}
				
				if (!empty($addon))
				{
					$ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "language_phrasegroups
                                                WHERE groupname = '" . $pgroup['groupname'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
				}
			}
                        
			// ##### rebuild our language cache ####################
			$this->rebuild_language_cache();
                        
			return 1;
		}
		else
		{
			return 0;
		}
	}
        
        /*
        * Function to handle all aspects of upgrading an official add-on product for the ILance Framework
        *
        * @param       string 	     xml add-on product template data
        * @param       boolean       move database phrases to global if they already exist
        * @param       boolean       update phrases in database from uploaded product upgrade xml
        * @param       boolean       update email templates in database from uploaded product upgrade xml
        * @param       boolean       after upgrade is completed should we show admin any missing files the addon requires?
        * 
        * @return      string        Returns HTML formatted response based on the upgrade process
        */
        function upgrade($xml = '', $movephrases = 0, $updatephrases = 0, $updateemails = 0, $showmissingfiles = 0)
	{
                global $ilance, $ilconfig, $ilpage, $myapi, $phrase;
                
                $xml_encoding = '';
                
                if (MULTIBYTE)
                {
                        $xml_encoding = mb_detect_encoding($xml);
                }
                
                if ($xml_encoding == 'ASCII')
                {
                        $xml_encoding = '';
                }
                
                $parser = xml_parser_create($xml_encoding);
                $data = array();
                
                xml_parse_into_struct($parser, $xml, $data);
                $error_code = xml_get_error_code($parser);                
                xml_parser_free($parser);
                if ($error_code == 0)
                {
                        $ilance->xml = construct_object('api.xml');
                        
                        $result = $ilance->xml->process_addon_xml($data, $xml_encoding);
                        
			$modulearray = $result['modulearray'];
                        $productname = $result['modulegroup'][0][0];
                        $upgradecode = isset($result['upgradecode']) ? $result['upgradecode'] : '';
                        $installcode = isset($result['installcode']) ? $result['installcode'] : '';
                        $uninstallcode = isset($result['uninstallcode']) ? $result['uninstallcode'] : '';
                        $filestructure = isset($result['filestructure']) ? $result['filestructure'] : '';
                        if (!empty($filestructure))
                        {
                                $filestructure = serialize($filestructure);
                        }
                        
                        $notice = $movephraseids = '';
                        
                        // #### does this addon exist? #########################
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "modules_group
                                WHERE modulegroup = '" . $ilance->db->escape_string($result['modulegroup'][0][0]) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {                                
                                // #### update upgrade code ####################
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "modules_group
                                        SET installcode = '" . $ilance->db->escape_string($installcode) . "',
                                        uninstallcode = '" . $ilance->db->escape_string($uninstallcode) . "',
                                        filestructure = '" . $ilance->db->escape_string($filestructure) . "',
                                        upgradedate = '" . DATETIME24H . "'
                                        WHERE modulegroup = '" . $ilance->db->escape_string($result['modulegroup'][0][0]) . "'
                                ");
                                
                                $modcount = count($modulearray);
                                if ($modcount > 0)
                                {
                                        for ($i = 0; $i < $modcount; $i++)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "modules
                                                        SET tab = '" . $ilance->db->escape_string($modulearray[$i][1]) . "',
                                                        template = '" . $ilance->db->escape_string($modulearray[$i][6]) . "',
                                                        subcmd = '" . $ilance->db->escape_string($modulearray[$i][2]) . "',
                                                        parentid = '" . intval($modulearray[$i][3]) . "',
                                                        sort = '" . intval($modulearray[$i][4]) . "'
                                                        WHERE modulegroup = '" . $ilance->db->escape_string($modulearray[$i][0]) . "'
                                                                AND parentkey = '" . $ilance->db->escape_string($modulearray[$i][5]) . "'
                                                ");
                                        }
                                }
                                
                                if (eval($upgradecode) === false)
                                {
                                        // upgrade code failed (maybe none exists)
                                        // space reserved for future error capture
                                        DEBUG($result['modulegroup'][0][0] . ' : Upgrade Code Failed!', 'NOTICE');
                                }
                                else
                                {
                                        DEBUG($result['modulegroup'][0][0] . ' : Upgrade Code Success!', 'NOTICE');
                                }
                                
                                // #### HANDLE SETTINGS UPGRADE ################
                                $settings = $result['setting'];                                
                                $settingscount = count($settings);
				
                                if ($settingscount > 0)
                                {
                                        for ($i = 0; $i < $settingscount; $i++)
                                        {
                                                if (!empty($settings[$i][2]) AND $settings[$i][4] != '')
                                                {
                                                        // insert new setting if does not exist
                                                        if ($this->upgrade_settings_scan($settings[$i][2], $settings[$i][1]))
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . $settings[$i][1] . "
                                                                        (id, name, comment, description, value, inputtype, sort)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . $ilance->db->escape_string($settings[$i][2]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][7]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][3]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][4]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][5]) . "',
                                                                        '" . $ilance->db->escape_string($settings[$i][6]) . "')
                                                                ", 0, null, __FILE__, __LINE__);
                                                                
                                                                DEBUG($result['modulegroup'][0][0] . ' : Added New Setting: ' . $settings[$i][2], 'NOTICE');
                                                        }
                                                }
                                        }
                                }
                                
                                // #### HANDLE PHRASES UPGRADE #################
                                $phrasearray = $result['phrasearray'];                                
                                $phrasearraycount = count($phrasearray);
                                if ($phrasearraycount > 0)
                                {
                                        for ($i = 0; $i < $phrasearraycount; $i++)
                                        {
                                                if (isset($phrasearray[$i][2]) AND $phrasearray[$i][2] != '')
                                                {
                                                        $ids = $val = $ids2 = $val2 = '';
                                                        
                                                        $langs = $ilance->db->query("
                                                                SELECT *
                                                                FROM " . DB_PREFIX . "language
                                                        ", 0, null, __FILE__, __LINE__);
                                                        while ($langres = $ilance->db->fetch_array($langs))
                                                        {
                                                               $ids .= "text_" . mb_substr($langres['languagecode'], 0, 3) . ", ";
                                                               $val .= "'" . $ilance->db->escape_string($phrasearray[$i][2]) . "', ";
                                                               $ids2 .= " text_" . mb_substr($langres['languagecode'], 0, 3) . " = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "', ";
                                                        }
                                                
                                                        // insert new phrase
                                                        if (isset($phrasearray[$i][1]) AND isset($phrasearray[$i][2]) AND isset($phrasearray[$i][0]))
                                                        {
                                                                // determine if we have this particular phrase
                                                                $exists = $ilance->db->query("
                                                                        SELECT *
                                                                        FROM " . DB_PREFIX . "language_phrases
                                                                        WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                if ($ilance->db->num_rows($exists) > 0)
                                                                {
                                                                        $resexist = $ilance->db->fetch_array($exists);
                                                                        
                                                                        if ($movephrases)
                                                                        {
                                                                                $ilance->db->query("
                                                                                        UPDATE " . DB_PREFIX . "language_phrases
                                                                                        SET phrasegroup = 'main'
                                                                                        WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
                                                                                                AND phrasegroup != 'main'
                                                                                                AND phrasegroup != '" . $ilance->db->escape_string($phrasearray[$i][0]) . "'
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                        }
                                                                        
                                                                        if ($updatephrases)
                                                                        {
                                                                                $ilance->db->query("
                                                                                        UPDATE " . DB_PREFIX . "language_phrases
                                                                                        SET $ids2
                                                                                        isupdated = '1'
                                                                                        WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
                                                                                ", 0, null, __FILE__, __LINE__);        
                                                                        }
                                                                }
                                                                else
                                                                {
                                                                        $ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "language_phrases
                                                                                (phraseid, phrasegroup, varname, text_original, $ids isupdated)
                                                                                VALUES(
                                                                                NULL,
                                                                                '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
                                                                                '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
                                                                                '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
                                                                                $val
                                                                                '0')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        
                                                                        DEBUG($result['modulegroup'][0][0] . ' : Added New Phrase: ' . $ilance->db->escape_string($phrasearray[$i][2]) . ", Phrase Group: " . $phrasearray[$i][0], 'NOTICE');
                                                                }
                                                        }        
                                                }
                                        }
                                        
                                        // lets rebuild our language cache (usually the javascript file)
                                        $this->rebuild_language_cache();
                                }
                                                        
                                // #### HANDLE NEW EMAIL TEMPLATES #####################
                                $emailgroups = $result['emailgroup'];
                                $emailgroupcount = count($emailgroups);
                                if ($emailgroupcount > 0)
                                {
                                        for ($i = 0; $i < $emailgroupcount; $i++)
                                        {
                                                // varname, name, subject, type, body
                                                if (!empty($emailgroups[$i][0]) AND !empty($emailgroups[$i][1]) AND !empty($emailgroups[$i][2]) AND !empty($emailgroups[$i][3]) AND !empty($emailgroups[$i][4]))
                                                {
                                                        $query2 = $ilance->db->query("
                                                                SELECT languagecode
                                                                FROM " . DB_PREFIX . "language
                                                        ");
                                                        if ($ilance->db->num_rows($query2) > 0)
                                                        {
                                                                while ($row = $ilance->db->fetch_array($query2))
                                                                {
                                                                        $lfn1 = 'subject_' . mb_substr($row['languagecode'], 0, 3);
                                                                        $lfn2 = 'message_' . mb_substr($row['languagecode'], 0, 3);
                                                                        
                                                                        if ($ilance->db->num_rows($ilance->db->query("SELECT * FROM " . DB_PREFIX . "email WHERE name = '" . $ilance->db->escape_string($emailgroups[$i][1]) . "' LIMIT 1")) == 0)
                                                                        {
                                                                                // insert new email template
                                                                                $ilance->db->query("
                                                                                        INSERT INTO " . DB_PREFIX . "email
                                                                                        (`name`)
                                                                                        VALUES ('" . $ilance->db->escape_string($emailgroups[$i][1]) . "')
                                                                                ");
                                                                                                            
                                                                                $ilance->db->query("
                                                                                        UPDATE " . DB_PREFIX . "email 
                                                                                        SET subject_original = '" . $ilance->db->escape_string($emailgroups[$i][2]) . "',
											message_original = '" . $ilance->db->escape_string($emailgroups[$i][4]) . "',
											`" . $lfn1 . "` = '" . $ilance->db->escape_string($emailgroups[$i][2]) . "',
                                                                                        `" . $lfn2 . "` = '" . $ilance->db->escape_string($emailgroups[$i][4]) . "',
                                                                                        `type` = '" . $ilance->db->escape_string($emailgroups[$i][3]) . "',
                                                                                        `varname` = '" . $ilance->db->escape_string(trim($emailgroups[$i][0])) . "',
                                                                                        `product` = '" . $ilance->db->escape_string(trim($productname)) . "'
                                                                                        WHERE `name` = '" . $ilance->db->escape_string($emailgroups[$i][1]) . "'
                                                                                        LIMIT 1
                                                                                ");
                                                                                
                                                                                DEBUG($result['modulegroup'][0][0] . ' : Added New Email Template: ' . $emailgroups[$i][1], 'NOTICE');
                                                                        }
                                                                        else
                                                                        {
                                                                                // email template already exists.. do nothing
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                }
				
				// #### handle upgrade of css templates ########
				$cssgroups = $result['cssgroup'];
				$cssgroupcount = count($cssgroups);
				if ($cssgroupcount > 0)
				{
					for ($i = 0; $i < $cssgroupcount; $i++)
					{                                        
						// 0 csselement, 1 elementdescription, 2 csstype, 3 cssstatus, 4 cssauthor, 5 styleids, 6 csscontent
						if (!empty($cssgroups[$i][0]) AND !empty($cssgroups[$i][2]) AND !empty($cssgroups[$i][3]) AND !empty($cssgroups[$i][5]) AND !empty($cssgroups[$i][6]))
						{
							if ($ilance->db->num_rows($ilance->db->query("SELECT * FROM " . DB_PREFIX . "templates WHERE name = '" . $ilance->db->escape_string($cssgroups[$i][0]) . "' LIMIT 1")) == 0)
							{
								// #### multiple style ids
								if (strlen($cssgroups[$i][5]) > 1)
								{
									$styletemp = explode(',', $cssgroups[$i][5]);
									foreach ($styletemp AS $cssstyleid)
									{
										if ($cssstyleid > 0)
										{
											$ilance->GPC['css']['background'] = '';
											$ilance->GPC['css']['color'] = '';
											$ilance->GPC['css']['font']['style'] = '';
											$ilance->GPC['css']['font']['size'] = '';
											$ilance->GPC['css']['font']['family'] = '';
											$ilance->GPC['css']['EXTRA'] = trim($cssgroups[$i][6]);
											$ilance->GPC['css']['LINK_NORMAL']['background'] = '';
											$ilance->GPC['css']['LINK_NORMAL']['color'] = '';
											$ilance->GPC['css']['LINK_NORMAL']['text-decoration'] = '';
											$ilance->GPC['css']['LINK_VISITED']['background'] = '';
											$ilance->GPC['css']['LINK_VISITED']['color'] = '';
											$ilance->GPC['css']['LINK_VISITED']['text-decoration'] = '';
											$ilance->GPC['css']['LINK_HOVER']['background'] = '';
											$ilance->GPC['css']['LINK_HOVER']['color'] = '';
											$ilance->GPC['css']['LINK_HOVER']['text-decoration'] = '';
											
											if (isset($ilance->GPC['css']['EXTRA']) AND !empty($ilance->GPC['css']['EXTRA']))
											{
												$extra64 = base64_encode($ilance->GPC['css']['EXTRA']);
												$ilance->GPC['css']['EXTRA'] = $extra64;				
											}
											
											$cssoriginal = serialize($ilance->GPC['css']);
											$csscontent = $cssoriginal;
											
											// #### insert new css template
											$ilance->db->query("
												INSERT INTO " . DB_PREFIX . "templates
												(tid, name, description, original, content, type, status, createdate, author, styleid, product, sort)
												VALUES (
												NULL,
												'" . $ilance->db->escape_string($cssgroups[$i][0]) . "',
												'" . $ilance->db->escape_string($cssgroups[$i][1]) . "',
												'" . $ilance->db->escape_string($cssoriginal) . "',
												'" . $ilance->db->escape_string($csscontent) . "',
												'" . $ilance->db->escape_string($cssgroups[$i][2]) . "',
												'" . intval($cssgroups[$i][3]) . "',
												'" . DATETIME24H . "',
												'" . $ilance->db->escape_string($cssgroups[$i][4]) . "',
												'" . intval($cssstyleid) . "',
												'" . $ilance->db->escape_string($productname) . "',
												'" . intval($cssgroups[$i][7]) . "')
											");	
										}
									}
								}
								
								// #### single style id
								else
								{
									$ilance->GPC['css']['background'] = '';
									$ilance->GPC['css']['color'] = '';
									$ilance->GPC['css']['font']['style'] = '';
									$ilance->GPC['css']['font']['size'] = '';
									$ilance->GPC['css']['font']['family'] = '';
									$ilance->GPC['css']['EXTRA'] = trim($cssgroups[$i][6]);
									$ilance->GPC['css']['LINK_NORMAL']['background'] = '';
									$ilance->GPC['css']['LINK_NORMAL']['color'] = '';
									$ilance->GPC['css']['LINK_NORMAL']['text-decoration'] = '';
									$ilance->GPC['css']['LINK_VISITED']['background'] = '';
									$ilance->GPC['css']['LINK_VISITED']['color'] = '';
									$ilance->GPC['css']['LINK_VISITED']['text-decoration'] = '';
									$ilance->GPC['css']['LINK_HOVER']['background'] = '';
									$ilance->GPC['css']['LINK_HOVER']['color'] = '';
									$ilance->GPC['css']['LINK_HOVER']['text-decoration'] = '';
									
									if (isset($ilance->GPC['css']['EXTRA']) AND !empty($ilance->GPC['css']['EXTRA']))
									{
										$extra64 = base64_encode($ilance->GPC['css']['EXTRA']);
										$ilance->GPC['css']['EXTRA'] = $extra64;				
									}
									
									$cssoriginal = serialize($ilance->GPC['css']);
									$csscontent = $cssoriginal;
									$cssstyleid  = $cssgroups[$i][5];
									
									// #### insert new css template
									$ilance->db->query("
										INSERT INTO " . DB_PREFIX . "templates
										(tid, name, description, original, content, type, status, createdate, author, styleid, product, sort)
										VALUES (
										NULL,
										'" . $ilance->db->escape_string($cssgroups[$i][0]) . "',
										'" . $ilance->db->escape_string($cssgroups[$i][1]) . "',
										'" . $ilance->db->escape_string($cssoriginal) . "',
										'" . $ilance->db->escape_string($csscontent) . "',
										'" . $ilance->db->escape_string($cssgroups[$i][2]) . "',
										'" . intval($cssgroups[$i][3]) . "',
										'" . DATETIME24H . "',
										'" . $ilance->db->escape_string($cssgroups[$i][4]) . "',
										'" . intval($cssstyleid) . "',
										'" . $ilance->db->escape_string($productname) . "',
										'" . intval($cssgroups[$i][7]) . "')
									");	
								}
							}
							else
							{
								// css template exists.. do nothing
							}
						}
					}
				}
                                
                                // #### HANDLE NEW SCHEDULED TASKS #####################
                                $taskgroups = $result['taskgroup'];
                                $taskarray = $result['taskarray'];                                
                                $taskgroupcount = count($taskgroups);
                                if ($taskgroupcount > 0)
                                {
                                        for ($i = 0; $i < $taskgroupcount; $i++)
                                        {
                                                // varname, filename, active, loglevel, productname
                                                if (!empty($taskgroups[$i][0]) AND !empty($taskgroups[$i][1]))
                                                {
                                                        $taskarraycount = count($taskarray);
                                                        if ($taskarraycount > 0)
                                                        {
                                                                for ($j = 0; $j < $taskarraycount; $j++)
                                                                {
                                                                        if ($taskgroups[$i][0] == $taskarray[$j][0])
                                                                        {
                                                                                // varname, weekday, day, hour, minute
                                                                                if (!empty($taskarray[$j][0]) AND !empty($taskarray[$j][1]) AND !empty($taskarray[$j][2]) AND isset($taskarray[$j][3]) AND isset($taskarray[$j][4]))
                                                                                {
                                                                                        $cron['varname'] = $taskgroups[$i][0];
                                                                                        $cron['filename'] = $taskgroups[$i][1];
                                                                                        $cron['active'] = $taskgroups[$i][2];
                                                                                        $cron['loglevel'] = $taskgroups[$i][3];
                                                                                        $cron['product'] = $taskgroups[$i][4];
                                                                                        $cron['weekday'] = intval($taskarray[$j][1]);
                                                                                        $cron['day'] = intval($taskarray[$j][2]);
                                                                                        $cron['hour'] = intval($taskarray[$j][3]);                                                                                
                                                                                        $cron['minute'] = explode(',', preg_replace('#[^0-9,-]#i', '', $taskarray[$j][4]));                                                                                
                                                                                        if (count($cron['minute']) == 0)
                                                                                        {
                                                                                                $cron['minute'] = array(0);
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                                $cron['minute'] = array_map('intval', $cron['minute']);
                                                                                        }
                                                                                        
                                                                                        // does this task already exist?
                                                                                        $sql = $ilance->db->query("
                                                                                                SELECT *
                                                                                                FROM " . DB_PREFIX . "cron
                                                                                                WHERE varname = '" . $ilance->db->escape_string($cron['varname']) . "'
                                                                                        ");
                                                                                        if ($ilance->db->num_rows($sql) > 0)
                                                                                        {
                                                                                               // task exists .. do nothing (in case developer modifies own schedules)
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                                // task appears to be new
                                                                                                $ilance->db->query("
                                                                                                        INSERT INTO " . DB_PREFIX . "cron
                                                                                                        (weekday, day, hour, minute, filename, loglevel, active, varname, product)
                                                                                                        VALUES (
                                                                                                        '" . $cron['weekday'] . "',
                                                                                                        '" . $cron['day'] . "',
                                                                                                        '" . $cron['hour'] . "',
                                                                                                        '" . $ilance->db->escape_string(serialize($cron['minute'])) . "',
                                                                                                        '" . $ilance->db->escape_string($cron['filename']) . "',
                                                                                                        '" . intval($cron['loglevel']) . "',
                                                                                                        '" . intval($cron['active']) . "',
                                                                                                        '" . $ilance->db->escape_string($cron['varname']) . "',
                                                                                                        '" . $ilance->db->escape_string($cron['product']) . "')
                                                                                                ");
                                                                                                
                                                                                                DEBUG($result['modulegroup'][0][0] . ' : Added New Scheduled Task: ' . $cron['filename'], 'NOTICE');
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                        }        
                                                }
                                        }
                                }
                                
                                //print_r($GLOBALS['DEBUG']);
                                
                                $notice .= '<div><strong>' . $result['modulegroup'][0][1] . '</strong> was successfully updated to the latest version.  To remove this addon, please use the uninstall manager.</div>';
                                
                                print_action_success($notice, $ilpage['components']);
                                exit();           
                        }
                        else
                        {
                                print_action_failed("We're sorry.  In order to upgrade an addon it must already be installed using the product add-on install manager.  Try installing it then proceed to the upgrade process.", $ilpage['components'].'?cmd=install');
                                exit();     
                        }
                }
                else
                {
                        $error_string = xml_error_string($error_code);                        
                        print_action_failed("We're sorry.  There was an error with the formatting of the xml file [".$error_string."].  Please fix the problem and retry your action.", $ilpage['components'].'?cmd=install');
                        exit();
                }
	}
        
        /*
        * Function to print the add-on product modules in a pulldown menu element.
        *
        * @return      string       HTML representation of the pulldown menu
        */
        function modules_pulldown()
        {
                global $ilance, $myapi, $phrase, $show;
                
                $show['productsavailable'] = false;
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "modules_group
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $show['productsavailable'] = true;
                        
                        $html = '<select name="modulegroup" style="font-family: verdana">';
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                            $html .= '<option value="' . $res['modulegroup'] . '">' . stripslashes($res['modulename']) . ' ' . $res['version'] . '</option>';
                        }
                        $html .= '</select>';
                }
                
                if (isset($html))
                {
                        return $html;
                }
                
                return $phrase['_no_addons_to_uninstall'];
        }
        
        /*
        * Function to determine if a current setting varname exists for a particular add-on being upgraded
        *
        * @param       string 	     add-on setting varname
        * @param       string        add-on database table
        * 
        * @return      bool          Returns true if the new setting should be added
        */
        function upgrade_settings_scan($settingname = '', $dbtable = '')
        {
                global $ilance, $ilconfig, $ilpage, $myapi, $phrase;
                
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . $dbtable . "
                        WHERE name = '" . $ilance->db->escape_string($settingname) . "'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        // setting exists.. do nothing
                        return false;
                }
                else
                {
                        // new setting detected.. add it!
                        return true;        
                }
        }
        
        /*
        * Function to print a list of product addon files residing in the file system and to report if they are found or not found respectively.
        *
        * @param       string 	     add-on product name (example: lancekb)
        * 
        * @return      bool          Returns string
        */
        function print_file_dependencies($product = '')
        {
                global $ilance, $ilconfig, $ilpage, $myapi, $phrase;
                
                $filealert = '';
                
                $sql = $ilance->db->query("
                        SELECT filestructure
                        FROM " . DB_PREFIX . "modules_group
                        WHERE modulegroup = '" . $ilance->db->escape_string($product) . "'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        if (!empty($res['filestructure']) AND is_serialized($res['filestructure']))
                        {
                                $filesarray = unserialize($res['filestructure']);
                                foreach ($filesarray AS $key => $files)
                                {
                                        // $files[0] = md5
                                        // $files[1] = filename
                                        $files[1] = preg_replace("/%functions%/si", DIR_FUNCT_NAME, $files[1]);
                                        $files[1] = preg_replace("/%admincp%/si", DIR_ADMIN_NAME, $files[1]);
                                        $files[1] = preg_replace("/%addons%/si", DIR_ADMIN_ADDONS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%core%/si", DIR_CORE_NAME, $files[1]);
                                        $files[1] = preg_replace("/%cron%/si", DIR_CRON_NAME, $files[1]);
                                        $files[1] = preg_replace("/%cache%/si", DIR_TMP_NAME, $files[1]);
                                        $files[1] = preg_replace("/%api%/si", DIR_API_NAME, $files[1]);
                                        $files[1] = preg_replace("/%xml%/si", DIR_XML_NAME, $files[1]);
                                        $files[1] = preg_replace("/%uploads%/si", DIR_UPLOADS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%attachments%/si", DIR_ATTACHMENTS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%fonts%/si", DIR_FONTS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%sounds%/si", DIR_SOUNDS_NAME, $files[1]);
                                        $files[1] = preg_replace("/%livebid%/si", DIR_LIVEBID_NAME, $files[1]);
                                        
                                        $filealert .= (!file_exists(DIR_SERVER_ROOT . $files[1])) ? '<div>' . DIR_SERVER_ROOT . '<strong>' . $files[1] . '</strong> <span style="color:red">not found</span></div>' : '<div>' . DIR_SERVER_ROOT . '<strong>' . $files[1] . '</strong> <span style="color:blue">found</span></div>';
                                }
                                unset($filesarray);
                                
                                return $filealert;
                        }
                }
                
                return $filealert;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>