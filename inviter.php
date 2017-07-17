<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.1.9 Build 1155
|| # -------------------------------------------------------------------- # ||
|| # Customer License # Baz-Ma-m-nFQPtSdZ-drK-REUQuYIXGKyDtIequB-zSq-l-M3u
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
        'buying',
        'selling',
        'rfp',
        'search',
        'feedback',
        'accounting',
        'javascript',
        'invitation'
);
$jsinclude = array(
        'jquery',
        'modal'
);
// #### setup script location ##################################################
define('LOCATION','inviter');

// #### require backend ########################################################
require_once('./functions/config.php');
error_reporting(E_ALL);
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[inviter]" => $ilcrumbs["$ilpage[inviter]"]);

if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) {

    $contents = '';
    $area_title = $phrase['_invitation'];
    $page_title = SITE_NAME . ' - ' . $phrase['_invitation'];
    $user_id = $_SESSION['ilancedata']['user']['userid'];
    $rid = $_SESSION['ilancedata']['user']['ridcode'];

    require_once DIR_FUNCTIONS. DS . 'custom' . DS . 'OpenInviter'. DS.'openinviter.php' ;

    $inviter=new OpenInviter();



    $oi_services=$inviter->getPlugins();
    $ilance->invitation = construct_object('api.mmi_invitation');

    $ilance->invitation->filterServices(& $oi_services);

    $ilance->invitation_post = construct_object('api.mmi_invitation_post');

    if (isset($ilance->GPC['provider_box'])) {
        if (isset($oi_services['email'][$ilance->GPC['provider_box']])) $plugType='email';
        elseif (isset($oi_services['social'][$ilance->GPC['provider_box']])) $plugType='social';
        else $plugType='';
    }
    else $plugType = '';
    function ers($ers) {
        if (!empty($ers)) {
            $contents="<table cellspacing='0' cellpadding='0' style='border:1px solid red;' align='center'><tr><td valign='middle' style='padding:3px' valign='middle'><img src='".OIBASE_URL."images/ers.gif'></td><td valign='middle' style='color:red;padding:5px;'>";
            foreach ($ers as $key=>$error)
                $contents.="{$error}<br >";
            $contents.="</td></tr></table><br >";
            return $contents;
        }
    }

    function oks($oks) {
        if (!empty($oks)) {
            $contents="<table border='0' cellspacing='0' cellpadding='10' style='border:1px solid #5897FE;' align='center'><tr><td valign='middle' valign='middle'><img src='".OIBASE_URL."images/oks.gif' ></td><td valign='middle' style='color:#5897FE;padding:5px;'>	";
            foreach ($oks as $key=>$msg)
                $contents.="{$msg}<br >";
            $contents.="</td></tr></table><br >";
            return $contents;
        }
    }

    if (!empty($ilance->GPC['step'])) $step=$ilance->GPC['step'];
    else $step='get_contacts';

    $ers=array();
    $oks=array();
    $import_ok=false;
    $done=false;
    if ($_SERVER['REQUEST_METHOD']=='POST') {
        if ($step=='get_contacts') {
            if (empty($ilance->GPC['email_box']))
                $ers['email']="{$phrase['_email_missing']} !";
            if (empty($ilance->GPC['password_box']))
                $ers['password']="{$phrase['_password_missing']} !";
            if (empty($ilance->GPC['provider_box']))
                $ers['provider']="{$phrase['_provider_missing']}!";
            if (count($ers)==0) {
                $inviter->startPlugin($ilance->GPC['provider_box']);
                $internal=$inviter->getInternalError();
                if ($internal)
                    $ers['inviter']=$internal;
                elseif (!$inviter->login($ilance->GPC['email_box'],$ilance->GPC['password_box'])) {
                    $internal=$inviter->getInternalError();

                    $ers['login']=($internal?$internal:"{$phrase['_login_failed_please_check_the_email_and_password_you_have_provided_and_try_again_later']}!");
                }
                elseif (false===$contacts=$inviter->getMyContacts())
                    $ers['contacts']="{$phrase['_unable_to_get_contacts']}!";
                else {
                    $import_ok=true;
                    $step='send_invites';
                    $ilance->GPC['oi_session_id']=$inviter->plugin->getSessionID();
                    $ilance->GPC['message_box']='';
                }
            }
        }
        elseif ($step=='send_invites') {
            if (empty($ilance->GPC['provider_box'])) $ers['provider']="{$phrase['_provider_missing']} !";
            else {
                $inviter->startPlugin($ilance->GPC['provider_box']);
                $internal=$inviter->getInternalError();
                if ($internal) $ers['internal']=$internal;
                else {
                    if (empty($ilance->GPC['email_box'])) $ers['inviter']="{$phrase['_inviter_information_missing']} !";
                    if (empty($ilance->GPC['oi_session_id'])) $ers['session_id']="{$phrase['_no_active_session']}";
                    if($ilance->invitation->config['requiremessage']){
                    
                            if (empty($ilance->GPC['message_box'])) $ers['message_body'] = "{$phrase['_message_missing']}!";
                    }
                    if (!empty($ilance->GPC['message_box'])){
                        $ilance->GPC['message_box']=strip_tags($ilance->GPC['message_box']);
                    }
                    $selected_contacts=array();
                    $contacts=array();


                    $ilMessage = & $ilance->invitation->getEmailMessage();

                    /**
                     *  The $message is for the internal plugins that have a sendMessage() method
                     */

                    $message = array('subject'=>$ilMessage->subject,
                            'body'=>$ilMessage->message,
                            'attachment'=>"\n\r{$phrase['_attached_message']}: \n\r".
                                    $ilance->GPC['message_box']);
                    /**
                     * End Building the Message
                     */

                    if ($inviter->showContacts()) {
                        //     parray($ilance->GPC);
                        foreach ($ilance->GPC as $key=>$val) {
                            if (strpos($key,'check_')!==false)
                                $selected_contacts[$ilance->GPC['email_'.$val]]=$ilance->GPC['name_'.$val];
                            elseif (strpos($key,'email_')!==false) {
                                $temp=explode('_',$key);
                                $counter=$temp[1];
                                if (is_numeric($temp[1])) $contacts[$val]=$ilance->GPC['name_'.$temp[1]];
                            }

                            /**
                             * MMI ADDED
                             */
                            if (strpos($key,'extra_')!==false) {
                                $newtemp = explode('_', $key);
                                $val = $newtemp[2];
                                $selected_contacts[$ilance->GPC['extra_email_'.$val]]=$ilance->GPC['extra_name_'.$val];
                            }
                        }
                        /* parray($selected_contacts,__FILE__. __LINE__);
                            parray($contacts,__FILE__. __LINE__);*/
                        /**
                         * end MMI ADDED
                         */
                        if (count($selected_contacts)==0) $ers['contacts']="{$phrase['_you_havent_selected_any_contacts_to_invite']}!";
                    }
                }
            }

            if (count($ers)==0) {
                $sendMessage=$inviter->sendMessage($ilance->GPC['oi_session_id'],$message,$selected_contacts);

                $inviter->logout();
                if ($sendMessage===-1) {
                    
                    /***
                     *  MMI Start Email
                    */


                    foreach ($selected_contacts as $email=>$name) {

                        $ilMessage->mail = $email;
                        if($ilMessage->send()) {
                            $sent[]= $email;
                        } else {
                            $notsent[] = $email;
                        }


                    }

                    $oks['mails'] = $phrase['_mail_success'];

                    /***
                     * MMI End Email
                     */


                }
                elseif ($sendMessage===false) {
                    $internal=$inviter->getInternalError();
                    $ers['internal']=($internal?$internal:"There were errors while sending your invites.<br>Please try again later!");
                }
                else $oks['internal']="Invites sent successfully!";
                $done=true;
            }
        }
    }
    else {
        $ilance->GPC['email_box']='';
        $ilance->GPC['password_box']='';
        $ilance->GPC['provider_box']='';
    }
    $headinclude .="<script type='text/javascript'>
                    function toggleAll(element)
                    {
                    var form = document.forms.openinviter, z = 0;
                    for(z=0; z<form.length;z++)
                            {
                            if(form[z].type == 'checkbox')
                                    form[z].checked = element.checked;
                            }
                    }
                    </script>
                    <link rel='stylesheet' type='text/css' href='styles/oistyle.css'/>
                    ";

    if (!$done) {
        if ($step=='get_contacts') {
            $oi_messages = ers($ers).oks($oks);

            $type = $ilance->invitation->config['serviceselecttype'];
            $provider_selection = $ilance->invitation_post->post_service_selection_html($type);
            $email_box = $ilance->GPC['email_box'];
            $password_box = $ilance->GPC['password_box'];



            $ilance->template->fetch('main', 'inviter_import.html');
            $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
            $ilance->template->parse_if_blocks('main');

            $pprint_array = array('email_box','provider_selection','oi_messages', 'user_id', 'remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

            $ilance->template->pprint('main', $pprint_array);
            exit();

        }  else {


            $message_box = $ilance->GPC['message_box'];

        }
    }

    if (!$done) {
        if ($step=='send_invites') {
            if ($inviter->showContacts()) {
                $oi_messages = ers($ers).oks($oks);
                $invitetdhead_colspan= ($plugType=='email')? "3":"2";
                $email_label = ($plugType == 'email') ? "<td><strong>{$phrase['_email']}</strong></td>":"";
                $contact_list = '';
                $odd=true;
                $counter=0;
                foreach ($contacts as $email=>$name) {
                    $counter++;
                    if ($odd) $class='thTableOddRow'; else $class='thTableEvenRow';
                    $contact_list.="<tr class=''><td><input name='check_{$counter}' value='{$counter}' type='checkbox' class='thCheckbox' checked><input type='hidden' name='email_{$counter}' value='{$email}'><input type='hidden' name='name_{$counter}' value='{$name}'></td><td>{$name}</td>".($plugType == 'email' ?"<td>{$email}</td>":"")."</tr>";
                    $odd=!$odd;
                }
                $extras = 4;
                $extra_emails = '';
                for($i = 1; $i <= $extras; $i++) {

                    $counter++;
                    $extra_emails .="<tr><td><label>{$phrase['_email']}</label><input type='text' name='extra_email_{$counter}' value=''><label>{$phrase['_name']}</label><input type='text' name='extra_name_{$counter}' value=''></td></tr>";

                }
//echo '<table>'.$contact_list.'</table>';
                $provider_box = $ilance->GPC['provider_box'];
                $email_box = $ilance->GPC['email_box'];
                $oi_session_id = $ilance->GPC['oi_session_id'];
                $contact_count = count($contacts);

            }
        }




            $ilMessage = & $ilance->invitation->getEmailMessage(true);

           
            $email_preview = nl2br($ilMessage->message);//' Message Holder will be iLance Email'; // TODO: make email building in one fucntion

            $ilance->template->fetch('main', 'inviter_send.html');
            $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
            $ilance->template->parse_if_blocks('main');

            $pprint_array = array('email_preview','contact_count','message_box','email_label','oi_messages','oi_session_id','email_box','provider_box','extra_emails','contact_list','invitetdhead_colspan', 'user_id', 'remote_addr','rid','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');

            $ilance->template->pprint('main', $pprint_array);
            exit();
        } else {
            ///function print_notice($header_text = '', $body_text = '', $return_url = '', $return_name = '', $custom = '')
            $header_text = $phrase['_mail_sent_successfully'];
            $body_text = $phrase['_mail_sent_successfully_body_text'];
            $return_url = HTTPS_SERVER;
            $return_name = $phrase['_home'];
            print_notice($header_text, $body_text , $return_url , $return_name);
            exit();
        }

    } else {
        /**
         * Redirect if not logged in...
         */
        refresh($ilpage['login'] . '?redirect=' . urlencode($ilpage['inviter'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
        exit();
    
}

?>