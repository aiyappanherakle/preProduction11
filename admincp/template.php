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
	'colorpicker',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');
define('AREA', 'template');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[template]" => $ilcrumbs["$ilpage[template]"]);

$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['template']);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	// #### DOWNLOAD XML STYLE PACKAGE ######################################
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'downloadcss')
	{
		$area_title = $phrase['_exporting_css_styles_to_xml'];
		$page_title = SITE_NAME . ' - ' . $phrase['_exporting_css_styles_to_xml'];
	
		if (isset($ilance->GPC['id']))
		{
			$styleid = intval($ilance->GPC['id']);
		}
		else if (isset($ilance->GPC['styleid']))
		{
			$styleid = intval($ilance->GPC['styleid']);
		}
                else
                {
                        $styleid = 1;
                }
		
		$product = (isset($ilance->GPC['product']) AND !empty($ilance->GPC['product'])) ? $ilance->GPC['product'] : 'ilance';
	
		$query = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "styles
                        WHERE styleid = '" . intval($styleid) . "'
                ");
		if ($ilance->db->num_rows($query) > 0)
		{
			$style = $ilance->db->fetch_array($query, DB_ASSOC);
                        
			$xml_output = '<?xml version="1.0" encoding="' . $ilconfig['template_charset'] . '"?>' . LINEBREAK . LINEBREAK;
                        $xml_output .= "<!--" . LINEBREAK . "This xml document was exported for use with ILance " . $ilance->config['ilversion'] . ".  Do not hand edit this document." . LINEBREAK . "-->" . LINEBREAK . LINEBREAK;
			$xml_output .= "<style name=\"" . stripslashes(un_htmlspecialchars($style['name'])) . "\" ilversion=\"" . $ilance->config['ilversion'] . "\">" . LINEBREAK . LINEBREAK;
                        
			$query2 = $ilance->db->query("
                                SELECT name, description, type, content, product, sort
                                FROM " . DB_PREFIX . "templates
                                WHERE styleid = '" . intval($styleid) . "'
					AND product = '" . $ilance->db->escape_string($product) . "'
                                ORDER BY sort ASC
                        ");
			if ($ilance->db->num_rows($query2) > 0)
			{
				while ($template = $ilance->db->fetch_array($query2, DB_ASSOC))
				{
                                        $xml_output .= "\t<template name=\"" . trim(stripslashes(un_htmlspecialchars($template['name']))) . "\" description=\"" . trim(stripslashes(un_htmlspecialchars($template['description']))) . "\" type=\"" . trim($template['type']) . "\" product=\"" . trim(un_htmlspecialchars($template['product'])) . "\" sort=\"" . intval($template['sort']) . "\"><![CDATA[" . $template['content'] . "]]></template>" . LINEBREAK;
				}
			}
			
			$xml_output .= LINEBREAK . "</style>";
			
			// #### send download as prompt to admin ###############
			$ilance->common->download_file($xml_output, "$product-style-" . VERSIONSTRING . "-styleid-$styleid.xml", "text/plain");
		}
	}
    
	// #### UPLOAD XML STYLES PACKAGE ######################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'uploadcss')
	{
		$area_title = $phrase['_importing_xml_styles_via_xml'];
		$page_title = SITE_NAME . ' - ' . $phrase['_importing_xml_styles_via_xml'];
                
		while(list($key, $value) = each($_FILES)) $GLOBALS["$key"] = $value;
		foreach ($_FILES AS $key => $value)
		{
			$GLOBALS["$key"] = $_FILES["$key"]['tmp_name'];
			foreach ($value AS $ext => $value2)
			{
				$key2 = $key . '_' . $ext;
				$GLOBALS["$key2"] = $value2;
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
			$ilance->xml = construct_object('api.xml');
			
			$result = $ilance->xml->process_style_xml($data, $xml_encoding);
                        
			if ($result['ilversion'] != $ilance->config['ilversion'])
			{
				print_action_failed($phrase['_the_version_of_the_css_package_is_different_than_the_installed_version_of_ilance'] . ' <strong>' . $ilance->config['ilversion'] . '</strong>.  ' . $phrase['_the_operation_has_aborted_due_to_a_version_conflict'], $ilance->GPC['return']);
				exit();
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
                                        '10')
                                ", 0, null, __FILE__, __LINE__);
                                
                                $newstyleid = $ilance->db->insert_id();
                                
                                // set the updated XML as default?
                                if (isset($ilance->GPC['makedefault']) AND $ilance->GPC['makedefault'])
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "configuration
                                                SET value = '" . intval($newstyleid) . "'
                                                WHERE name = 'defaultstyle'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                // move onto updating each template
                                
                                // holds NAME=0, DESCRIPTION=1, TYPE=2, value=3
                                $templatearray = $result['templatearray'];
                                
                                $templatecount = count($templatearray);
                                for ($i = 0; $i < $templatecount; $i++)
                                {
                                        // ensure template is not empty
                                        if (isset($templatearray[$i][4]) AND $templatearray[$i][4] != '')
                                        {
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "templates
                                                        (tid, name, description, type, status, original, content, createdate, styleid, product, sort)
                                                        VALUES(
                                                        NULL,
                                                        '" . $ilance->db->escape_string($templatearray[$i][0]) . "',
                                                        '" . $ilance->db->escape_string($templatearray[$i][1]) . "',
                                                        '" . $ilance->db->escape_string($templatearray[$i][2]) . "',
                                                        '1',
                                                        '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
                                                        '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
                                                        '" . DATETIME24H . "',
                                                        '" . intval($newstyleid) . "',
							'" . $ilance->db->escape_string($templatearray[$i][3]) . "',
							'" . intval($templatearray[$i][4]) . "')
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else
                                        {
                                                $notice .= "Error: style: <strong>".$templatearray[$i][1]."</strong> could not be added due to blank template data existing within the xml file (near CDATA)";
                                        }
                                }
                                
                                // set the imported style as default?
                                if (isset($ilance->GPC['makedefault']) AND $ilance->GPC['makedefault'] AND isset($newstyleid) AND $newstyleid > 0)
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "configuration
                                                SET value = '" . intval($newstyleid) . "'
                                                WHERE name = 'defaultstyle'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                print_action_success($phrase['_css_style_importation_success'], $ilance->GPC['return']);
                                exit();
                        }
                        else
                        {
                                // xml style exists in db already .. lets update templates instead
                                
                                // fetch style id based on the style name being imported (hopefully we have a match!)
                                $styleid = $ilance->db->fetch_field(DB_PREFIX . "styles", "name = '" . trim($ilance->db->escape_string($result['name'])) . "'", "styleid");
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "styles
                                        SET visible = '1',
                                        sort = '10'
                                        WHERE name = '" . trim($ilance->db->escape_string($result['name'])) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);

                                // #### move onto updating each template #######
                                $templatearray = $result['templatearray'];
                                $templatecount = count($templatearray);
				
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
								AND styleid = '" . intval($styleid) . "'
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
								'" . DATETIME24H . "',
								'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
								'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
								'" . $ilance->db->escape_string($templatearray[$i][3]) . "',
								'" . intval($templatearray[$i][4]) . "')
							", 0, null, __FILE__, __LINE__);	
						}
						else
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "templates
								SET `name` = '" . $ilance->db->escape_string($templatearray[$i][0]) . "',
								`description` = '" . $ilance->db->escape_string($templatearray[$i][1]) . "',
								`type` = '" . $ilance->db->escape_string($templatearray[$i][2]) . "',
								`status` = '1',
								`updatedate` = '" . DATETIME24H . "',
								`original` = '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
								`content` = '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
								`product` = '" . $ilance->db->escape_string($templatearray[$i][3]) . "',
								`sort` = '" . intval($templatearray[$i][4]) . "'
								WHERE `name` = '" . $ilance->db->escape_string($templatearray[$i][0]) . "'
								AND `type` = '" . $ilance->db->escape_string($templatearray[$i][2]) . "'
								AND `styleid` = '" . intval($styleid) . "'
							", 0, null, __FILE__, __LINE__);	
						}
                                        }
                                        else
                                        {
                                                $notice .= "Error: style: <strong>" . $templatearray[$i][0] . "</strong> could not be added due to blank template data existing within the xml file (near CDATA)";
                                        }
                                }
                                
                                // set the imported style as default?
                                if (isset($ilance->GPC['makedefault']) AND $ilance->GPC['makedefault'] AND isset($styleid) AND $styleid > 0)
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "configuration
                                                SET value = '" . intval($styleid) . "'
                                                WHERE name = 'defaultstyle'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                print_action_success($phrase['_css_style_importation_success'], $ilance->GPC['return']);
                                exit();
                        }
		}
		else
		{
			$error_string = xml_error_string($error_code);
                        
			$notice .= $phrase['_were_sorry_there_was_an_error_with_the_formatting_of_the_configuration_file'] . ' [' . $error_string . '].';
			print_action_failed($notice, $ilance->GPC['return']);
			exit();
		}
	}
	
	// #### MANAGING STYLES (CREATE, REMOVE and RENAME) ####################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'styles')
	{
		if (isset($ilance->GPC['styleid']) AND $ilance->GPC['styleid'] > 0)
		{
			// #### CREATE NEW STYLE ###############################
			if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'newstyle')
			{
				$newname = strip_tags($ilance->GPC['name']);
				
				$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "styles
					WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "styles
						(styleid, name, visible)
						VALUES(
						NULL,
						'" . $ilance->db->escape_string($newname) . "',
						'1')
					", 0, null, __FILE__, __LINE__);
					
					$newstyleid = $ilance->db->insert_id();
					
					$sql = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "templates
						WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
						{
							$ilance->db->query("
								INSERT INTO " . DB_PREFIX . "templates
								(tid, name, description, original, content, type, isupdated, updatedate, styleid, product, sort)
								VALUES(
								NULL,
								'" . $ilance->db->escape_string($res['name']) . "',
								'" . $ilance->db->escape_string($res['description']) . "',
								'" . $ilance->db->escape_string($res['original']) . "',
								'" . $ilance->db->escape_string($res['content']) . "',
								'" . $ilance->db->escape_string($res['type']) . "',
								'0',
								'" . $ilance->db->escape_string($res['updatedate']) . "',
								'" . $newstyleid . "',
								'" . $ilance->db->escape_string($res['product']) . "',
								'" . intval($res['sort']) . "')
							", 0, null, __FILE__, __LINE__);
						}
					}
					
					print_action_success($phrase['_the_new_style_was_created_and_is_available_to_the_template_system'], $ilance->GPC['return']);
					exit();
				}
			}
			
			// #### REMOVE STYLE ###################################
			else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'removestyle')
			{
				$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "styles
				", 0, null, __FILE__, __LINE__);
				$countstyles = $ilance->db->num_rows($sql);
				if ($countstyles > 1)
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "styles
						WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "templates
						WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
					", 0, null, __FILE__, __LINE__);
                                        
                                        // for each user that had this style installed, remove it and replace with new default style
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET styleid = '" . intval($ilconfig['defaultstyle']) . "'
                                                WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
                                        ", 0, null, __FILE__, __LINE__);
					
					print_action_success($phrase['_the_selected_style_group_and_associated_templates_was_removed_from_the_template_system'], $ilance->GPC['return']);
					exit();
				}
				else
				{
					print_action_success($phrase['_were_sorry_there_seems_to_be_only_1_available_style'], $ilance->GPC['return']);
					exit();
				}
			}
			
			// #### RENAME STYLE ###################################
			else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'renamestyle')
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "styles
					SET name = '" . strip_tags($ilance->db->escape_string($ilance->GPC['name'])) . "'
					WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
				", 0, null, __FILE__, __LINE__);
				
				print_action_success($phrase['_the_selected_style_name_title_was_successfully_updated'], $ilance->GPC['return']);
				exit();
			}
			
			// #### MAKE DEFAULT STYLE #############################
			else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'defaultstyle')
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "configuration
					SET value = '" . intval($ilance->GPC['styleid']) . "'
					WHERE name = 'defaultstyle'
				", 0, null, __FILE__, __LINE__);
				
				print_action_success($phrase['_the_selected_style_id_was_flagged_as_the_default_theme'], $ilance->GPC['return']);
				exit();
			}
		}
	}
	
	// #### CSS UPDATE HANDLER #############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'updatecss')
	{
		// #### HANDLE TEMPLATE UPDATE FOR THIS CSS TYPE #######
		foreach ($ilance->GPC['css'] AS $tid => $array)
		{
			if (!empty($ilance->GPC['css']["$tid"]['name']))
			{
				$name = $ilance->GPC['css']["$tid"]['name'];
				
				$ilance->GPC['css']["$tid"]['name'] = '';
				unset($ilance->GPC['css']["$tid"]['name']);
			}
			
			if (!empty($ilance->GPC['css']["$tid"]['sort']))
			{
				$sort = $ilance->GPC['css']["$tid"]['sort'];
				
				$ilance->GPC['css']["$tid"]['sort'] = '';
				unset($ilance->GPC['css']["$tid"]['sort']);
			}
			
			if (isset($ilance->GPC['css']["$tid"]['EXTRA']))
			{
				$ilance->GPC['css']["$tid"]['EXTRA'] = base64_encode($ilance->GPC['css']["$tid"]['EXTRA']);                                        
				$cssform = serialize($ilance->GPC['css']["$tid"]);
			}
			else
			{
				$cssform = serialize($ilance->GPC['css']["$tid"]);
			}
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "templates
				SET content = '" . addslashes($cssform) . "',
				name = '" . $ilance->db->escape_string($name) . "',
				sort = '" . intval($sort) . "'
				WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
				    AND tid = '" . intval($tid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		
		// #### REVERT TO ORIGINAL? ############################
		if (isset($ilance->GPC['revert']) AND is_array($ilance->GPC['revert']))
		{
			foreach ($ilance->GPC['revert'] AS $tid => $value)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "templates
					SET content = original
					WHERE tid = '" . intval($tid) . "'
					AND styleid = '" . intval($ilance->GPC['styleid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
			}
		}
		else if (isset($ilance->GPC['delete']) AND is_array($ilance->GPC['delete']))
		{
			foreach ($ilance->GPC['delete'] AS $tid => $value)
			{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "templates
					WHERE tid = '" . intval($tid) . "'
					AND styleid = '" . intval($ilance->GPC['styleid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
			}
		}
	    
		print_action_success($phrase['_the_selected_css_elements_have_been_updated_within_the_selected'], $ilance->GPC['return']);
		exit();
	}
	
	// #### EDITING CSS ####################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'editcss' AND isset($ilance->GPC['action']) AND isset($ilance->GPC['styleid']))
	{
		// #### UPDATING CSS ###############################################
		$ilance->GPC['pp'] = (isset($ilance->GPC['pp']) AND !empty($ilance->GPC['pp']) AND $ilance->GPC['pp'] > 0) ? intval($ilance->GPC['pp']) : 10;
                $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
                $limit = ' ORDER BY sort ASC LIMIT ' . (($ilance->GPC['page'] - 1) * intval($ilance->GPC['pp'])) . ',' . intval($ilance->GPC['pp']);
                
		$type = $ilance->GPC['action'];
		
		if (isset($ilance->GPC['csselement']) AND $ilance->GPC['csselement'] != '')
		{
			$sql = $ilance->db->query("
				SELECT tid, name, description, content, type, isupdated, product, sort
				FROM " . DB_PREFIX . "templates
				WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
				    AND name = '" . $ilance->db->escape_string($ilance->GPC['csselement']) . "'
			", 0, null, __FILE__, __LINE__);
                        
                        $number = 1;
                        $counter = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];                        
		}
		else
		{
                        $numberrows = $ilance->db->query("
                                SELECT tid, name, description, content, type, isupdated, product, sort
				FROM " . DB_PREFIX . "templates
				WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
				    AND type = '" . $ilance->db->escape_string($ilance->GPC['action']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        $number = $ilance->db->num_rows($numberrows);
                        $counter = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];
                        
			$sql = $ilance->db->query("
				SELECT tid, name, description, content, type, isupdated, product, sort
				FROM " . DB_PREFIX . "templates
				WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
				    AND type = '" . $ilance->db->escape_string($ilance->GPC['action']) . "'
                                $limit
			", 0, null, __FILE__, __LINE__);
		}
                
		if ($ilance->db->num_rows($sql) > 0)
		{
			$jsvar = '';
			$count = 1;
			while ($res = $ilance->db->fetch_array($sql))
			{
				if ($res['isupdated'])
				{
					$res['rowcolor'] = '#43DE43';
				}
				else
				{
					$res['rowcolor'] = '#1D3E54';
				}
				
				$content = unserialize($res['content']);
				
				$res['css_background'] = $content['background'];
				$res['css_color'] = $content['color'];
				$res['css_font_style'] = $content['font']['style'];
				$res['css_font_size'] = $content['font']['size'];
				$res['css_font_family'] = $content['font']['family'];
				
				$res['css_EXTRA'] = base64_decode($content['EXTRA']);
				//$res['css_EXTRA'] = $content['EXTRA'];
				
				$res['css_LINK_NORMAL_background'] = $content['LINK_NORMAL']['background'];
				$res['css_LINK_NORMAL_color'] = $content['LINK_NORMAL']['color'];
				$res['css_LINK_NORMAL_textdecoration'] = $content['LINK_NORMAL']['text-decoration'];
				
				$res['css_LINK_VISITED_background'] = $content['LINK_VISITED']['background'];
				$res['css_LINK_VISITED_color'] = $content['LINK_VISITED']['color'];
				$res['css_LINK_VISITED_textdecoration'] = $content['LINK_VISITED']['text-decoration'];
				
				$res['css_LINK_HOVER_background'] = $content['LINK_HOVER']['background'];
				$res['css_LINK_HOVER_color'] = $content['LINK_HOVER']['color'];
				$res['css_LINK_HOVER_textdecoration'] = $content['LINK_HOVER']['text-decoration'];
				
				$res['count1'] = $count;
				$res['css_1_div'] = '<div title="' . $phrase['_click_to_change_color'] . '" id="preview_' . $count . '" class="sample_swatch" style="background:' . $res['css_background'] . ';" onclick="showColorGrid3(\'color_' . $count . '\',\'preview_' . $count . '\')"></div>';
				$count = $count + 1;
				
				$res['count2'] = $count;
				$res['css_2_div'] = '<div title="' . $phrase['_click_to_change_color'] . '" id="preview_' . $count . '" class="sample_swatch" style="background:' . $res['css_color'] . ';" onclick="showColorGrid3(\'color_' . $count . '\',\'preview_' . $count . '\')"></div>';
				$count = $count + 1;
				
				$res['count3'] = $count;
				$res['css_3_div'] = '<div title="' . $phrase['_click_to_change_color'] . '" id="preview_' . $count . '" class="sample_swatch" style="background:' . $res['css_LINK_NORMAL_background'] . ';" onclick="showColorGrid3(\'color_' . $count . '\',\'preview_' . $count . '\')"></div>';
				$count = $count + 1;
				
				$res['count4'] = $count;
				$res['css_4_div'] = '<div title="' . $phrase['_click_to_change_color'] . '" id="preview_' . $count . '" class="sample_swatch" style="background:' . $res['css_LINK_NORMAL_color'] . ';" onclick="showColorGrid3(\'color_' . $count . '\',\'preview_' . $count . '\')"></div>';
				$count = $count + 1;
				
				$res['count5'] = $count;
				$res['css_5_div'] = '<div title="' . $phrase['_click_to_change_color'] . '" id="preview_' . $count . '" class="sample_swatch" style="background:' . $res['css_LINK_VISITED_background'] . ';" onclick="showColorGrid3(\'color_' . $count . '\',\'preview_' . $count . '\')"></div>';
				$count = $count + 1;
				
				$res['count6'] = $count;
				$res['css_6_div'] = '<div title="' . $phrase['_click_to_change_color'] . '" id="preview_' . $count . '" class="sample_swatch" style="background:' . $res['css_LINK_VISITED_color'] . ';" onclick="showColorGrid3(\'color_' . $count . '\',\'preview_' . $count . '\')"></div>';
				$count = $count + 1;
				
				$res['count7'] = $count;
				$res['css_7_div'] = '<div title="' . $phrase['_click_to_change_color'] . '" id="preview_' . $count . '" class="sample_swatch" style="background:' . $res['css_LINK_HOVER_background'] . ';" onclick="showColorGrid3(\'color_' . $count . '\',\'preview_' . $count . '\')"></div>';
				$count = $count + 1;
				
				$res['count8'] = $count;
				$res['css_8_div'] = '<div title="' . $phrase['_click_to_change_color'] . '" id="preview_' . $count . '" class="sample_swatch" style="background:' . $res['css_LINK_HOVER_color'] . ';" onclick="showColorGrid3(\'color_' . $count . '\',\'preview_' . $count . '\')"></div>';
				$count = $count + 1;
				
				$cssedit[] = $res;
			}
		}
                
                $ilance->GPC['csselement'] = isset($ilance->GPC['csselement']) ? $ilance->GPC['csselement'] : '';
                $ilance->GPC['action'] = isset($ilance->GPC['action']) ? $ilance->GPC['action'] : 'cssclient';
                $ilance->GPC['styleid'] = ($ilance->GPC['styleid'] > 0) ? intval($ilance->GPC['styleid']) : $ilconfig['defaultstyle'];
                
                $prevnext = print_pagnation($number, $ilance->GPC['pp'], $ilance->GPC['page'], $counter, $ilpage['template'] . '?cmd=editcss&amp;styleid=' . intval($ilance->GPC['styleid']) . '&amp;action=' . $ilance->GPC['action'] . '&amp;csselement=' . $ilance->GPC['csselement']);
		
		$headinclude .= '
<script src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/javascript/functions_colorpicker.js" type="text/javascript"></script>

<style>
.sample_swatch
{
        cursor: pointer;
        font-weight: normal;
        border: inset 1px #DEE0E2;
        width: 40px;
        height: 21px;
}
</style>';
	}
	
	// ### ADD NEW CSS ELEMENT #############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'addcss' AND isset($ilance->GPC['action']) AND isset($ilance->GPC['styleid']))
	{
		if (isset($ilance->GPC['css']['EXTRA']) AND !empty($ilance->GPC['css']['EXTRA']))
		{
			$extra64 = base64_encode($ilance->GPC['css']['EXTRA']);
			$ilance->GPC['css']['EXTRA'] = $extra64;				
			$cssform = serialize($ilance->GPC['css']);
		}
		else
		{
			$cssform = serialize($ilance->GPC['css']);
		}
		
		$ilance->GPC['classorid'] = (isset($ilance->GPC['classorid']) ? $ilance->GPC['classorid'] : '');
		$ilance->GPC['element'] = $ilance->GPC['classorid'] . $ilance->GPC['name'];
		$ilance->GPC['content'] = $cssform;
		$ilance->GPC['description'] = isset($ilance->GPC['description']) ? $ilance->GPC['description'] : '';
		$ilance->GPC['product'] = isset($ilance->GPC['product']) ? $ilance->GPC['product'] : 'ilance';
		$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '100';
		
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "templates
			(tid, name, description, type, styleid, createdate, original, content, product, status, sort)
			VALUES(
			NULL,
			'" . trim($ilance->db->escape_string($ilance->GPC['element'])) . "',
			'" . trim($ilance->db->escape_string($ilance->GPC['description'])) . "',
			'" . trim($ilance->db->escape_string($ilance->GPC['action'])) . "',
			'" . intval($ilance->GPC['styleid']) . "',
			'" . DATETIME24H . "',
			'" . $ilance->db->escape_string($ilance->GPC['content']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['content']) . "',
			'" . trim($ilance->db->escape_string($ilance->GPC['product'])) . "',
			'1',
			'" . intval($ilance->GPC['sort']) . "')
		", 0, null, __FILE__, __LINE__);
		
		print_action_success($phrase['_the_new_css_element_was_added_to_the_selected_style_set'], $ilance->GPC['return']);
		exit();
	}
	
	// #### CREATE TEMPLATE VARIABLE HANDLER ###################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'createtemplatevar')
	{
		$area_title = $phrase['_creating_template_variable'];
		$page_title = SITE_NAME . ' - ' . $phrase['_creating_template_variable'];
		
		$ilance->GPC['product'] = isset($ilance->GPC['product']) ? $ilance->GPC['product'] : 'ilance';
		$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '100';
		
		if (isset($ilance->GPC['gid']) AND isset($ilance->GPC['type']) AND isset($ilance->GPC['author']) AND isset($ilance->GPC['name']) AND isset($ilance->GPC['content']) AND isset($ilance->GPC['description']))
		{
			// insert new template variable for all installed styles
			$sql = $ilance->db->query("SELECT styleid FROM " . DB_PREFIX . "styles");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql))
				{
					$ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "templates
                                                (tid, name, description, original, content, type, status, createdate, author, version, styleid, product, sort)
						VALUES (
						NULL,
                                                '" . $ilance->db->escape_string($ilance->GPC['name']) . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['content']) . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['content']) . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['type']) . "',
                                                '1',
                                                NOW(),
                                                '" . $ilance->db->escape_string($ilance->GPC['author']) . "',
                                                '1.0',
                                                '" . $res['styleid'] . "',
						'" . $ilance->db->escape_string($ilance->GPC['product']) . "',
						'" . intval($ilance->GPC['sort']) . "')
                                        ", 0, null, __FILE__, __LINE__);
				}
                                
				print_action_success($phrase['_the_new_template_variable_was_created_and_is_available_to_the_template_system'], $ilance->GPC['return']);
				exit();
			}
		}
	}
	
	// #### TEMPLATE VARIABLES HANDLER #############################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_update-template-variables')
	{
		$area_title = $phrase['_updating_template_variables'];
		$page_title = SITE_NAME . ' - ' . $phrase['_updating_template_variables'];
		
		$notice = '';
		
		if (isset($ilance->GPC['templatevariable']) AND $ilance->GPC['templatevariable'] != '')
		{
			foreach ($ilance->GPC['templatevariable'] AS $key => $value)
			{
				$content = stripslashes($value);
				
				$sql = $ilance->db->query("
					SELECT content
					FROM " . DB_PREFIX . "templates 
					WHERE tid = '" . intval($key) . "'
					    AND styleid = '" . intval($ilance->GPC['styleid']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql);
					if ($res['content'] != $content)
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "templates 
							SET content = '" . $ilance->db->escape_string($content) . "',
							updatedate = '" . DATETIME24H . "',
							isupdated = '1'
							WHERE tid = '" . intval($key) . "'
							    AND styleid = '" . intval($ilance->GPC['styleid']) . "'
						", 0, null, __FILE__, __LINE__);
						
						$notice .= "<li />" . $phrase['_updated_template_content_for_template_pound'] . "<strong>$key</strong>.";
					}
				}
			}
			
			print_action_success($notice, $ilance->GPC['return']);
			exit();
		}
		else
		{
			print_action_failed($phrase['_there_was_a_problem_with_updating_your_templates_please_retry_your_actions'], $ilance->GPC['return']);
			exit();
		}
	}
	
	// #### CREATE TEMPLATE VARIABLE HANDLER ###############################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'createtemplatevar')
	{
		$area_title = $phrase['_creating_template_variable'];
		$page_title = SITE_NAME . ' - ' . $phrase['_creating_template_variable'];
		
		$ilance->GPC['product'] = isset($ilance->GPC['product']) ? $ilance->GPC['product'] : 'ilance';
		$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '100';
		
		if (isset($ilance->GPC['gid']) AND isset($ilance->GPC['type']) AND isset($ilance->GPC['author']) AND isset($ilance->GPC['name']) AND isset($ilance->GPC['content']) AND isset($ilance->GPC['description']))
		{
			// insert new template variable for all installed styles
			$sql = $ilance->db->query("SELECT styleid FROM " . DB_PREFIX . "styles");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "templates
                                                (tid, name, description, original, content, type, status, createdate, author, version, styleid, product, sort)
						VALUES (
                                                NULL,
                                                '" . $ilance->db->escape_string($ilance->GPC['name']) . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['content']) . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['content']) . "',
                                                '" . $ilance->db->escape_string($ilance->GPC['type']) . "',
                                                '1',
                                                NOW(),
                                                '" . $ilance->db->escape_string($ilance->GPC['author']) . "',
                                                '1.0',
                                                '" . $res['styleid'] . "',
						'" . $ilance->db->escape_string($ilance->GPC['product']) . "',
						'" . intval($ilance->GPC['sort']) . "')
                                        ", 0, null, __FILE__, __LINE__);
				}
                                
				print_action_success($phrase['_the_new_template_variable_was_created_and_is_available_to_the_template_system'], $ilance->GPC['return']);
				exit();
			}
		}
	}
        
        // #### REMOVE TEMPLATE VARIABLE HANDLER ###############################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'removetemplatevar')
	{
		$area_title = $phrase['_removing_template_variable'];
		$page_title = SITE_NAME . ' - ' . $phrase['_removing_template_variable'];
		
		if (isset($ilance->GPC['tid']) AND $ilance->GPC['tid'] > 0)
		{
			// insert new template variable for all installed styles
			$ilance->db->query("
                                DELETE FROM " . DB_PREFIX . "templates
                                WHERE tid = '" . intval($ilance->GPC['tid']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
			print_action_success($phrase['_the_template_variable_was_removed_from_the_template_system'], $ilance->GPC['return']);
                        exit();
		}
	}
	
	
	$headinclude .= '
<!--<script src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/javascript/functions_colorpicker.js" type="text/javascript"></script>-->

<style>
.sample_swatch
{
        cursor: pointer;
        font-weight: normal;
        border: inset 1px #DEE0E2;
        width: 40px;
        height: 21px;
}
</style>';
	
	$area_title = $phrase['_templates_administration_menu'];
	$page_title = SITE_NAME . ' - ' . $phrase['_templates_administration_menu'];

	($apihook = $ilance->api('admincp_template_management')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['template'], $ilpage['template'], $_SESSION['ilancedata']['user']['slng']);
	
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'edittemplates' AND isset($ilance->GPC['styleid']))
	{
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'templatevars')
		{
			$styleid = isset($ilance->GPC['styleid']) ? intval($ilance->GPC['styleid']) : $ilconfig['defaultstyle'];
			$show['no_templatevariables'] = false;
			
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "templates
				WHERE type = 'variable'
				    AND styleid = '" . intval($ilance->GPC['styleid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
                                $return = $ilpage['template'] . '?cmd=templatevars&amp;styleid=' . $ilance->GPC['styleid'] . '&amp;action=' . $ilance->GPC['action'];
                                $return = urlencode($return);
                                
				while ($res = $ilance->db->fetch_array($sql))
				{
                                        $res['name'] = $res['name'];
					if ($res['isupdated'])
					{
						$res['rowcolor'] = '#43DE43';
					}
					else
					{
						$res['rowcolor'] = '#1D3E54';
					}
					$res['description'] = stripslashes($res['description']);
                                        $res['content'] = ilance_htmlentities($res['content']);
                                        $res['remove'] = '<a href="' . $ilpage['template'] . '?cmd=removetemplatevar&amp;tid=' . $res['tid'] . '&amp;return=' . $return . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
                                        $res['phpicon'] = '<nophraseparse><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\\\x27' . $res['name'] . '\\\x27]\') && alert(\'This variable: $ilconfig[\\\x27' . $res['name'] . '\\\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\\\x27<span style=color:blue>' . $res['name'] . '</span>\\\x27]<div class=smaller gray>' . $phrase['_click_this_icon_to_copy_the_html_variable_to_your_clipboard'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'icons/php_icon.gif" hspace="0" vspace="0" border="0" alt="" /></a></nophraseparse>';
                                        $res['htmlicon'] = '<nophraseparse><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'{' . $res['name'] . '}\') && alert(\'This variable: {' . $res['name'] . '} has been copied to your clipboard\')" onmouseover="Tip(\'<strong>HTML</strong> variable: {<span style=color:blue>' . $res['name'] . '</span>}<div class=smaller gray>' . $phrase['_click_this_icon_to_copy_the_html_variable_to_your_clipboard'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'wysiwyg/html_tag.gif" hspace="0" vspace="0" border="0" alt="" /></a></nophraseparse>';
					$templatevariables[] = $res;
				}
				
				$show['no_templatevariables'] = false;
			}
			else
			{
				$show['no_templatevariables'] = true;
			}
		}
	}
	else
	{
		$show['no_templatevariables'] = true;
	}
	
	$styles_pulldown = (isset($ilance->GPC['styleid']))
		? $ilance->styles->print_styles_pulldown($ilance->GPC['styleid'])
		: $ilance->styles->print_styles_pulldown();
		
	$defaultstyle = $ilance->styles->fetch_default_style_title();
	
	// #### css elements pulldown ##########################################
	$csselement = isset($ilance->GPC['csselement']) ? $ilance->GPC['csselement'] : '';
	$elements_pulldown = $ilance->styles->print_css_elements_pulldown($csselement);
	
	// #### language pulldown menu #########################################
	$languageid = (isset($ilance->GPC['languageid'])) ? intval($ilance->GPC['languageid']) : 0;
	$language_pulldown = $ilance->language->print_language_pulldown($languageid, false);
	
        // #### after update we want to return where we were ###################
        $after_update_return_to_page = $ilpage['template'] . '?';
        
        if (isset($ilance->GPC['cmd']))
        {
                $after_update_return_to_page .= "cmd=" . $ilance->GPC['cmd'] . "&amp;";
        }
        if (isset($ilance->GPC['styleid']))
        {
                $after_update_return_to_page .= "styleid=" . $ilance->GPC['styleid'] . "&amp;";
        }
        if (isset($ilance->GPC['action']))
        {
                $after_update_return_to_page .= "action=" . $ilance->GPC['action'] . "&amp;";
        }
	
	// #### template settings ##############################################
	$global_templatesettings = $ilance->admincp->construct_admin_input('template', $ilpage['template']);
	
	$styleid = isset($ilance->GPC['styleid']) ? intval($ilance->GPC['styleid']) : 0;
	
	// #### products pulldown menu #########################################
	$productselected = isset($ilance->GPC['product']) ? $ilance->GPC['product'] : 'ilance';
	$products_pulldown = $ilance->admincp->products_pulldown($productselected);
        
        $pprint_array = array('products_pulldown','prevnext','buildversion','ilanceversion','login_include_admin','elements_pulldown','type','styleid','global_templatesettings','styleid','language_pulldown','defaultstyle','styles_pulldown','template_variables','after_update_return_to_page');
        
        ($apihook = $ilance->api('admincp_templates_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'templates.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav','subnav_settings','templatevariables','cssedit'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
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