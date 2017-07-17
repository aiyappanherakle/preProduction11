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
        'pmb',
        'buying',
        'selling',
        'preferences'
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
define('LOCATION', 'pmb');

// #### require backend ########################################################
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_pmb.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[pmb]" => $ilcrumbs["$ilpage[pmb]"]);

$area_title = $phrase['_posting_private_message'];
$page_title = SITE_NAME . ' - ' . $phrase['_posting_private_message'];

$ilance->GPC['decrypted'] = isset($ilance->GPC['crypted']) ? decrypt_url($ilance->GPC['crypted']) : '';

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND !empty($ilance->GPC['decrypted']))
{
	global $headinclude;
        
	// does admin request pmb removal?
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'remove-post' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
                {
                        //echo 'removing post?!?!!?';
                        remove_pmb_post(intval($ilance->GPC['id']));
                        // fix and include refresh bit
                }
	}

        // fetch an existing private message event
        $pmb['noproject'] = 0;
        if (!empty($ilance->GPC['decrypted']['event_id']) AND $ilance->GPC['decrypted']['event_id'] > 0)
        {
                if ($ilance->GPC['decrypted']['project_id'] == '0')
                {
                        $pmb['noproject'] = 1;
                }
        }
        else
        {
                $ilance->GPC['decrypted']['event_id'] = fetch_pmb_eventid($ilance->GPC['decrypted']['project_id'], $ilance->GPC['decrypted']['from_id'], $ilance->GPC['decrypted']['to_id']);
        }
        
        if ($pmb['noproject'])
        {
                // fake PMB into thinking a project is open
                $res['status'] = 'open';
                $res['project_state'] = 'service';
                $res['project_title'] = $phrase['_non_auction_related'];
                $res['cid'] = '0';
        }
        else
        {
                // be sure project is not delisted, cancelled, etc
                $sql = $ilance->db->query("
                        SELECT status, project_state, project_title, cid
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($ilance->GPC['decrypted']['project_id']) . "'
                        LIMIT 1
                ");
                $res = $ilance->db->fetch_array($sql);        
        }
        
        $cmd = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
        
        if (!empty($ilance->GPC['decrypted']['isadmin']))
        {
                $ilance->GPC['decrypted']['isadmin'] = intval($ilance->GPC['decrypted']['isadmin']);
        }
        else
        {
                $ilance->GPC['decrypted']['isadmin'] = 0;
        }
        
        // #### SUBMIT NEW PRIVATE MESSAGE #####################################
        if (isset($cmd) AND $cmd == '_process-pm' AND isset($ilance->GPC['submit']) AND !empty($ilance->GPC['submit']) AND !isset($ilance->GPC['preview']) AND isset($ilance->GPC['message']) AND !empty($ilance->GPC['message']))
        {
                
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
                {
                        // admin is only one viewing
                        $ilance->GPC['decrypted']['from_id'] = $_SESSION['ilancedata']['user']['userid'];
                        $ilance->GPC['decrypted']['isadmin'] = '1';
                }
                else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
                {
                        // user viewing
                        $ilance->GPC['decrypted']['from_id'] = $_SESSION['ilancedata']['user']['userid'];
                }
                
                $message = $ilance->GPC['message'];
                $subject = $ilance->GPC['subject'];
                if($ilconfig['globalfilters_emailfilterpmb'])
                {
                	$subject = strip_email_words($subject);
                	$message = strip_email_words($message);
                }
                if($ilconfig['globalfilters_domainfilterpmb'])
                {
                	$subject = strip_domain_words($subject);
                	$message = strip_domain_words($message);
                }
                

                // #### COMPOSE NEW PRIVATE MESSAGE ############
                compose_private_message(intval($ilance->GPC['decrypted']['to_id']), $ilance->GPC['from_id'], $subject, $message, $ilance->GPC['decrypted']['project_id'], $ilance->GPC['decrypted']['event_id'], $ilance->GPC['decrypted']['isadmin']);
                
                if (!empty($ilance->GPC['decrypted']['isadmin']) AND $ilance->GPC['decrypted']['isadmin'] == '1')
                {
                        $ilance->GPC['decrypted']['isadmin'] = '1';
                        $ilance->GPC['decrypted']['from_id'] = $_SESSION['ilancedata']['user']['userid'];
                }
                else
                {
                        $ilance->GPC['decrypted']['isadmin'] = '0';
                        $ilance->GPC['decrypted']['from_id'] = $_SESSION['ilancedata']['user']['userid'];
                }
                
                $ilance->GPC['decrypted'] = array(
                        'event_id'  => intval($ilance->GPC['decrypted']['event_id']),
                        'project_id' => intval($ilance->GPC['decrypted']['project_id']),
                        'from_id' => intval($ilance->GPC['decrypted']['from_id']),
                        'to_id' => intval($ilance->GPC['decrypted']['to_id']),
                        'isadmin' => $ilance->GPC['decrypted']['isadmin']
                );
                
                refresh($ilpage['pmb'] . '?crypted=' . encrypt_url($ilance->GPC['decrypted']) . '&amp;noonload=1');
                exit();
                
        }
        
        // #### PREVIEW PRIVATE MESSAGE ########################################
        else if (isset($cmd) AND $cmd == '_process-pm' AND !isset($ilance->GPC['submit']) AND isset($ilance->GPC['preview']) AND !empty($ilance->GPC['preview']))
        {
                $area_title = $phrase['_posting_private_message'] . ' - ' . $phrase['_preview'] . '<div class="smaller">' . $phrase['_to_upper'] . ': <strong>' . fetch_user('username', $ilance->GPC['to_id']) . '</strong></div>';
                $page_title = SITE_NAME . ' - ' . $phrase['_posting_private_message'] . ' - ' . $phrase['_preview'];

                $pmb['preview_mode'] = true;
                $pmb['previewsubject'] = $pmb['subject'] = '';

                if (isset($ilance->GPC['subject']) AND !empty($ilance->GPC['subject']))
                {
                        $pmb['subject'] = strip_tags($ilance->GPC['subject']);
                        $pmb['previewsubject'] = $pmb['subject'] . '<br /><br />';
                        if($ilconfig['globalfilters_emailfilterpmb'])
                        {
                        	$pmb['subject'] = strip_email_words($pmb['subject']);
                        }
                        if($ilconfig['globalfilters_domainfilterpmb'])
                        {
                        	$pmb['subject'] = strip_domain_words($pmb['subject']);
                        }
                }
                
                // #### PROCESS PREVIEW POST ###################################
                // we assume the user has just posted his message and a preview is being requested
                // we will determine if the wysiwyg editor is enabled before we decide what to do
                if (!empty($ilance->GPC['message']))
                {
                        $message = $ilance->GPC['message'];
                        if($ilconfig['globalfilters_emailfilterpmb'])
                        {
                        	$message = strip_email_words($message);
                        }
                        if($ilconfig['globalfilters_domainfilterpmb'])
                        {
                        	$message = strip_domain_words($message);
                        }
                }                
                
                // #### PREVIEW IN HTML ########################################
                // our text is already converted to bbcode so for preview, we will parse it back to html
                $ilance->bbcode = construct_object('api.bbcode');
                $pmb['preview'] = $ilance->bbcode->bbcode_to_html($ilance->GPC['message']);

		// #### RELOAD INTO WYSIWYG ####################################
                $wysiwyg_area = print_wysiwyg_editor('message', $message, 'bbeditor', $ilconfig['globalfilters_pmbwysiwyg'], $ilconfig['globalfilters_pmbwysiwyg'], $ishtml = false, $width = '', $height = '');

                $ilance->template->load_popup('popupheader', 'popup_header.html');
                $ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time'));
        }
        else
        {
                $area_title = $phrase['_posting_private_message'] . '<div class="smaller">' . $phrase['_to_upper'] . ': <strong>' . fetch_user('username', $ilance->GPC['decrypted']['to_id']) . '</strong></div>';
                $page_title = SITE_NAME . ' - ' . $phrase['_posting_private_message'];
		
		$wysiwyg_area = print_wysiwyg_editor('message', '', 'bbeditor', $ilconfig['globalfilters_pmbwysiwyg'], $ilconfig['globalfilters_pmbwysiwyg'], $ishtml = false, $width = '', $height = '');
                
                $ilance->template->load_popup('popupheader', 'popup_header.html');
                $ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time'));
        }
        
        // admin viewing access to remove pmbs?
        $isadminviewing = 0;
        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
        {
                $isadminviewing = 1;
        }
?>

<script language="JavaScript" type="text/javascript">
<!--
function closePMB(href) 
{
	window.opener.location=href
	window.close();
}
function validate_subject(f)
{
	if (window.document.ilform.subject.value == '')
	{
		alert('Please enter the subject to dispatch this private message.');
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
	return validate_subject() && validate_message();
}
//-->
</script>

<?php
        $pmb['attachid'] = time();
        $pmbaction = '';
        if (!empty($ilance->GPC['decrypted']['status']) AND $ilance->GPC['decrypted']['status'] == 'archived') 
        {
                $pmbaction = 'disabled';
        }
        if ($res['project_state'] == 'service') 
        {
                $auctiontype = $ilpage['rfp'];
        }
        else if ($res['project_state'] == 'product') 
        {
                $auctiontype = $ilpage['merch'];
        }
?>
<table border="0" cellpadding="12" cellspacing="0" width="99.9%">
<tr>
	<td>
        <form name="ilform" method="post" accept-charset="UTF-8" style="margin: 0px" onsubmit="return validate_all();">
        <input type="hidden" name="cmd" value="_process-pm" />
<?php
        if (isset($ilance->GPC['decrypted']['isadmin']) AND $ilance->GPC['decrypted']['isadmin'] == 1)
        {
?>
        <input type="hidden" name="isadmin" value="1" />
<?php
        }
?>
        <input type="hidden" name="project_id" value="<?php echo intval($ilance->GPC['decrypted']['project_id']); ?>" />
        <input type="hidden" name="from_id" value="<?php echo intval($ilance->GPC['decrypted']['from_id']); ?>" />
        <input type="hidden" name="to_id" value="<?php echo intval($ilance->GPC['decrypted']['to_id']); ?>" />
        <input type="hidden" name="event_id" value="<?php echo intval($ilance->GPC['decrypted']['event_id']); ?>" />
        <input type="hidden" name="pmbfileid" value="<?php echo $pmb['attachid']; ?>" />
<?php
        if (isset($pmb['preview_mode']) AND $pmb['preview_mode'] == 1)
        {
?>
        <input type="hidden" name="subject" value="<?php echo $pmb['subject']; ?>" />
<?php
        }
?>
        <div class="block-wrapper">

                <div class="block">
                
                                <div class="block-top">
                                                <div class="block-right">
                                                                <div class="block-left"></div>
                                                </div>
                                </div>
                                
                                <div class="block-header"><span style="float:right"><span class="blue"><a href="#" onclick="window.location.reload()"><?php echo $phrase['_refresh_pmb']; ?></a></span></span><?php echo $phrase['_private_messages']; ?></div>
                                <div class="block-content-yellow" style="padding:9px"><div class="smaller"><?php echo $phrase['_pmb_for']; ?> <?php if ($ilance->GPC['decrypted']['project_id'] == 0) { echo $phrase['_non_auction_related']; } else { ?><span class="blue"><a href="<?php echo $auctiontype; ?>?id=<?php echo $ilance->GPC['decrypted']['project_id']; ?>" target="_blank"><?php echo stripslashes($res['project_title']); ?></a></span><?php } ?></div></div>
                                <div class="block-content" style="padding:0px">
                                        
        <table width="100%" border="0" cellspacing="<?php echo $ilconfig['table_cellspacing']; ?>" cellpadding="<?php echo $ilconfig['table_cellpadding']; ?>" align="center" dir="<?php echo $ilconfig['template_textdirection']; ?>">
        <tr>
                <td align="left" class="alt1">
<?php
        if (empty($pmb['preview_mode']) OR $pmb['preview_mode'] != true)
        {
?>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" dir="<?php echo $ilconfig['template_textdirection']; ?>">
                        <tr height="40" valign="top">
                                <td colspan="2">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" dir="<?php echo $ilconfig['template_textdirection']; ?>">
<?php
                                        if (isset($ilance->GPC['decrypted']['isadmin']) AND $ilance->GPC['decrypted']['isadmin'] == 1)
                                        {
                                                $querymessages = $ilance->db->query("
                                                        SELECT alert.id, alert.from_id, alert.to_id, alert.from_status, alert.to_status, alert.isadmin, pm.project_id, pm.event_id, pm.datetime, pm.message, pm.subject
                                                        FROM " . DB_PREFIX . "pmb_alerts as alert,
                                                        " . DB_PREFIX . "pmb as pm
                                                        WHERE alert.id = pm.id
                                                                AND alert.project_id = '" . $ilance->GPC['decrypted']['project_id'] . "'
                                                                AND alert.event_id = '" . $ilance->GPC['decrypted']['event_id'] . "'
                                                                AND alert.event_id = pm.event_id
                                                                AND alert.project_id = pm.project_id
                                                        ORDER BY pm.id DESC
                                                ");
                                        }
                                        else
                                        {
                                                $querymessages = $ilance->db->query("
                                                        SELECT alert.id, alert.from_id, alert.to_id, alert.from_status, alert.to_status, alert.isadmin, pm.project_id, pm.event_id, pm.datetime, pm.message, pm.subject
                                                        FROM " . DB_PREFIX . "pmb_alerts as alert,
                                                        " . DB_PREFIX . "pmb as pm
                                                        WHERE alert.id = pm.id
                                                                AND (alert.from_id = '" . $ilance->GPC['decrypted']['from_id'] . "' AND alert.to_id = '" . $ilance->GPC['decrypted']['to_id'] . "' OR alert.from_id = '" . $ilance->GPC['decrypted']['to_id'] . "' AND alert.to_id = '" . $ilance->GPC['decrypted']['from_id'] . "')
                                                                AND alert.project_id = '" . $ilance->GPC['decrypted']['project_id'] . "'
                                                                AND alert.event_id = '" . $ilance->GPC['decrypted']['event_id'] . "'
                                                                AND alert.event_id = pm.event_id
                                                                AND alert.project_id = pm.project_id
                                                        ORDER BY pm.id DESC
                                                ");
                                        }
                                        
                                        if ($ilance->db->num_rows($querymessages) > 0)
                                        {
                                                $rows = $item = 0;
                                                $ilance->bbcode = construct_object('api.bbcode');
                                                
                                                while ($resmessages = $ilance->db->fetch_array($querymessages))
                                                {
                                                        $rows++;
                                                        $item++;
                                                        if (empty($ilance->GPC['decrypted']['isadmin']) OR $ilance->GPC['decrypted']['isadmin'] == 0)
                                                        {
                                                                update_pmb_tracker($resmessages['id'], $_SESSION['ilancedata']['user']['userid']);
                                                        }
                                                        if (floor($rows/2) == ($rows/2)) 
                                                        {
                                                                $tr = '2';
                                                        }
                                                        else 
                                                        {
                                                                $tr = '';
                                                        }
                                
                                                        if (!empty($resmessages['subject']) AND $resmessages['subject'] != 'No Subject') 
                                                        {
                                                                $pmb['subject'] = stripslashes($resmessages['subject']);
                                                        }
                                                        else 
                                                        {
                                                                $pmb['subject'] = '';
                                                        }
                                                        $pmb['subject'] = strip_vulgar_words($pmb['subject']);
                                                        
                                                        
                                                        if (empty($resmessages['message']))
                                                        {
                                                                $pmb['message'] = '(' . $phrase['_no_message_posted'] . ')';
                                                        }	
                                                        else
                                                        {
                                                                $pmb['message'] = strip_vulgar_words($resmessages['message']);
                                                                $pmb['message'] = $ilance->bbcode->bbcode_to_html($pmb['message']);
                                                                $pmb['message'] = print_string_wrap($pmb['message'], 100);
                                                        }
?>
                                        <tr class="alt1">
                                                <td colspan="2" valign="top">
                                                        <div>
                                                                <table cellpadding="<?php echo $ilconfig['table_cellpadding']; ?>" cellspacing="<?php echo $ilconfig['table_cellspacing']; ?>" border="0" width="100%" align="center" dir="<?php echo $ilconfig['template_textdirection']; ?>">
                                                                <tr>
                                                                        <td><span style="float:right" class="smaller gray">&nbsp;<?php echo $phrase['_message']; ?> #<?php echo $resmessages['id']; ?> &nbsp;</span><span class="smaller"><?php echo $phrase['_posted'] . ': <span class="gray">' . print_date($resmessages['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 1, 1, 0, 1); ?></span></span></td>
                                                                </tr>
                                                                <tr>
                                                                        <td style="padding:0px">
                                                                                <!-- customer info -->
                                                                                <table cellpadding="0" cellspacing="6" border="0" width="100%" dir="<?php echo $ilconfig['template_textdirection']; ?>">
                                                                                <tr>
                                                                                        <td nowrap="nowrap"><span class="header"><?php echo fetch_user('username', $resmessages['from_id']); ?></span><div class="smaller gray"><?php echo print_online_status($resmessages['from_id']); ?></div></td>
                                                                                        <td width="100%" align="right"><span class="blue"><a href="#reply"><?php echo $phrase['_post_new_reply']; ?></a></span>
<?php
                                                        // just in case admin is viewing
                                                        if (isset($isadminviewing) AND $isadminviewing)
                                                        {
?>
                                                                                                <div class="smaller"><a href="<?php echo $ilpage['pmb']; ?>?cmd=remove-post&amp;id=<?php echo $resmessages['id']; ?>" onclick="return confirm_js('<?php echo $phrase['_please_take_a_moment_to_confirm_your_action_continue']; ?>')"><font color="red"><?php echo $phrase['_delete']; ?></font></a></div>
<?php
                                                        }
?>
                                                                                        </td>
                                                                                        <td valign="top" nowrap="nowrap"></td>
                                                                                </tr>
                                                                                </table>
                                                                                <!-- / customer info -->
                                                                        </td>
                                                                </tr>
                                                                <tr>
                                                                        <td align="left">
                                                                                <!-- private message -->
<?php
                                                        if (!empty($pmb['subject']))
                                                        {
                                                                echo '<div class="smaller gray" style="padding-bottom:4px"><strong>' . $pmb['subject'] . '</strong></div>';
                                                        }
                                                        echo $pmb['message'];
?>
                                                                                <!-- / private message -->
                                                                        </td>
                                                                </tr>
                                                                </table>
                                                        </div>
                                                        <div style="padding-top:7px"></div>
                                                </td>
                                        </tr>
<?php
                                                }
                                        }
                                        else
                                        {
                                                // no messages posted in this pmb.
?>
                                                <div><?php echo $phrase['_no_messages_currently_posted']; ?></div>
<?php
                                        }
?>
                                        </table>
                                </td>
                        </tr>
                        </table>
                </td>
        </tr>
<?php
                if ($ilconfig['globalfilters_pmbattachments'] > 0) 
                {
?>
        <tr>
                <td class="alt2" align="left"><strong><?php echo $phrase['_attachments_window']; ?></strong></td>
        </tr>
        <tr>
                <td class="alt1">
                        <div>
<?php
                        $attachment_list = '';
                        if (!empty($_SESSION['ilancedata']['user']['userid']))
                        {
                                $attachment_list = fetch_inline_attachment_filelist('', $ilance->GPC['decrypted']['project_id'], 'pmb');
                        }
?>														
                                <div align="left">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" dir="<?php echo $ilconfig['template_textdirection']; ?>">
                                        <tr>
                                                <td valign="top" align="left"><div id="attachmentlist"><?php echo (isset($attachment_list) ? $attachment_list : '' ); ?></div></td>
                                        </tr>
                                        </table>
<?php
                        $ilance->subscription = construct_object('api.subscription');
                                
                        $hiddeninput = array(
                                'attachtype' => 'pmb',
                                'pmb_id' => intval($ilance->GPC['decrypted']['event_id']),
                                'project_id' => intval($ilance->GPC['decrypted']['project_id']),
                                'user_id' => (!empty($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : '-1'),
                                'category_id' => intval($res['cid']),
                                'filehash' => md5(time()),
                                'max_filesize' => $ilance->subscription->check_access((!empty($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : '-1'), 'uploadlimit')
                        );
                        if (isset($ilance->GPC['decrypted']['isadmin']) AND $ilance->GPC['decrypted']['isadmin'])
                        {
                                $uploadbutton = '<div style="padding-top:5px"></div><input name="attachment" onclick=Attach("' . $ilpage['upload'] . '?crypted=' . encrypt_url($hiddeninput) . '") type="button" value="' . $phrase['_upload'] . '" class="buttons" style="font-size:15px" ' . $pmbaction . ' disabled="disabled" />';
                        }
                        else
                        {
                                $uploadbutton = '<div style="padding-top:5px"></div><input name="attachment" onclick=Attach("' . $ilpage['upload'] . '?crypted=' . encrypt_url($hiddeninput) . '") type="button" value="' . $phrase['_upload'] . '" class="buttons" style="font-size:15px" ' . $pmbaction . ' />';
                        }
                        
                        echo $uploadbutton;
?>
                                        <br />
                                </div>
                        </div>
                </td>
        </tr>
        <!-- / attachment window -->
<?php
                }
        }
        
        // #### PREVIEW PRIVATE MESSAGE ################################
        else 
        {
?>
                <div><div class="smaller"><strong><?php echo $pmb['previewsubject']; ?></strong></div><div><?php echo $pmb['preview']; ?></div></div>
<?php
        }
?>						
        <tr>
                <td class="alt2" align="left"><strong><?php echo $phrase['_submit_message']; ?></strong></td>
        </tr>
        <tr class="alt1">
                <td>
                        <div>
                                <div style="width:610px" align="left">
                                        <a name="reply"></a>
                                        <div align="left" style="padding-bottom:6px;">
                                                <div class="black" style="padding-bottom:3px"><strong><?php echo $phrase['_subject']; ?></strong></div>
                                                <input type="text" name="subject" value="<?php if (isset($ilance->GPC['preview'])) { echo $pmb['subject']; } ?>" style="width: 500px; font-family: verdana" />
                                        </div>
                                        <div style="padding-top:3px"><div class="black" style="padding-bottom:3px"><?php echo $phrase['_message'] ?></div><?php echo $wysiwyg_area; ?></div>
                                </div>
                        </div>
                </td>
        </tr>
        <tr>
                <td><input type="submit" name="preview" id="preview" value="<?php echo $phrase['_preview']; ?>" class="buttons" style="font-size:15px" <?php echo $pmbaction; ?> />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" id="save" value="<?php echo $phrase['_continue']; ?>" class="buttons" style="font-size:15px" <?php echo $pmbaction; ?> /></td>
        </tr>
        </table>
<!--</td>
</tr>
</table>-->
                                        
                                </div>
                                
                                <div class="block-footer">
                                                <div class="block-right">
                                                                <div class="block-left"></div>
                                                </div>
                                </div>
                                
                </div>
        </div>       
        </form>
	</td>
</tr>
</table>
	
					
<?php
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupfooter', array('headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','finaltime','finalqueries'));
	exit();
}
else 
{
        $area_title = $phrase['_access_denied'];
        $page_title = SITE_NAME . ' - ' . $phrase['_access_denied'];
        
        $ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_denied.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time') );
	$ilance->template->pprint('popupmain', array('input_style','remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer'));
	$ilance->template->pprint('popupfooter', array('headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','finaltime','finalqueries'));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>