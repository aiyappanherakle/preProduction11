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
	'pmb'
);

// #### define top header nav ##################################################
$topnavlink = array(
        'messages'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'messages');

// #### require backend ########################################################
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_pmb.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[messages]" => $ilcrumbs["$ilpage[messages]"]);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'pmb-manage' AND isset($ilance->GPC['cmd']))
	{
		// #### ARCHIVE PMB ############################################
		if ($ilance->GPC['cmd'] == 'archive')
		{
			$area_title = $phrase['_pmb_message_archiving'];
			$page_title = SITE_NAME . ' - ' . $phrase['_pmb_message_archiving'];
			if (isset($ilance->GPC['event_id']) AND !empty($ilance->GPC['event_id']))
			{
				if (!isset($ilance->GPC['folder']))
				{
					foreach ($ilance->GPC['event_id'] as $value)
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "pmb_alerts
							SET to_status = 'archived'
							WHERE event_id = '" . $ilance->db->escape_string($value) . "'
								AND to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						");
					}
				}
				else 
				{
					foreach ($ilance->GPC['event_id'] as $value)
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "pmb_alerts
							SET from_status = 'archived'
							WHERE event_id = '" . $ilance->db->escape_string($value) . "'
								AND from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						");
					}					
				}
                                
				print_notice($phrase['_private_message_board_archived'], $phrase['_you_have_successfully_archived_one_or_more_private_message_boards']. "<br /><br />" .$phrase['_please_contact_customer_support'], $ilpage['messages'], $phrase['_private_message_board_menu']);
				exit();
			}
			else
			{
				print_notice($phrase['_invalid_pmb_event_id_selected'], $phrase['_your_requested_action_cannot_be_completed']. "<br /><br />" .$phrase['_in_order_to_manage_private_messages']. "<br /><br />" .$phrase['_please_contact_customer_support'], $ilpage['messages'], $phrase['_private_messages_menu']);
				exit();
			}	
		}
		
		// #### DOWNLOAD PMB IN TEXT FORMAT ############################
		else if ($ilance->GPC['cmd'] == 'txt')
		{
			$txt = SITE_NAME . " " . HTTP_SERVER . LINEBREAK;
			$txt .= $phrase['_pmb_snapshot_for']." ".$_SESSION['ilancedata']['user']['username']." ".print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . LINEBREAK . LINEBREAK;
	
			foreach ($ilance->GPC['event_id'] AS $value)
			{
				$sql = "
					SELECT a.id, a.from_id, a.to_id, a.from_status, a.to_status, p.id, p.project_id, p.event_id, p.datetime, p.message
					FROM " . DB_PREFIX . "pmb_alerts as a,
					" . DB_PREFIX . "pmb as p
					WHERE p.event_id = '" . $ilance->db->escape_string($value) . "'
						AND a.id = p.id 
					ORDER BY p.id ASC
				";
				
				$sql_rfp = $ilance->db->query("
					SELECT p.project_title FROM " . DB_PREFIX . "projects as p,
					" . DB_PREFIX . "pmb_alerts as a
					WHERE event_id='" . $ilance->db->escape_string($value) . "'
						AND a.project_id = p.project_id
				");
				$res_rfp = $ilance->db->fetch_array($sql_rfp);
				
				$result = $ilance->db->query($sql);
				
				if ($ilance->db->num_rows($result) > 0)
				{
					$title = !empty($row['project_title']) ? mb_strtoupper(stripslashes($res_rfp['project_title'])) : $phrase['_delisted'];
					$txt .= "================================================================================" . LINEBREAK;
					$txt .= $phrase['_pmb_for_upper'] . " " . $title . LINEBREAK;
					$txt .= "================================================================================" . LINEBREAK . LINEBREAK;
					
					while ($pmb = $ilance->db->fetch_array($result))
					{
						$pmb['message'] = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $pmb['message']));
						$pmb['message'] = strip_vulgar_words($pmb['message']);
						
						$txt .= "--------------------------------------------------------------------------------" . LINEBREAK;
						$txt .= $phrase['_from'] . ":\t" . fetch_user('username', $pmb['from_id']) . LINEBREAK;
						$txt .= $phrase['_to'] . ":\t" . fetch_user('username', $pmb['to_id']) . LINEBREAK;
						$txt .= $phrase['_date'] . ":\t" . print_date($pmb['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . LINEBREAK;
						$txt .= "ID:\t" . $pmb['id'] . LINEBREAK;
						$txt .= "--------------------------------------------------------------------------------" . LINEBREAK;
						$txt .= $pmb['message'] . LINEBREAK . LINEBREAK;
					}
				}
			}
			$ilance->common->download_file($txt, "pmb-txt-" . $_SESSION['ilancedata']['user']['username'] . ".txt", "text/plain");	
		}
		
		// #### DOWNLOAD PMB IN CSV FORMAT #############################
		else if ($ilance->GPC['cmd'] == 'csv')
		{
			$csv = $phrase['_title'] . "," . $phrase['_date'] . "," . $phrase['_from'] . "," . $phrase['_to'] . ",PMBID," . $phrase['_message'] . LINEBREAK;
			foreach ($ilance->GPC['event_id'] as $value)
			{
				$sql = "
					SELECT a.id, a.from_id, a.to_id, a.from_status, a.to_status, p.id, p.project_id, p.event_id, p.datetime, p.message
					FROM " . DB_PREFIX . "pmb_alerts as a,
					" . DB_PREFIX . "pmb as p
					WHERE p.event_id = '" . $ilance->db->escape_string($value) . "'
						AND a.id = p.id 
					ORDER BY p.id ASC
				";
				
				$sql_rfp = $ilance->db->query("
					SELECT p.project_title
					FROM " . DB_PREFIX . "projects AS p,
					" . DB_PREFIX . "pmb_alerts as a
					WHERE event_id = '" . $ilance->db->escape_string($value) . "'
						AND a.project_id = p.project_id
				");
				$res_rfp = $ilance->db->fetch_array($sql_rfp);
				$result = $ilance->db->query($sql);
				if ($ilance->db->num_rows($result) > 0)
				{
					$msg['project_title'] = !empty($msg['project_title']) ? mb_strtoupper(stripslashes($res_rfp['project_title'])) : $phrase['_delisted'];
					while ($pmb = $ilance->db->fetch_array($result))
					{
						$msg['datetime'] = print_date($pmb['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$msg['from_id'] = fetch_user('username', $pmb['from_id']);
						$msg['to_id'] = fetch_user('username', $pmb['to_id']);
						$msg['id'] = $pmb['id'];
						$msg['message'] = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $pmb['message']));
						$msg['message'] = strip_vulgar_words($pmb['message']);
						
						foreach ($msg as $key => $val)
						{
							if (preg_match('/\,|"/siU', $val))
							{
								$msg["$key"] = '"' . str_replace('"', '""', $val) . '"';
							}
						}
                                                
						$csv .= implode(',', $msg) . LINEBREAK;
					}
				}
			}
                        
			$ilance->common->download_file($csv, "pmb-csv-" . $_SESSION['ilancedata']['user']['username'] . ".csv", "text/x-csv");	
		}
		
		// #### DELETE PRIVATE MESSAGES ################################
		else if ($ilance->GPC['cmd'] == 'delete')
		{
			$area_title = $phrase['_deleting_private_messages'];
			$page_title = SITE_NAME . ' - ' . $phrase['_deleting_private_messages'];
	
			if (isset($ilance->GPC['event_id']) AND $ilance->GPC['event_id'] != "")
			{
				foreach ($ilance->GPC['event_id'] as $value)
				{
					$query = $ilance->db->query("
						UPDATE " . DB_PREFIX . "pmb_alerts
						SET to_status = 'deleted'
						WHERE event_id = '" . intval($value) . "'
							AND to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					");				  
					$query = $ilance->db->query("
						UPDATE " . DB_PREFIX . "pmb_alerts
						SET from_status = 'deleted'
						WHERE event_id = '" . intval($value) . "'
							AND from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					");
				}
				print_notice($phrase['_private_message_board_removed'], $phrase['_you_have_successfully_removed'] . "<br /><br />" . $phrase['_a_good_rule_of_thumb_would_be'] . "<br /><br />" . $phrase['_please_contact_customer_support'], $ilpage['messages'], $phrase['_private_messages_menu']);
				exit();
			}
			else
			{
				print_notice($phrase['_invalid_pmb_event_id_selected'], $phrase['_your_requested_action_cannot_be_completed'] . "<br /><br />" . $phrase['_in_order_to_manage_private_messages'] . "<br /><br />" . $phrase['_please_contact_customer_support'], $ilpage['messages'], $phrase['_private_messages_menu']);
				exit();
			}	
		}
	}
	else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'alert-manage' AND $ilance->GPC['cmd'] == 'delete')
	{
		$area_title = $phrase['_deleting_private_messages'];
		$page_title = SITE_NAME . ' - ' . $phrase['_deleting_private_messages'];
                
		if (isset($ilance->GPC['alert_id']) AND !empty($ilance->GPC['alert_id']))
		{
			foreach ($ilance->GPC['alert_id'] as $value)
			{
				$query = $ilance->db->query("
					DELETE FROM " . DB_PREFIX . "emaillog
					WHERE emaillogid = '" . $ilance->db->escape_string($value) . "'
						AND logtype = 'alert'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				");
			}
			
			print_notice($phrase['_private_message_board_removed'], $phrase['_you_have_successfully_removed'] . "<br /><br />" . $phrase['_a_good_rule_of_thumb_would_be'] . "<br /><br />" . $phrase['_please_contact_customer_support'], $ilpage['messages'], $phrase['_private_messages_menu']);
			exit();
		}
		else
		{
			$area_title = $phrase['_deleting_private_messages'];
			$page_title = SITE_NAME . ' - ' . $phrase['_deleting_private_messages'];
			print_notice($phrase['_invalid_pmb_event_id_selected'], $phrase['_your_requested_action_cannot_be_completed'] . "<br /><br />" . $phrase['_in_order_to_manage_private_messages'] . "<br /><br />" . $phrase['_please_contact_customer_support'], $ilpage['messages'], $phrase['_private_messages_menu']);
			exit();
		}
	}
	else
	{
		$ilance->subscription = construct_object('api.subscription');
		
		// composing new private message?
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'compose' AND $ilconfig['globalfilters_cansendpms'] AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'pmbcompose') == 'yes')
		{
			$area_title = $phrase['_messages'];
			$page_title = SITE_NAME . ' - ' . $phrase['_messages'];
		
			// some javascript above the template (not between <head>..)
			$js_start = '
<script language="javascript">
<!--
function validate_username(f)
{
        if (window.document.ilform.username.value == \'\')
        {
                alert(\'' . $phrase['_please_enter_a_username_to_dispatch_this_private_message'] . '\');
                return(false);
        }
        if (window.document.ilform.username.value == \'' . $_SESSION['ilancedata']['user']['username'] . '\')
        {
                alert(\'' . $phrase['_please_enter_a_username_other_than_yourself_to_dispatch_this_private_message'] . '\');
                return(false);
        }
        return (true);
}

function validate_subject(f)
{
        if (window.document.ilform.subject.value == \'\')
        {
                alert(\'' . $phrase['_please_enter_the_subject_to_dispatch_this_private_message'] . '\');
                return(false);
        }
        return (true);
}

function validate_message()
{
        fetch_bbeditor_data();
        return(true);
}

function validate_all()
{	
        return validate_username() && validate_subject() && validate_message();
}
//-->
</script>
';
			$username = isset($ilance->GPC['username']) ? $ilance->GPC['username'] : '';
			$subject = isset($ilance->GPC['subject']) ? handle_input_keywords($ilance->GPC['subject']) : '';
			$project_id = isset($ilance->GPC['project_id']) ? intval($ilance->GPC['project_id']) : '';
			
			$show['subject'] = (empty($subject)) ? 0 : 1;
			$show['preview'] = $show['errorusername'] = $show['subjecterror'] = $show['errorprojectid'] = 0;
			
                        // #### PREVIEW MESSAGE ################################
                        if (isset($ilance->GPC['preview']) AND !empty($ilance->GPC['preview']))
                        {
				$show['preview'] = $show['errorusername'] = $show['errorprojectid'] = 1;
				$project_id = isset($ilance->GPC['project_id']) ? $ilance->GPC['project_id'] : '0';
				
				// #### PROCESS PREVIEW POST ###################################
				$message = (!empty($ilance->GPC['message'])) ?  $ilance->GPC['message'] : '';
				
				// #### PREVIEW IN HTML ########################################
				// our text is already converted to bbcode so for preview, we will parse it back to html
				$ilance->bbcode = construct_object('api.bbcode');
				$descriptionpv = $ilance->bbcode->bbcode_to_html($message);
				
				// #### RELOAD INTO WYSIWYG ####################################
				$wysiwyg_area = print_wysiwyg_editor('message', $message, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);				
				$subjectpv = handle_input_keywords($subject);
				
				if (fetch_userid($ilance->GPC['username']) > 0)
				{
					$show['errorusername'] = 0;
				}
				else
				{
					$username = '';
				}
				
				if (isset($project_id) AND $project_id > 0)
				{
					if (is_valid_project_id($project_id))
					{
						$show['errorprojectid'] = 0;
					}
				}
				else
				{
					$show['errorprojectid'] = 0;
				}				
			}
			
			// #### SUBMIT MESSAGE #################################
			else if (isset($ilance->GPC['submit']) AND !empty($ilance->GPC['submit']))
			{
				$show['preview'] = 0;
				$show['errorusername'] = $show['errorprojectid'] = 1;
                                
				$project_id = isset($ilance->GPC['project_id']) ? $ilance->GPC['project_id'] : '0';
				
				$userid = fetch_userid($ilance->GPC['username']);
				if ($userid > 0)
				{
					$show['errorusername'] = 0;
				}
				else
				{
					$username = '';
				}
                                
				if (isset($project_id) AND $project_id > 0)
				{
					if (is_valid_project_id($project_id))
					{
						$show['errorprojectid'] = 0;
					}
				}
				else
				{
					$show['errorprojectid'] = 0;
				}
                                
				if ($show['errorusername'] == 0 AND $show['errorprojectid'] == 0)
				{
					// #### compose and send email for new private message!
					$message = (!empty($ilance->GPC['message'])) ? $ilance->GPC['message'] : '';
					
					compose_private_message($userid, intval($ilance->GPC['from_id']), $subject, $message, $project_id);
					
					refresh(HTTP_SERVER . $ilpage['messages'] . '?cmd=management&pmbfolder=sent&sent=1');
					exit();
				}
				else
				{
					$show['preview'] = $show['errorusername'] = 1;
                                        
					// #### PROCESS PREVIEW POST ###################################
					// we assume the user has just posted his message and a preview is being requested
					// we will determine if the wysiwyg editor is enabled before we decide what to do
					$message = (!empty($ilance->GPC['message'])) ? $ilance->GPC['message'] : '';
					
					// #### PREVIEW IN HTML ########################################
					// our text is already converted to bbcode so for preview, we will parse it back to html
					$ilance->bbcode = construct_object('api.bbcode');
					$descriptionpv = $ilance->bbcode->bbcode_to_html($message);
					
					// #### RELOAD INTO WYSIWYG ####################################
					$wysiwyg_area = print_wysiwyg_editor('message', $message, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);					
					$subjectpv = handle_input_keywords($subject);
				}
			}
			else
			{
				$wysiwyg_area = print_wysiwyg_editor('message', '', 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
			}
			
			$pprint_array = array('js_start','project_id','username','subject','message','descriptionpv','subjectpv','wysiwyg_area','pmbfolders','advchat','pmbadv','alertsadv','chatsadv','advtoday','advyesterday','advarchived','advhistory','site_email','collapseobj_alerthistory','collapseobj_chats','collapseobj_pmbs','collapseobj_alerts','collapseobj_chathistory','collapseobj_pmbstoday','collapseobj_pmbsyesterday','collapseobj_pmbsarchived','count_archived','count_yesterday','count_alerts','count_transcripts','count_today','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
			
			$ilance->template->fetch('main', 'messages_compose.html');
			$ilance->template->parse_if_blocks('main');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->pprint('main', $pprint_array);
			exit();	
		}
		
		$area_title = $phrase['_messages'];
		$page_title = SITE_NAME . ' - ' . $phrase['_messages'];

		// construct breadcrumb trail
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = $phrase['_my_cp'];
		$navcrumb[""] = $phrase['_messages'];
	
		$pmb['folder'] = 'inbox';
		if (isset($ilance->GPC['pmbfolder']) AND $ilance->GPC['pmbfolder'] != "")
		{
			$pmb['folder'] = trim(mb_strtolower($ilance->GPC['pmbfolder']));
		}
		
		// pmb gauge
		$pmbgauge = print_pmb_gauge($_SESSION['ilancedata']['user']['userid']);
		
		// folders
		switch ($pmb['folder'])
		{
			case 'inbox':
			{
				$pmfoldername = $phrase['_received_inbox'];
                                
				$sql = "SELECT a.id, a.event_id, a.project_id, a.from_id, a.to_id, a.from_status, a.to_status, a.track_status, p.subject, p.message, p.datetime, MAX(p.datetime) AS postdate
				FROM " . DB_PREFIX . "pmb_alerts AS a,
				" . DB_PREFIX . "pmb AS p
				WHERE a.to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        AND a.to_status != 'deleted'
                                        AND a.to_status != 'archived'
                                        AND a.event_id = p.event_id
				GROUP BY a.event_id
				ORDER BY postdate DESC, p.id DESC";
				break;
			}
			case 'sent':
			{
				$pmfoldername = $phrase['_sent_outbox'];
                                
				$sql = "SELECT a.id, a.event_id, a.project_id, a.from_id, a.to_id, a.from_status, a.to_status, a.track_status, p.subject, p.message, p.datetime, MAX(p.datetime) AS postdate
				FROM " . DB_PREFIX . "pmb_alerts AS a,
				" . DB_PREFIX . "pmb AS p
				WHERE a.from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        AND a.from_status != 'deleted'
                                        AND a.from_status != 'archived'
                                        AND a.event_id = p.event_id
				GROUP BY a.event_id
				ORDER BY postdate DESC, p.id DESC";
				break;
			}            
			case 'archived':
			{
				$pmfoldername = $phrase['_archived'];
                                
				$sql = "SELECT a.id, a.event_id, a.project_id, a.from_id, a.to_id, a.from_status, a.to_status, a.track_status, p.subject, p.message, p.datetime, MAX(p.datetime) AS postdate
				FROM
				" . DB_PREFIX . "pmb_alerts AS a,
				" . DB_PREFIX . "pmb AS p
				WHERE
				(
					a.from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND a.from_status = 'archived'
					OR
					a.to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND a.to_status = 'archived'
				)
                                        AND a.event_id = p.event_id
				GROUP BY a.event_id
				ORDER BY postdate DESC, p.id DESC";
				break;
			}            
			case 'deleted': //admin only
			{
				$pmfoldername = $phrase['_deleted'];
				break;
			}
		}
        
		$result = $ilance->db->query($sql);
		$count = @$ilance->db->num_rows($result);
		
		$pmbfolders = '
		<select name="pmbfolder" style="font-family: verdana">
		<optgroup label="'.$phrase['_inbox'].'">
		    <option value="inbox"'; if ($pmb['folder'] == "inbox") { $pmbfolders .= ' selected="selected"'; } $pmbfolders .= '>' . $phrase['_received_messages'] . ' (' . fetch_pmb_count($_SESSION['ilancedata']['user']['userid'], 'received') . ')</option>
		</optgroup>
		<optgroup label="'.$phrase['_other'].'">
		    <option value="sent"'; if ($pmb['folder'] == "sent") { $pmbfolders .= ' selected="selected"'; } $pmbfolders .= '>' . $phrase['_sent_messages'] . ' (' . fetch_pmb_count($_SESSION['ilancedata']['user']['userid'], 'sent') . ')</option>
		    <option value="archived"'; if ($pmb['folder'] == "archived") { $pmbfolders .= ' selected="selected"'; } $pmbfolders .= '>' . $phrase['_archived_messages'] . ' (' . fetch_pmb_count($_SESSION['ilancedata']['user']['userid'], 'archived') . ')</option>
		</optgroup>
		</select>';
		
		// #### PRIVATE MESSAGE BOARDS #################################
		if ($ilance->db->num_rows($result) > 0)
		{
			$altrows = 0;
			$row_count = 0;
			while ($row = $ilance->db->fetch_array($result))
			{
				// last posted message within this board info bit
				$sql_lastpost = $ilance->db->query("
					SELECT from_id, to_id
					FROM " . DB_PREFIX . "pmb_alerts
					WHERE (to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
						AND project_id = '" . $row['project_id'] . "'
					ORDER BY id DESC
				");
				if ($ilance->db->num_rows($sql_lastpost) > 0)
				{
					$res_lastpost = $ilance->db->fetch_array($sql_lastpost);
				}
				$row['lastpost'] = print_username($res_lastpost['from_id'], 'plain');
				$row['date_posted'] = print_date($row['postdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 1);
				$altrows++;
				if (floor($altrows/2) == ($altrows/2))
				{
					$row['class'] = 'alt2';
				}
				else
				{
					$row['class'] = 'alt1';
				}

				$sql_projects = $ilance->db->query("
					SELECT project_id, project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $row['project_id'] . "'
					ORDER BY project_id DESC
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql_projects) > 0)
				{
					$projects_array = $ilance->db->fetch_array($sql_projects);
					$row['relatedauction'] = 'RE: <strong>' . stripslashes($projects_array['project_title']).'</strong>';
				}
				else 
				{
					$projects_array['project_id'] = '0';
					$projects_array['project_title'] = '';
					$row['relatedauction'] = '';
				}

				// check for attachments
				$row['attach'] = '';
				$sql_attachments = $ilance->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "attachment
					WHERE attachtype = 'pmb'
						AND project_id = '" . $row['project_id'] . "'
						AND visible = '1'
				");
				if ($ilance->db->num_rows($sql_attachments) > 0)
				{
					$res = $ilance->db->fetch_array($sql_attachments);
					if ($res['count'] > 0)
					{
						$row['attach'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" border="0" alt="'.$res['count'].' '.$phrase['_attachments'].'" /> ';
					}
				}

				if ($row['to_id'] == $_SESSION['ilancedata']['user']['userid'])
				{
					$toID = $row['from_id'];
					$fromID = $_SESSION['ilancedata']['user']['userid'];
                                        $row['date_posted2'] = print_date($row['postdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 1);
				}
				else
				{
					$toID = $row['to_id'];
					$fromID = $_SESSION['ilancedata']['user']['userid'];
                                        $row['date_posted2'] = print_date($row['postdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0, 0, 1);
				}
                                
				// from user
				$row['from'] = print_username($row['from_id'], 'plain');
				$row['to'] = print_username($toID, 'plain');

                                $crypted = array(
                                        'event_id' => $row['event_id'],
                                        'project_id' => $projects_array['project_id'],
                                        'from_id' => $fromID,
                                        'to_id' => $toID
                                );
                                //print_r($crypted); exit;
                                $row['subject'] = '<a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;">' . fetch_last_pmb_subject($row['project_id'], $row['event_id'], $_SESSION['ilancedata']['user']['userid']) . '</a>';
                                $row['posts'] = fetch_pmb_posts($row['project_id'], $row['event_id']);
                                $row['unread'] = fetch_unread_pmb_posts($row['project_id'], $row['event_id'], $_SESSION['ilancedata']['user']['userid']);
				$row['action'] = '<input type="checkbox" name="event_id[]" value="' . $row['event_id'] . '" />';
				$pmbs[] = $row;
				$row_count++;
			}
		}
		else
		{
			$show['no_pmbs'] = true;
		}

		// #### ALERTS HISTORY #########################################
		//counter for page
		 $ilconfig['globalfilters_maxrowsdisplaysubscribers']='5';
                 $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
				 
				  if ($ilconfig['globalauctionsettings_seourls'])
				   $scriptpageprevnext = $ilpage['messages'].'?mode=1';
				  else
				    $scriptpageprevnext = $ilpage['messages'].'?mode=1';
		
		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
				 {
					$ilance->GPC['page'] = 1;
				 }
				 else
				 {
					$ilance->GPC['page'] = intval($ilance->GPC['page']);
				 }
	        
	     
		$resulting = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "emaillog
			WHERE logtype = 'alert'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			ORDER BY emaillogid DESC 
		"); 		    
					
		$result = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "emaillog
			WHERE logtype = 'alert'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			ORDER BY emaillogid DESC LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . ",".$ilconfig['globalfilters_maxrowsdisplaysubscribers']."
		");
		$count_alerts = @$ilance->db->num_rows($result);
		if ($count_alerts == 0)
		{
			$count_alerts = '0';	
		}
		if ($ilance->db->num_rows($result) > 0)
		{
		
		$number = (int)$ilance->db->num_rows($resulting);
		
			$altrows = 0;
			$row_count = 0;
			while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				// last poster info
				$sql_max = $ilance->db->query("
					SELECT date
					FROM " . DB_PREFIX . "emaillog
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					ORDER BY emaillogid DESC
				");
				if ($ilance->db->num_rows($sql_max) > 0)
				{
					$res_max = $ilance->db->fetch_array($sql_max, DB_ASSOC);
				}
				$altrows++;
				if (floor($altrows/2) == ($altrows/2))
				{
					$row['class'] = 'alt2';
				}
				else
				{
					$row['class'] = 'alt1';
				}
				
				$row['subject'] = stripslashes($ilance->common->xss_clean($row['subject']));
				$row['body'] = strip_vulgar_words($row['body'], false);
				$row['body'] = nl2br($row['body']);
				$row['date'] = print_date($row['date'], '%b. %d, %Y', 0, 0);
				$row['action'] = '<input type="checkbox" name="alert_id[]" value="' . $row['emaillogid'] . '" />';
				$alerthistory[] = $row;
				$row_count++;
			}
		}
		else
		{
			$show['no_alerthistory'] = true;
		}
		
		if ($pmb['folder'] == 'sent')
		{
			$show['pmb_folder_sent'] = true;
		}

		$headinclude .= "
<script language=\"Javascript\">
<!--
checked = false;
function check_uncheck_all(formid)
{
        if (checked == false)
        {
                checked = true
        }
        else
        {
                checked = false
        }
        for (var i = 0; i < fetch_js_object(formid).elements.length; i++)
        {
                fetch_js_object(formid).elements[i].checked = checked;
        }
}
//-->
</script>
";
        $prof = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);

		$pprint_array = array('prof','pmbgauge','pmfoldername','pmbfolders','advchat','pmbadv','alertsadv','chatsadv','advtoday','advyesterday','advarchived','advhistory','site_email','collapseobj_alerthistory','collapseobj_chats','collapseobj_pmbs','collapseobj_alerts','collapseobj_chathistory','collapseobj_pmbstoday','collapseobj_pmbsyesterday','collapseobj_pmbsarchived','count_archived','count_yesterday','count_alerts','count_transcripts','count_today','input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		$ilance->template->fetch('main', 'messages.html');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('pmbs','chathistory','alerthistory'));
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
else
{	
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['messages'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>