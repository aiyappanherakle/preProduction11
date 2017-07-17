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
* Email functions for ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
/**
* Function to dispatch new email using php's mail() or SMTP
*
* @param       string         to email address
* @param       string         email subject
* @param       string         email message
* @param       string         email from address
* @param       string         email from name
* @param       bool           send html formatted email? (default is false)
* @param       string         log type (default alert)
*
* @return      bool           Returns true on successful email dispatch
*/
// murugan added here $project for Save project Id in email log On Apr 5
function send_email($toemail = '', $subject = '', $message = '', $from = '', $fromname = '', $html = false, $logtype = 'alert', $project = 0)
{
        global $ilance, $myapi, $ilconfig, $ilpage;
        if(ENVIROMNENT!='production')
		$toemail=$ilconfig['globalserversettings_testemail'];
	if(defined('SITE_TO_EMAIL'))
		$toemail=SITE_TO_EMAIL;
		 $sql = $ilance->db->query("
                                SELECT status
                                FROM " . DB_PREFIX . "users 
                                WHERE email = '" . $toemail. "'
                        ");
		if($ilance->db->num_rows($sql)>0)
		{
				
			$user = $ilance->db->fetch_array($sql);
					
					if($user['status'] == 'active')	
					{
					if (empty($toemail) OR empty($subject) OR empty($message))
					{
						return false;
					}
				
				$ilance->bbcode = construct_object('api.bbcode');
				
				$pathto_sendmail = @ini_get('sendmail_path');
				$uid = 0;
				
				($apihook = $ilance->api('send_email_start')) ? eval($apihook) : false;
				
				$delimiter = "\n";
				if (!$pathto_sendmail OR defined('SMTP_ENABLED') AND SMTP_ENABLED)
				{
						$delimiter = "\r\n";
				}
				
				$toemail = trim($toemail);
				if (!empty($toemail))
				{
						$toemail = un_htmlspecialchars($toemail);
						$subject = trim($subject);
						$message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
						
						if (!defined('NO_DB'))
						{
								$message = $ilance->template->parse_hash('emailtemplate', array('ilpage' => $ilpage), 1, $message);
								if ((mb_strtolower($ilconfig['template_charset']) == 'iso-8859-1' OR !empty($ilconfig['template_charset'])) AND preg_match('/&[a-z0-9#]+;/i', $message))
								{
										$message = utf8_encode($message);
										$subject = utf8_encode($subject);
										$encoding = 'UTF-8';
										$unicode_decode = true;
								}
								else
								{
										$encoding = $ilconfig['template_charset'];
										$unicode_decode = false;
								}
								
								$message = un_htmlspecialchars($message, $unicode_decode); 
						}
						else
						{
								$message = utf8_encode($message);
								$subject = utf8_encode($subject);
								$encoding = 'UTF-8';
								$unicode_decode = true;
								$message = un_htmlspecialchars($message, $unicode_decode); 
						}
						
						// #### handle bbcode removal ##################################
						$message = $ilance->bbcode->strip_bb_tags($message);
				
				// #### if we're sending as HTML, convert newlines to <br />'s
				if ($html)
				{
					$message = nl2br($message);
				}
						$subject = un_htmlspecialchars($subject, $unicode_decode);
						
						if (empty($from))
						{
								$from = SITE_EMAIL;
						}
						@ini_set('sendmail_from', $from);
						
						if (empty($fromname))
						{
								$fromname = SITE_NAME;
						}
						if ($unicode_decode)
						{
								$fromname = utf8_encode($fromname);
						}
						$fromname = un_htmlspecialchars($fromname, $unicode_decode);
						//$headers = "From: \"$fromname\" <" . $from . ">" . $delimiter . "Return-Path: " . $from . $delimiter;
						// murugan added below lini on july 19
						$headers = "From: \"$fromname\" <" . $from . ">" . $delimiter . "Return-Path: <bounces-ian%40domain_com@greatcollections.com>" . $delimiter;	
						$http_host = HTTP_SERVER;
						if (!$http_host)
						{
								$http_host = mb_substr(md5($message), 6, 12) . '.ilance_unknown.unknown';
						}
						$msgid = '<' . gmdate('YmdHs') . '.' . mb_substr(md5($message . microtime()), 0, 6) . rand(100000, 999999) . '@' . $http_host . '>';
						$headers .= 'Message-ID: ' . $msgid . $delimiter . "X-Priority: 3" . $delimiter . "X-Mailer: " . stripslashes(SITE_NAME) . " " . $ilance->config['ilversion'] . $delimiter;
						if ($html)
						{
								// we are sending html formatted email
								$headers .= 'Content-Type: text/html' . iif($encoding, "; charset=$encoding") . $delimiter;
						}
						else
						{
								$headers .= 'Content-Type: text/plain' . iif($encoding, "; charset=$encoding") . $delimiter;
						}
						$headers .= "Content-Transfer-Encoding: 8bit" . $delimiter;                
						
						if (defined('SMTP_ENABLED') AND SMTP_ENABLED AND defined('SMTP_HOST') AND SMTP_HOST != '' AND defined('SMTP_PORT') AND SMTP_PORT != '')
						{
								@ini_set('SMTP', SMTP_HOST);
								@ini_set('smtp_port', SMTP_PORT);
								
								$ilance->smtp = construct_object('api.smtp');
								$ilance->smtp->toemail = $toemail;
								$ilance->smtp->fromemail = $from;
								$ilance->smtp->headers = $headers;
								$ilance->smtp->subject = $subject;
								$ilance->smtp->message = $message;
								
								if (!$ilance->smtp->send())
								{
										if (!defined('NO_DB'))
										{
												// could not send - queue for sending later via cron
												$ilance->db->query("
														INSERT INTO " . DB_PREFIX . "emaillog
														(emaillogid, logtype, user_id, project_id, email, subject, body, date, sent)
														VALUES(
														NULL,
														'" . $ilance->db->escape_string($logtype) . "',
														'" . intval($uid) . "',
														'" . intval($project) . "',
														'" . $ilance->db->escape_string($toemail) . "',
														'" . $ilance->db->escape_string($subject) . "',
														'" . $ilance->db->escape_string($message) . "',
														'" . DATETIME24H . "',
														'no')
												", 0, null, __FILE__, __LINE__);        
										}
								}
								else
								{
										if (!defined('NO_DB'))
										{
												$sql = $ilance->db->query("
														SELECT user_id
														FROM " . DB_PREFIX . "users
														WHERE email = '" . $ilance->db->escape_string($toemail) . "'
														LIMIT 1
												", 0, null, __FILE__, __LINE__);
												if ($ilance->db->num_rows($sql) > 0)
												{
														$res = $ilance->db->fetch_array($sql);
														$uid = $res['user_id'];
												}
												unset($res);
												
												$ilance->db->query("
														 INSERT INTO " . DB_PREFIX . "emaillog
														(emaillogid, logtype, user_id, project_id, email, subject, body, date, sent)
														VALUES(
														NULL,
														'" . $ilance->db->escape_string($logtype) . "',
														'" . intval($uid) . "',
														'" . intval($project) . "',
														'" . $ilance->db->escape_string($toemail) . "',
														'" . $ilance->db->escape_string($subject) . "',
														'" . $ilance->db->escape_string($message) . "',
														'" . DATETIME24H . "',
														'yes')
												", 0, null, __FILE__, __LINE__);
										}
								}
						}
						else
						{
								if (!mb_send_mail($toemail, $subject, $message, $headers))
								{
										if (!defined('NO_DB'))
										{
												// could not send - queue for sending later via cron
												$ilance->db->query("
														INSERT INTO " . DB_PREFIX . "emaillog
														(emaillogid, logtype, user_id, project_id, email, subject, body, date, sent)
														VALUES(
														NULL,
														'" . $ilance->db->escape_string($logtype) . "',
														'" . intval($uid) . "',
														'" . intval($project) . "',
														'" . $ilance->db->escape_string($toemail) . "',
														'" . $ilance->db->escape_string($subject) . "',
														'" . $ilance->db->escape_string($message) . "',
														'" . DATETIME24H . "',
														'no')
												", 0, null, __FILE__, __LINE__);
										}
								}
								else
								{
										if (!defined('NO_DB'))
										{
												$sql = $ilance->db->query("
														SELECT user_id
														FROM " . DB_PREFIX . "users
														WHERE email = '" . $ilance->db->escape_string($toemail) . "'
														LIMIT 1
												", 0, null, __FILE__, __LINE__);
												if ($ilance->db->num_rows($sql) > 0)
												{
														$res = $ilance->db->fetch_array($sql);
														$uid = $res['user_id'];
												}
												unset($res);
												
												$ilance->db->query("
														 INSERT INTO " . DB_PREFIX . "emaillog
														(emaillogid, logtype, user_id, project_id, email, subject, body, date, sent)
														VALUES(
														NULL,
														'" . $ilance->db->escape_string($logtype) . "',
														'" . intval($uid) . "',
														'" . intval($project) . "',
														'" . $ilance->db->escape_string($toemail) . "',
														'" . $ilance->db->escape_string($subject) . "',
														'" . $ilance->db->escape_string($message) . "',
														'" . DATETIME24H . "',
														'yes')
												", 0, null, __FILE__, __LINE__);
										}
								}
						}
				
				}
					}
		}
		else	
		{
				
					
	   
			
	        if (empty($toemail) OR empty($subject) OR empty($message))
	        {
	                return false;
	        }
	        
	        $ilance->bbcode = construct_object('api.bbcode');
	        
	        $pathto_sendmail = @ini_get('sendmail_path');
	        $uid = 0;
	        
	        ($apihook = $ilance->api('send_email_start')) ? eval($apihook) : false;
	        
	        $delimiter = "\n";
	        if (!$pathto_sendmail OR defined('SMTP_ENABLED') AND SMTP_ENABLED)
	        {
	                $delimiter = "\r\n";
	        }
	        
	        $toemail = trim($toemail);
	        if (!empty($toemail))
	        {
	                $toemail = un_htmlspecialchars($toemail);
	                $subject = trim($subject);
	                $message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
	                
	                if (!defined('NO_DB'))
	                {
	                        $message = $ilance->template->parse_hash('emailtemplate', array('ilpage' => $ilpage), 1, $message);
	                        if ((mb_strtolower($ilconfig['template_charset']) == 'iso-8859-1' OR !empty($ilconfig['template_charset'])) AND preg_match('/&[a-z0-9#]+;/i', $message))
	                        {
	                                $message = utf8_encode($message);
	                                $subject = utf8_encode($subject);
	                                $encoding = 'UTF-8';
	                                $unicode_decode = true;
	                        }
	                        else
	                        {
	                                $encoding = $ilconfig['template_charset'];
	                                $unicode_decode = false;
	                        }
	                        
	                        $message = un_htmlspecialchars($message, $unicode_decode); 
	                }
	                else
	                {
	                        $message = utf8_encode($message);
	                        $subject = utf8_encode($subject);
	                        $encoding = 'UTF-8';
	                        $unicode_decode = true;
	                        $message = un_htmlspecialchars($message, $unicode_decode); 
	                }
	                
	                // #### handle bbcode removal ##################################
	                $message = $ilance->bbcode->strip_bb_tags($message);
			
			// #### if we're sending as HTML, convert newlines to <br />'s
			if ($html)
			{
				$message = nl2br($message);
			}
	                $subject = un_htmlspecialchars($subject, $unicode_decode);
	                
	                if (empty($from))
	                {
	                        $from = SITE_EMAIL;
	                }
	                @ini_set('sendmail_from', $from);
	                
	                if (empty($fromname))
	                {
	                        $fromname = SITE_NAME;
	                }
	                if ($unicode_decode)
	                {
	                        $fromname = utf8_encode($fromname);
	                }
	                $fromname = un_htmlspecialchars($fromname, $unicode_decode);
	                //$headers = "From: \"$fromname\" <" . $from . ">" . $delimiter . "Return-Path: " . $from . $delimiter;
					// murugan added below lini on july 19
					$headers = "From: \"$fromname\" <" . $from . ">" . $delimiter . "Return-Path: <bounces-ian%40domain_com@greatcollections.com>" . $delimiter;	
	                $http_host = HTTP_SERVER;
	                if (!$http_host)
	                {
	                        $http_host = mb_substr(md5($message), 6, 12) . '.ilance_unknown.unknown';
	                }
	                $msgid = '<' . gmdate('YmdHs') . '.' . mb_substr(md5($message . microtime()), 0, 6) . rand(100000, 999999) . '@' . $http_host . '>';
	                $headers .= 'Message-ID: ' . $msgid . $delimiter . "X-Priority: 3" . $delimiter . "X-Mailer: " . stripslashes(SITE_NAME) . " " . $ilance->config['ilversion'] . $delimiter;
	                if ($html)
	                {
	                        // we are sending html formatted email
	                        $headers .= 'Content-Type: text/html' . iif($encoding, "; charset=$encoding") . $delimiter;
	                }
	                else
	                {
	                        $headers .= 'Content-Type: text/plain' . iif($encoding, "; charset=$encoding") . $delimiter;
	                }
	                $headers .= "Content-Transfer-Encoding: 8bit" . $delimiter;                
	                
	                if (defined('SMTP_ENABLED') AND SMTP_ENABLED AND defined('SMTP_HOST') AND SMTP_HOST != '' AND defined('SMTP_PORT') AND SMTP_PORT != '')
	                {
	                        @ini_set('SMTP', SMTP_HOST);
	                        @ini_set('smtp_port', SMTP_PORT);
	                        
	                        $ilance->smtp = construct_object('api.smtp');
	                        $ilance->smtp->toemail = $toemail;
	                        $ilance->smtp->fromemail = $from;
	                        $ilance->smtp->headers = $headers;
	                        $ilance->smtp->subject = $subject;
	                        $ilance->smtp->message = $message;
	                        
	                        if (!$ilance->smtp->send())
	                        {
	                                if (!defined('NO_DB'))
	                                {
	                                        // could not send - queue for sending later via cron
	                                        $ilance->db->query("
	                                                INSERT INTO " . DB_PREFIX . "emaillog
	                                                (emaillogid, logtype, user_id, project_id, email, subject, body, date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'no')
	                                        ", 0, null, __FILE__, __LINE__);        
	                                }
	                        }
	                        else
	                        {
	                                if (!defined('NO_DB'))
	                                {
	                                        $sql = $ilance->db->query("
	                                                SELECT user_id
	                                                FROM " . DB_PREFIX . "users
	                                                WHERE email = '" . $ilance->db->escape_string($toemail) . "'
	                                                LIMIT 1
	                                        ", 0, null, __FILE__, __LINE__);
	                                        if ($ilance->db->num_rows($sql) > 0)
	                                        {
	                                                $res = $ilance->db->fetch_array($sql);
	                                                $uid = $res['user_id'];
	                                        }
	                                        unset($res);
	                                        
	                                        $ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "emaillog
	                                                (emaillogid, logtype, user_id, project_id, email, subject, body, date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'yes')
	                                        ", 0, null, __FILE__, __LINE__);
	                                }
	                        }
	                }
	                else
	                {
	                        if (!mb_send_mail($toemail, $subject, $message, $headers))
	                        {
	                                if (!defined('NO_DB'))
	                                {
	                                        // could not send - queue for sending later via cron
	                                        $ilance->db->query("
	                                                INSERT INTO " . DB_PREFIX . "emaillog
	                                                (emaillogid, logtype, user_id, project_id, email, subject, body, date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'no')
	                                        ", 0, null, __FILE__, __LINE__);
	                                }
	                        }
	                        else
	                        {
	                                if (!defined('NO_DB'))
	                                {
	                                        $sql = $ilance->db->query("
	                                                SELECT user_id
	                                                FROM " . DB_PREFIX . "users
	                                                WHERE email = '" . $ilance->db->escape_string($toemail) . "'
	                                                LIMIT 1
	                                        ", 0, null, __FILE__, __LINE__);
	                                        if ($ilance->db->num_rows($sql) > 0)
	                                        {
	                                                $res = $ilance->db->fetch_array($sql);
	                                                $uid = $res['user_id'];
	                                        }
	                                        unset($res);
	                                        
	                                        $ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "emaillog
	                                                (emaillogid, logtype, user_id, project_id, email, subject, body, date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'yes')
	                                        ", 0, null, __FILE__, __LINE__);
	                                }
	                        }
	                }
	        
			}
		}
	
	
}


function send_email_enquiry($toemail = '', $subject = '', $message = '', $from = '', $fromname = '', $html = false, $logtype = 'alert', $project = 0)
{
        global $ilance, $myapi, $ilconfig, $ilpage;
	if(ENVIROMNENT!='production')
		$toemail=$ilconfig['globalserversettings_testemail'];
	if(defined('SITE_TO_EMAIL'))
		$toemail=SITE_TO_EMAIL;
				$ilance->db->query("
								INSERT INTO " . DB_PREFIX . "emaillog_to_subject
								(emaillogid, logtype, project_id, email, subject, body, date)
								VALUES(
								NULL,
								'" . $ilance->db->escape_string($logtype) . "',
								'" . intval($project) . "',
								'" . $ilance->db->escape_string($toemail) . "',
								'" . $ilance->db->escape_string($subject) . "',
								'" . $ilance->db->escape_string($message) . "',
								'" . DATETIME24H . "')
						", 0, null, __FILE__, __LINE__);
				if(!empty($subject))	
				{
					$subject .= ' - '.$ilance->db->insert_id();
				}
		//for bug #4455 end
		
		
        $sql = $ilance->db->query("
                                SELECT status
                                FROM " . DB_PREFIX . "users 
                                WHERE email = '" . $toemail. "'
                        ");
						
		if($ilance->db->num_rows($sql)>0)
		{
				
			$user = $ilance->db->fetch_array($sql);
					
					if($user['status'] == 'active')	
					{
					if (empty($toemail) OR empty($subject) OR empty ($from)OR empty($message))
					{
						return false;
					}
				
				$ilance->bbcode = construct_object('api.bbcode');
				
				$pathto_sendmail = @ini_get('sendmail_path');
				$uid = 0;
				
				($apihook = $ilance->api('send_email_start')) ? eval($apihook) : false;
				
				$delimiter = "\n";
				if (!$pathto_sendmail OR defined('SMTP_ENABLED') AND SMTP_ENABLED)
				{
						$delimiter = "\r\n";
				}
				
				 $toemail = trim($toemail);
				if (!empty($toemail))
				{
						$toemail = un_htmlspecialchars($toemail);
						$subject = trim($subject);
						$message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
						
						if (!defined('NO_DB'))
						{
								$message = $ilance->template->parse_hash('emailtemplate', array('ilpage' => $ilpage), 1, $message);
								if ((mb_strtolower($ilconfig['template_charset']) == 'iso-8859-1' OR !empty($ilconfig['template_charset'])) AND preg_match('/&[a-z0-9#]+;/i', $message))
								{
										$message = utf8_encode($message);
										$subject = utf8_encode($subject);
										$encoding = 'UTF-8';
										$unicode_decode = true;
								}
								else
								{
										$encoding = $ilconfig['template_charset'];
										$unicode_decode = false;
								}
								
								$message = un_htmlspecialchars($message, $unicode_decode); 
						}
						else
						{
								$message = utf8_encode($message);
								$subject = utf8_encode($subject);
								$encoding = 'UTF-8';
								$unicode_decode = true;
								$message = un_htmlspecialchars($message, $unicode_decode); 
						}
						
						// #### handle bbcode removal ##################################
						$message = $ilance->bbcode->strip_bb_tags($message);
				
				// #### if we're sending as HTML, convert newlines to <br />'s
				if ($html)
				{
					$message = nl2br($message);
				}
						$subject = un_htmlspecialchars($subject, $unicode_decode);
						
						if (empty($from))
						{

								return false;
						}
						@ini_set('sendmail_from', $from);
						
						if (empty($fromname))
						{
								$fromname = '';
						}
						if ($unicode_decode)
						{
								$fromname = utf8_encode($fromname);
						}
						$fromname = un_htmlspecialchars($fromname, $unicode_decode);
						//$headers = "From: \"$fromname\" <" . $from . ">" . $delimiter . "Return-Path: " . $from . $delimiter;
						// murugan added below lini on july 19
						$headers = "From: \"$fromname\" <".$from.">" . $delimiter . "Return-Path: <".$from.">" . $delimiter;	
						$http_host = HTTP_SERVER;
						if (!$http_host)
						{
								$http_host = mb_substr(md5($message), 6, 12) . '.ilance_unknown.unknown';
						}
						$msgid = '<' . gmdate('YmdHs') . '.' . mb_substr(md5($message . microtime()), 0, 6) . rand(100000, 999999) . '@' . $http_host . '>';
						$headers .= 'Message-ID: ' . $msgid . $delimiter . "X-Priority: 3" . $delimiter . "X-Mailer: " . stripslashes(SITE_NAME) . " " . $ilance->config['ilversion'] . $delimiter;
						if ($html)
						{
								// we are sending html formatted email
								$headers .= 'Content-Type: text/html' . iif($encoding, "; charset=$encoding") . $delimiter;
						}
						else
						{
								$headers .= 'Content-Type: text/plain' . iif($encoding, "; charset=$encoding") . $delimiter;
						}
						$headers .= "Content-Transfer-Encoding: 8bit" . $delimiter;                
						
						if (defined('SMTP_ENABLED') AND SMTP_ENABLED AND defined('SMTP_HOST') AND SMTP_HOST != '' AND defined('SMTP_PORT') AND SMTP_PORT != '')
						{
								@ini_set('SMTP', SMTP_HOST);
								@ini_set('smtp_port', SMTP_PORT);
								
								$ilance->smtp = construct_object('api.smtp');
								$ilance->smtp->toemail = $toemail;
								$ilance->smtp->fromemail = $ilconfig['globalserversettings_adminemail'];
								$ilance->smtp->headers = $headers;
								$ilance->smtp->subject = $subject;
								$ilance->smtp->message = $message;

								if (!$ilance->smtp->send())

								{
										if (!defined('NO_DB'))
										{
												// could not send - queue for sending later via cron
											
											$ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "enqiry_email
	                                                (email_logid,logtype, user_id, project_id, from_email,from_name,from_ipaddress,to_email, subject, body, create_date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
													 '" . $ilance->db->escape_string($from) . "',
													 '" . $ilance->db->escape_string($fromname) . "',
													 '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'no')
													  ", 0, null, __FILE__, __LINE__);
													
										}
								}
								else
								{
										if (!defined('NO_DB'))
										{
												$sql = $ilance->db->query("
														SELECT user_id
														FROM " . DB_PREFIX . "users
														WHERE email = '" . $ilance->db->escape_string($toemail) . "'
														LIMIT 1
												", 0, null, __FILE__, __LINE__);
												if ($ilance->db->num_rows($sql) > 0)
												{
														$res = $ilance->db->fetch_array($sql);
														$uid = $res['user_id'];
												}
												unset($res);
																																		
												 $ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "enqiry_email
	                                                (email_logid,logtype, user_id, project_id, from_email,from_name,from_ipaddress,to_email, subject, body, create_date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
													 '" . $ilance->db->escape_string($from) . "',
													 '" . $ilance->db->escape_string($fromname) . "',
													  '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'yes')
													  ", 0, null, __FILE__, __LINE__);
													  
										}
								}
						}
						else
						{
								if (!mb_send_mail($toemail, $subject, $message, $headers))

								{
									
										if (!defined('NO_DB'))
										{
												// could not send - queue for sending later via cron
												
												$ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "enqiry_email
	                                                (email_logid,logtype, user_id, project_id, from_email,from_name,from_ipaddress,to_email, subject, body, create_date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
													 '" . $ilance->db->escape_string($from) . "',
													 '" . $ilance->db->escape_string($fromname) . "',
													 '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'no')
													  ", 0, null, __FILE__, __LINE__);
													  
										}
								}
								else
								{
										if (!defined('NO_DB'))
										{
												$sql = $ilance->db->query("
														SELECT user_id
														FROM " . DB_PREFIX . "users
														WHERE email = '" . $ilance->db->escape_string($toemail) . "'
														LIMIT 1
												", 0, null, __FILE__, __LINE__);
												if ($ilance->db->num_rows($sql) > 0)
												{
														$res = $ilance->db->fetch_array($sql);
														$uid = $res['user_id'];
												}
												unset($res);
																							
												$ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "enqiry_email
	                                                (email_logid,logtype, user_id, project_id, from_email,from_name,from_ipaddress,to_email, subject, body, create_date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
													 '" . $ilance->db->escape_string($from) . "',
													 '" . $ilance->db->escape_string($fromname) . "',
													 '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'yes')
													  ", 0, null, __FILE__, __LINE__);
													  
										}
								}
						}
				
				}
					}
		}
		else	
		{
				
					
	   
			
	        if (empty($toemail) OR empty($subject) OR empty($message))
	        {
	                return false;
	        }
	        
	        $ilance->bbcode = construct_object('api.bbcode');
	        
	        $pathto_sendmail = @ini_get('sendmail_path');
	        $uid = 0;
	        
	        ($apihook = $ilance->api('send_email_start')) ? eval($apihook) : false;
	        
	        $delimiter = "\n";
	        if (!$pathto_sendmail OR defined('SMTP_ENABLED') AND SMTP_ENABLED)
	        {
	                $delimiter = "\r\n";
	        }
	        
	        $toemail = trim($toemail);
	        if (!empty($toemail))
	        {
	                $toemail = un_htmlspecialchars($toemail);
	                $subject = trim($subject);
	                $message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
	                
	                if (!defined('NO_DB'))
	                {
	                        $message = $ilance->template->parse_hash('emailtemplate', array('ilpage' => $ilpage), 1, $message);
	                        if ((mb_strtolower($ilconfig['template_charset']) == 'iso-8859-1' OR !empty($ilconfig['template_charset'])) AND preg_match('/&[a-z0-9#]+;/i', $message))
	                        {
	                                $message = utf8_encode($message);
	                                $subject = utf8_encode($subject);
	                                $encoding = 'UTF-8';
	                                $unicode_decode = true;
	                        }
	                        else
	                        {
	                                $encoding = $ilconfig['template_charset'];
	                                $unicode_decode = false;
	                        }
	                        
	                        $message = un_htmlspecialchars($message, $unicode_decode); 
	                }
	                else
	                {
	                        $message = utf8_encode($message);
	                        $subject = utf8_encode($subject);
	                        $encoding = 'UTF-8';
	                        $unicode_decode = true;
	                        $message = un_htmlspecialchars($message, $unicode_decode); 
	                }
	                
	                // #### handle bbcode removal ##################################
	                $message = $ilance->bbcode->strip_bb_tags($message);
			
			// #### if we're sending as HTML, convert newlines to <br />'s
			if ($html)
			{
				$message = nl2br($message);
			}
	                $subject = un_htmlspecialchars($subject, $unicode_decode);
	                
	                if (empty($from))
	                {

	                       return false;
	                }
	                @ini_set('sendmail_from', $from);
	                
	                if (empty($fromname))
	                {

	                        $fromname = '';
	                }
	                if ($unicode_decode)
	                {
	                        $fromname = utf8_encode($fromname);
	                }
	                $fromname = un_htmlspecialchars($fromname, $unicode_decode);
	                //$headers = "From: \"$fromname\" <" . $from . ">" . $delimiter . "Return-Path: " . $from . $delimiter;
					// murugan added below lini on july 19
					$headers = "From: \"$fromname\" <".$from.">" . $delimiter . "Return-Path:<".$from.">" . $delimiter;	
	                $http_host = HTTP_SERVER;
	                if (!$http_host)
	                {
	                        $http_host = mb_substr(md5($message), 6, 12) . '.ilance_unknown.unknown';
	                }
	                $msgid = '<' . gmdate('YmdHs') . '.' . mb_substr(md5($message . microtime()), 0, 6) . rand(100000, 999999) . '@' . $http_host . '>';
	                $headers .= 'Message-ID: ' . $msgid . $delimiter . "X-Priority: 3" . $delimiter . "X-Mailer: " . stripslashes(SITE_NAME) . " " . $ilance->config['ilversion'] . $delimiter;
	                if ($html)
	                {
	                        // we are sending html formatted email
	                        $headers .= 'Content-Type: text/html' . iif($encoding, "; charset=$encoding") . $delimiter;
	                }
	                else
	                {
	                        $headers .= 'Content-Type: text/plain' . iif($encoding, "; charset=$encoding") . $delimiter;
	                }
	                $headers .= "Content-Transfer-Encoding: 8bit" . $delimiter;                
	                
	                if (defined('SMTP_ENABLED') AND SMTP_ENABLED AND defined('SMTP_HOST') AND SMTP_HOST != '' AND defined('SMTP_PORT') AND SMTP_PORT != '')
	                {
	                        @ini_set('SMTP', SMTP_HOST);
	                        @ini_set('smtp_port', SMTP_PORT);
	                        
	                        $ilance->smtp = construct_object('api.smtp');
	                        $ilance->smtp->toemail = $toemail;
	                        $ilance->smtp->fromemail = $ilconfig['globalserversettings_adminemail'];
	                        $ilance->smtp->headers = $headers;
	                        $ilance->smtp->subject = $subject;
	                        $ilance->smtp->message = $message;

							
	                         if (!$ilance->smtp->send())

	                        {
	                                if (!defined('NO_DB'))
	                                {
	                                        // could not send - queue for sending later via cron
											
											$ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "enqiry_email
	                                                (email_logid,logtype, user_id, project_id, from_email,from_name,from_ipaddress,to_email, subject, body, create_date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
													 '" . $ilance->db->escape_string($from) . "',
													 '" . $ilance->db->escape_string($fromname) . "',
													 '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'no')
													  ", 0, null, __FILE__, __LINE__);
													  
	                                }
	                        }
	                        else
	                        {
	                                if (!defined('NO_DB'))
	                                {
	                                        $sql = $ilance->db->query("
	                                                SELECT user_id
	                                                FROM " . DB_PREFIX . "users
	                                                WHERE email = '" . $ilance->db->escape_string($toemail) . "'
	                                                LIMIT 1
	                                        ", 0, null, __FILE__, __LINE__);
	                                        if ($ilance->db->num_rows($sql) > 0)
	                                        {
	                                                $res = $ilance->db->fetch_array($sql);
	                                                $uid = $res['user_id'];
	                                        }
	                                        unset($res);
	                                        						
											$ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "enqiry_email
	                                                (email_logid,logtype, user_id, project_id, from_email,from_name,from_ipaddress,to_email, subject, body, create_date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
													 '" . $ilance->db->escape_string($from) . "',
													 '" . $ilance->db->escape_string($fromname) . "',
													 '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'yes')
													  ", 0, null, __FILE__, __LINE__);
													  
	                                }
	                        }
	                }
	                else
	                {
							if (!mb_send_mail($toemail, $subject, $message, $headers))

	                        {
	                                if (!defined('NO_DB'))
	                                {
	                                        // could not send - queue for sending later via cron
	                                        
										$ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "enqiry_email
	                                                (email_logid,logtype, user_id, project_id, from_email,from_name,from_ipaddress,to_email, subject, body, create_date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
													 '" . $ilance->db->escape_string($from) . "',
													 '" . $ilance->db->escape_string($fromname) . "',
													 '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'no')
													  ", 0, null, __FILE__, __LINE__);
													  
	                                }
	                        }
	                        else
	                        {
	                                if (!defined('NO_DB'))
	                                {
	                                        $sql = $ilance->db->query("
	                                                SELECT user_id
	                                                FROM " . DB_PREFIX . "users
	                                                WHERE email = '" . $ilance->db->escape_string($toemail) . "'
	                                                LIMIT 1
	                                        ", 0, null, __FILE__, __LINE__);
	                                        if ($ilance->db->num_rows($sql) > 0)
	                                        {
	                                                $res = $ilance->db->fetch_array($sql);
	                                                $uid = $res['user_id'];
	                                        }
	                                        unset($res);
	                                        
	                                        
											
											$ilance->db->query("
	                                                 INSERT INTO " . DB_PREFIX . "enqiry_email
	                                                (email_logid,logtype, user_id, project_id, from_email,from_name,from_ipaddress,to_email, subject, body, create_date, sent)
	                                                VALUES(
	                                                NULL,
	                                                '" . $ilance->db->escape_string($logtype) . "',
	                                                '" . intval($uid) . "',
													'" . intval($project) . "',
													 '" . $ilance->db->escape_string($from) . "',
													 '" . $ilance->db->escape_string($fromname) . "',
													  '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
	                                                '" . $ilance->db->escape_string($toemail) . "',
	                                                '" . $ilance->db->escape_string($subject) . "',
	                                                '" . $ilance->db->escape_string($message) . "',
	                                                '" . DATETIME24H . "',
	                                                'yes')
													  ", 0, null, __FILE__, __LINE__);
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
