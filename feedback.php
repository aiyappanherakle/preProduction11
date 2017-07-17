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
	'rfp',
	'buying',
	'selling',
	'search',
	'feedback'
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

// #### define top header nav ##################################################
$topnavlink = array(
        'feedback'
);

// #### setup script location ##################################################
define('LOCATION','feedback');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[feedback]" => $ilcrumbs["$ilpage[feedback]"]);

// #### LEAVE FEEDBACK #########################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_leave-feedback' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $area_title = $phrase['_leave_feedback'];
        $page_title = SITE_NAME . ' - ' . $phrase['_leave_feedback'];
    
        // #### load our backend ###############################################
        $ilance->bid = construct_object('api.bid');
        $ilance->feedback = construct_object('api.feedback');
        $ilance->mycp = construct_object('api.mycp');

        // #### define our feedback control tabs ###############################
        $showview = $jsbit = '';
        if (empty($ilance->GPC['view']) OR !empty($ilance->GPC['view']) AND $ilance->GPC['view'] == 0)
        {
                $showview = '<div style="font-size:13px" class="gray"><strong>' . $phrase['_view'] . ':</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="black">' . $phrase['_all'] . '</span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1">' . $phrase['_bought'] . '</a></span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2">' . $phrase['_sold'] . '</a></span></strong></div>';
                $ilance->GPC['view'] = 'all';
        }
        else if (!empty($ilance->GPC['view']) AND $ilance->GPC['view'] == 1)
        {
                $showview = '<div style="font-size:13px" class="gray"><strong>' . $phrase['_view'] . ':</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback">' . $phrase['_all'] . '</a></span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="black">' . $phrase['_bought'] . '</span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2">' . $phrase['_sold'] . '</a></span></strong></div>';
                $ilance->GPC['view'] = 'bought';
        }
        else if (!empty($ilance->GPC['view']) AND $ilance->GPC['view'] == 2)
        {
                $showview = '<div style="font-size:13px" class="gray"><strong>' . $phrase['_view'] . ':</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback">' . $phrase['_all'] . '</a></span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1">' . $phrase['_bought'] . '</a></span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="black">' . $phrase['_sold'] . '</span></strong></div>';
                $ilance->GPC['view'] = 'sold';
        }
        
        // #### generate loop for our template display #########################
        $fb = $ilance->mycp->feedback_activity($_SESSION['ilancedata']['user']['userid'], $ilance->GPC['view']);
        $feedback = (isset($fb) AND is_array($fb)) ? $fb[0] : array();
        $count = count($feedback);

        // #### handle template if conditional for no results found logic ######
        $show['noresults'] = ($count == 0) ? true : false;
        
        // #### build our criteria list ########################################
        $criteria = $ilance->feedback->criteria(0, $_SESSION['ilancedata']['user']['slng']);
        if (isset($feedback) AND is_array($feedback))
        {
                foreach ($feedback AS $key => $value)
                {
                        $GLOBALS['feedback_criteria' . $value['project_id']] = $criteria;
                }
        }
        
        // #### build our javascript ###########################################
        $headinclude .= '
<script type="text/javascript"> 
<!--
son = \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_on.gif\';
soff = \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_off.gif\';
enablerating = new Array(1);
enableratingsend = new Array(1);
for (i = 0; i < 100; ++ i)
{
        enablerating[i] = new Array(1);
        enableratingsend[i] = new Array(1);
}
';
$i = 0;
foreach ($criteria AS $key => $value)
{
        foreach ($feedback AS $key2 => $value2)
        {
                $headinclude .= '
enablerating[\'' . $value['id'] . '\'][\'' . $value2['project_id'] . '_' . $value2['md5'] . '\'] = 1;
enableratingsend[\'' . $value['id'] . '\'][\'' . $value2['project_id'] . '_' . $value2['md5'] . '\'] = 1;
';
        }
        $i++;
}
        $headinclude .= '
function starover(sid, pid, cid)
{
	if (enablerating[cid][pid] == 1)
	{
		counter = 5;
		while (counter < 6 && counter > 0)
		{
			if (counter > sid)
			{
				fetch_js_object(\'star\' + counter + \'_\' + pid + \'_\' + cid).src = soff;
                                fetch_js_object(\'fbtext_\' + pid + \'_\' + cid).innerHTML = sid + \' ' . $phrase['_of_5_stars'] . '\';
			}
                        else
                        {
				fetch_js_object(\'star\' + counter + \'_\' + pid + \'_\' + cid).src = son;
                                fetch_js_object(\'fbtext_\' + pid + \'_\' + cid).innerHTML = sid + \' ' . $phrase['_of_5_stars'] . '\';
			}
			counter--;
		}
                if (sid == \'0\')
                {
                        fetch_js_object(\'fbtext_\' + pid + \'_\' + cid).innerHTML = \'\';        
                }
	}
}

function stardown(sid, pid, cid)
{
	enablerating[cid][pid] = 1;
	if (enableratingsend[cid][pid] == 1)
	{
		starover(sid, pid, cid);
                fetch_js_object(\'fbtext_\' + pid + \'_\' + cid).innerHTML = sid + \' ' . $phrase['_of_5_stars'] . '\';
                fetch_js_object(\'criteria_\' + pid + \'_\' + cid).value = sid;
		enableratingsend[cid][pid] = 0;
	}
	enablerating[cid][pid] = 0;
}
//-->
</script>
';

        $pprint_array = array('showview','for_user_id','from_user_id','from_type_reverse','responsepulldown','totalamount','project_title','customer','project_id','seller_id','buyer_id','from_type','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        $ilance->template->fetch('main', 'feedback.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'feedback','criteria');
        if (!isset($feedback))
        {
                $feedback = array();
        }
        @reset($feedback);
        while ($i = @each($feedback))
        {
                $ilance->template->parse_loop('main', 'feedback_criteria' . $i['value']['project_id']);
        }
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}

// #### LEAVE FEEDBACK HANDLER #################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-feedback-submit' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $ilance->feedback = construct_object('api.feedback');
	$ilance->feedback_rating = construct_object('api.feedback_rating');
        
	$area_title = $phrase['_feedback_and_rating_submit_process'];
	$page_title = SITE_NAME . ' - ' . $phrase['_feedback_and_rating_submit_process'];
        
        //print_r($ilance->GPC); exit;
        
        // #### begin feedback save ############################################
        $ilance->GPC['response'] = ((isset($ilance->GPC['response']) AND is_array($ilance->GPC['response'])) ? $ilance->GPC['response'] : array());
        $ilance->GPC['comments'] = ((isset($ilance->GPC['comments']) AND is_array($ilance->GPC['comments'])) ? $ilance->GPC['comments'] : array());
        $ilance->GPC['criteria'] = ((isset($ilance->GPC['criteria']) AND is_array($ilance->GPC['criteria'])) ? $ilance->GPC['criteria'] : array());
        $ilance->GPC['for_user_id'] = ((isset($ilance->GPC['for_user_id']) AND is_array($ilance->GPC['for_user_id'])) ? $ilance->GPC['for_user_id'] : array());
        $ilance->GPC['from_user_id'] = ((isset($ilance->GPC['from_user_id']) AND is_array($ilance->GPC['from_user_id'])) ? $ilance->GPC['from_user_id'] : array());
        $ilance->GPC['fromtype'] = ((isset($ilance->GPC['fromtype']) AND is_array($ilance->GPC['fromtype'])) ? $ilance->GPC['fromtype'] : array());
        
        // #### skip listings where feedback will be left later ################
        $pids = array();
        $success = false;
        foreach ($ilance->GPC['response'] AS $project_id => $value)
        {
                if ($value != 'later')
                {
                        $pids[] = $project_id;
                }
        }
	
        // #### submit multiple feedback ratings ###############################
        foreach ($pids AS $project_id)
        {
		$pid = explode('_', $project_id);
                $success = $ilance->feedback_rating->insert_feedback_rating($pid[0], $ilance->GPC['for_user_id']["$project_id"], $ilance->GPC['from_user_id']["$project_id"], (isset($ilance->GPC['criteria']["$project_id"]) ? $ilance->GPC['criteria']["$project_id"] : array()), $ilance->GPC['comments']["$project_id"], $ilance->GPC['fromtype']["$project_id"], $ilance->GPC['response']["$project_id"]);
        }
        
        if ($success)
        {
                print_notice($phrase['_feedback_and_rating_complete'], $phrase['_thank_you_for_taking_a_few_moments_to_rate_this_customer_and_provide_feedback_for_others_to_review']."<br /><br />".$phrase['_please_contact_customer_support'], $ilpage['main'] . '?cmd=cp', $phrase['_my_cp']);
                exit();
        }
        else
        {
                print_notice($phrase['_leave_feedback'], $phrase['_you_have_chosen_to_leave_feedback_later_for_one_or_more_listings'], $ilpage['main'] . '?cmd=cp', $phrase['_my_cp']);
                exit();
        }
}
else
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['feedback'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>