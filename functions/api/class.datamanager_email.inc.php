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

if (!class_exists('datamanager'))
{
	exit;
}

/**
* Email data manager class to handle the majority of sending emails in ILance.  This
* is more or less a wrapper for our send_email() function that does all the email sending and detection
* for the SMTP wrapper as well (if applicable).
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class datamanager_email extends datamanager
{
	// to email
	var $mail = null;
	
	// from email
	var $from = null;
        
        // from name (optional) ie: Peter <peter@ilance.com>
        var $fromname = '';
	
	// language
	var $slng = null;
	
	// subject body
	var $subject = null;
	
	// message body
	var $message = null;
        
        // email id
        var $emailid = 0;
	
	// email department id
	var $departmentid = 0;
        
        // are we sending email as html?
        var $dohtml = false;
        
        // default email logtype
        var $logtype = 'alert';
		
		// project id Murugan changes on apr 5
		var $project = 0;

        function datamanager_email(&$registry)
	{
		parent::datamanager($registry);
	}
	
	function get($varname)
	{
		if (!empty($varname))
		{
			if (empty($this->slng))
			{
				$this->slng = 'eng';
			}
                        
			$sql = $this->dm->db->query("
				SELECT id, subject_" . $this->slng . " AS subject, message_" . $this->slng . " AS message, departmentid
				FROM " . DB_PREFIX . "email
				WHERE varname = '" . $this->dm->db->escape_string($varname) . "'
			");
			if ($this->dm->db->num_rows($sql) > 0)
			{
				$res = $this->dm->db->fetch_array($sql, DB_ASSOC);				
				$this->emailid = stripslashes(trim($res['id']));
				$this->subject = stripslashes(trim($res['subject']));
				$this->message = stripslashes(trim($res['message']));
				$this->departmentid = $res['departmentid'];
			}
			unset($res);
		}
	}
	
	function set($toconvert = array())
	{
		global $ilconfig;
                
		if (isset($toconvert) AND is_array($toconvert))
		{
			foreach ($toconvert AS $search => $replace)
			{
				if (!empty($search))
				{
					$this->subject = str_replace("$search", $replace, $this->subject);
					$this->message = str_replace("$search", $replace, $this->message);
				}
			}
			unset($search, $replace);
		}
		
		$commonfields = array(
			'{{site_name}}' => SITE_NAME,
			'{{site_email}}' => SITE_EMAIL,
			'{{site_phone}}' => SITE_PHONE,
			'{{site_address}}' => SITE_ADDRESS,
			'{{http_server_admin}}' => HTTP_SERVER_ADMIN,
			'{{https_server_admin}}' => HTTPS_SERVER_ADMIN,
			'{{https_server}}' => HTTPS_SERVER,
			'{{http_server}}' => HTTP_SERVER,
			'{{generate_date}}' => print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
			'{{email_id}}' => $this->emailid,
		);
		
		foreach ($commonfields AS $search => $replace)
		{
			if (!empty($search))
			{
				$this->subject = str_replace("$search", $replace, $this->subject);
				$this->message = str_replace("$search", $replace, $this->message);
			}
			unset($search, $replace);
		}
	}
	
	function send()
	{
                global $ilance;
                if(defined('SITE_TO_EMAIL'))
		$this->mail=SITE_TO_EMAIL;
		if (empty($this->from))
		{
			$this->from = SITE_EMAIL;
		}
                
                if (empty($this->fromname))
                {
                        $this->fromname = SITE_NAME;
                }
		
 
                
                if (!empty($this->mail))
                {
                        if (is_array($this->mail))
                        { // handle sending the same email template to multiple receipents
                                foreach ($this->mail AS $email)
                                {
                                        if (is_valid_email($email))
                                        {
                                                send_email($email, $this->subject, $this->message, $this->from, $this->fromname, $this->dohtml, $this->logtype, $this->project);
                                        }
                                }
                        }
                        else
                        {
                                if (is_valid_email($this->mail))
                                {
                                        send_email($this->mail, $this->subject, $this->message, $this->from, $this->fromname, $this->dohtml, $this->logtype, $this->project);
                                }
                        }
                }
                
		$this->mail = $this->subject = $this->message = $this->from = $this->fromname = $this->emailid = $this->departmentid = null;
                $this->logtype = 'alert';
				$this->project = 0;
	}	
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
