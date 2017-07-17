<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # iLance Knowledge Base 1.0.8 Build 85
|| # -------------------------------------------------------------------- # ||
|| # Customer License # KapIxNXTSUYf3LjCGHiWk1XevwZ-ISZStLboZ-ErQdU-pATvJ3
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000-2011 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'lancekb',
	'accounting',
	'search'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'flashfix',
	'jquery'
);

// #### define top header nav ##################################################
$topnavlink = array(
	'kb'
);

// #### setup script location ##################################################
define('LOCATION', 'kb');

// #### require backend ########################################################
if(is_file('./functions/config.php'))
{
require_once('./functions/config.php');	
}else{
require_once('../functions/config.php');
}
$show['widescreen'] = true;

if (!isset($show['lancekb']) OR $show['lancekb'] == 0)
{
	$navcrumb = array("index.php" => "Knowledge Base");
	print_notice($phrase['_disabled'], $phrase['_were_sorry_this_feature_is_currently_disabled'], HTTP_SERVER . $ilpage['main'], $phrase['_main_menu']);
	exit();
}

$id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;

$headinclude .= '
<style type="text/css">
<!--
.star
{
	cursor: pointer;
}
.articletitle
{
    font-family: Century Gothic, Lucida Grande, Trebuchet MS, Verdana, Sans-Serif;
    font-size: 18px;
    letter-spacing: -1px;
    font-weight: bold;
    margin: 10px 0 7px 0;
}
//-->
</style>

<script type="text/javascript">
<!--
son = \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_on.gif\';
soff = \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_off.gif\';
enablerating = 1;
enableratingsend = 1;
function starover(sid)
{
	if (enablerating == 1)
	{
		counter = 5;
		while (counter < 6 && counter > 0)
		{
			if (counter > sid)
			{
				fetch_js_object(\'star\' + counter).src = soff;
			}
                        else
                        {
				fetch_js_object(\'star\' + counter).src = son;
			}
			counter--;
		}
	}
}

function stardown(sid)
{
	enablerating = 1;
	if (enableratingsend == 1)
	{
		starover(sid);
		xml = new AJAX_Handler(true);
		xml.onreadystatechange(checkratingsend);
		xml.send(\'' . HTTP_KB . 'index' . $ilconfig['globalsecurity_extensionmime'] . '\', \'cmd=rating&id=' . $id . '&rating=\'+ sid);
		enableratingsend = 0;
	}
	enablerating = 0;
}

function checkratingsend()
{
	if (xml.handler.readyState == 4 && xml.handler.status == 200 && xml.handler.responseText)
	{
		enableratingsend = 1;
	}
}
//-->
</script>
';

$navcrumb = array("$ilpage[index]" => $phrase['_knowledge_base']);
$categorycachekb = $ilance->lancekb->build_array($_SESSION['ilancedata']['user']['slng'], $propersort = true);

// AJAX member article rating
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rating' AND isset($ilance->GPC['id']) AND isset($ilance->GPC['rating']) AND !empty($_SESSION['ilancedata']['user']['userid']))
{
	$sql = $ilance->db->query("
		SELECT rating
		FROM " . DB_PREFIX . "kbratings
		WHERE user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
		    AND postsid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) == 0)
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "kbratings
			(postsid, rating, user_id)
			VALUES(
			'" . intval($ilance->GPC['id']) . "',
			'" . intval($ilance->GPC['rating']) . "',
			'" . intval($_SESSION['ilancedata']['user']['userid']) . "')
		");
	}
}

if (!empty($ilance->GPC['crypted']))
{
	$uncrypted = decrypt_url($ilance->GPC['crypted']);
}

if (isset($ilance->GPC['cmd']))
{
	$cmd = $ilance->GPC['cmd'];
}

if (isset($ilance->GPC['id']))
{
	$id = intval($ilance->GPC['id']);
}

$file = array();
$file['template'] = '';

if (!isset($ilance->GPC['catid']))
{
	$ilance->GPC['catid'] = 0;
}

if (!isset($cmd)) 
{ 
	$cmd = 1;
}

$area_title = $phrase['_knowledge_base'];
$page_title = $phrase['_knowledge_base'] . ' - ' . SITE_NAME;

switch ($cmd) 
{
	// KB Main
	case '1':
	{
		$file['template'] = DIR_KB_TEMPLATES . 'mainpage.php';
		break;
	}	
	// KB Categories
	case '2':
	{
		// construct breadcrumb trail
		$navcrumb = array();
		$navcrumb[HTTP_KB] = $phrase['_knowledge_base'];
        $ilance->lancekb->breadcrumb(intval($ilance->GPC['catid']), $_SESSION['ilancedata']['user']['slng'], $categorycachekb);
		$page_title =$ilance->lancekb->title('eng',intval($ilance->GPC['catid'])).' Articles at '.SITE_NAME;
		$file['template'] = DIR_KB_TEMPLATES . 'subpage.php';
                
		$typekb = 1;
		$redirectURL = HTTP_KB . '?cmd=2&' . $ilance->lancekb->fetch_variables();
                $redirectURL = urlencode($redirectURL);
                
		include(DIR_KB_FUNCTIONS . 'checksession.php');
		break;
	}	
	// KB Popularity Controls
	case '3':
	{
		$file['template'] = DIR_KB_TEMPLATES . 'popular.php';
		$typekb = 2;
		break;
	}	
	// KB Viewing Detailed Article
	case '4':
	{
                $catid = 0;
                $sql = $ilance->db->query("
                        SELECT catid
                        FROM " . DB_PREFIX . "kbposts
                        WHERE postsid = '" . intval($ilance->GPC['id']) . "'
                        LIMIT 1
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $catid = $res['catid'];
                }

                $titleplain = $ilance->lancekb->fetch_article_title_plain(intval($ilance->GPC['id']));
                $descriptionplain = $ilance->lancekb->fetch_article_body_plain(intval($ilance->GPC['id']));
                $descriptionplain = strip_tags($descriptionplain);

                $navcrumb = array();
                $navcrumb[HTTP_KB] = $phrase['_knowledge_base'];
                $ilance->lancekb->breadcrumb($catid, $_SESSION['ilancedata']['user']['slng'], $categorycachekb);
                
                $navcrumb[""] = $titleplain;
                
                $area_title = $phrase['_knowledge_base'] . ': ' . $titleplain;
                $page_title = $titleplain . ' - ' . SITE_NAME;
                
                $metadescription = $descriptionplain;
                $metakeywords = '';
                
		$file['template'] = DIR_KB_TEMPLATES . 'viewarticle.php';
                
		$redirectURL = HTTP_KB . '?cmd=4&amp;' . $ilance->lancekb->fetch_variables();	
		break;
	}	
	// KB Ask a Question
	case '5':
	{
		// construct breadcrumb trail
		$navcrumb = array();
		$navcrumb[HTTP_KB] = $phrase['_knowledge_base'];
		$navcrumb[""] = $phrase['_ask_a_question'];
		
		include(DIR_KB_FUNCTIONS."checkifloggedin.php");
		$file['template'] = DIR_KB_TEMPLATES."askquestion.php";
		if (isset($ilance->GPC['comments']) AND $ilance->GPC['comments'] != "" AND $ilance->GPC['catid'] > 0)
		{
			$catid = intval($ilance->GPC['catid']);
			
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "kbposts
				(catid, name, email, subject, insdate, moddate) 
				VALUES(
				'" . $catid . "',
				'" . strip_tags($ilance->db->escape_string($_SESSION['ilancedata']['user']['username'])) . "',
				'" . strip_tags($ilance->db->escape_string($_SESSION['ilancedata']['user']['email'])) . "',
				'" . strip_tags($ilance->db->escape_string($ilance->GPC['comments'])) . "',
				'" . DATETIME24H . "',
				'" . DATETIME24H . "')
			");
			
			@send_email(SITE_EMAIL, "New KB Question Submitted", $phrase['_question_saved'] . ":\n" . strip_tags($ilance->GPC['comments'])."\n\nPlease moderate this question from your knowledge base admin control panel.", SITE_EMAIL);
			
			$title = "<div class='yellowhlite'><span class='header'>".$phrase['_question_saved']."</span></div>";
			$msg = $phrase['_your_question_has_been_saved_it_will_be_displayed_after_a_moderator_or_author_approves_it'];
			
			$file['template'] = DIR_KB_TEMPLATES."showmessage.php";
		}
		break;
	}	
	// KB Search Result Listings
	case '6':
	{
		// construct breadcrumb trail
		$navcrumb = array();
		$navcrumb[HTTP_KB] = $phrase['_knowledge_base'];
		$navcrumb[""] = $phrase['_search'];
		
		$file['template'] = DIR_KB_TEMPLATES . 'searchresults.php';
		break;
	}	
	// KB Saved Articles [via Cookies]
	case '11':
	{
		if (isset($ilance->GPC['id']))
		{
			$id = intval($ilance->GPC['id']);
			if (!empty($_COOKIE[COOKIE_PREFIX . 'savedarticles']))
			{
				$arr = explode('|', $_COOKIE[COOKIE_PREFIX . 'savedarticles']);
				if (!in_array($id, $arr))
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "kbposts SET numsaves = numsaves + 1 WHERE postsid = '".$id."'");
					$_COOKIE[COOKIE_PREFIX . 'savedarticles'] = $_COOKIE[COOKIE_PREFIX . 'savedarticles'] . "|$id";
					set_cookie('savedarticles', $_COOKIE[COOKIE_PREFIX . 'savedarticles'], true);
				}
			}
			else
			{
				$ilance->db->query("UPDATE " . DB_PREFIX . "kbposts SET numsaves = numsaves + 1 WHERE postsid = '".$id."'");
				$_COOKIE[COOKIE_PREFIX . 'savedarticles'] = "$id";
				set_cookie('savedarticles', $_COOKIE[COOKIE_PREFIX . 'savedarticles'], true);
			}
			
			$title = "<div><strong>" . $phrase['_article_saved'] . "</strong></div>";
			$msg = $phrase['_the_article_has_been_saved_in_your_saved_list_you_can_return_to_your_saved_list_at_any_time_to_review_save_articles'];
			
			$file['template'] = DIR_KB_TEMPLATES."showmessage.php";
		}
		break;
	}	
	// KB Email Article to Friend
	case '12':
	
	
	{
	
	
		// Total Amount of Email 2 Friends Allowed Per Session
		$total_kb_email = "5";
		$file['template'] = DIR_KB_TEMPLATES."emailarticle.php";
		
		/*sekar works on sep 07 for email article*/
		$allowed = 1;
		if (isset($ilance->GPC['name']))
		{
		
		
			// does admin use captcha?
			/*if ($ilconfig['registrationdisplay_turingimage'])
			{
			*/
			
				// user supplied turing captcha
				if (isset($ilance->GPC['captcha']) AND $ilance->GPC['captcha'] != '' AND !empty($_SESSION['ilancedata']['user']['captcha']))
				{
			
					$captcha = mb_strtoupper(trim($ilance->GPC['captcha']));
					if ($captcha == $_SESSION['ilancedata']['user']['captcha'])
					{
						$allowed =1;
					}
					else
					{
					$allowed=0;
					}
				}
				else
				{
				$allowed=0;
				}
			//}
			if ($allowed)
			{
			
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "kbposts
					SET numemails = numemails + 1
					WHERE postsid = '".intval($ilance->GPC['id'])."'
					LIMIT 1
				");
		
				$sub = $phrase['_interesting_article_sent_by']." ".ucfirst($ilance->GPC['name']);
					
				$messagebody  = $ilance->GPC['comments']."\n";
				 $firstname = $ilance->GPC['fname'];
				$id = $ilance->GPC['id'];
				$rid=$_SESSION['ilancedata']['user']['ridcode'];
				
				if($_SESSION['ilancedata']['user']['ridcode'] != '')
				{
				$link = HTTP_KB."?cmd=4&id=".intval($ilance->GPC['id'])."&rid=".$_SESSION['ilancedata']['user']['ridcode']."\n\n".SITE_NAME."\n".HTTP_SERVER;
				}
				else
				{
				$link = HTTP_KB."?cmd=4&id=".intval($ilance->GPC['id'])."\n\n".SITE_NAME."\n".HTTP_SERVER;
				}
				      $ilance->email = construct_dm_object('email', $ilance);
				    $ilance->email->mail = $ilance->GPC['femail'];
                    //$ilance->email->slng = fetch_user_slng($watching['user_id']);
                    $ilance->email->get('kb_friend_mail');		
					$ilance->email->set(array(
							'{{friendname}}' => $firstname,
							'{{yourname}}' => $ilance->GPC['name'],
							'{{messagebody}}' => $messagebody,
							'{{link}}' => $link  ,
							
					));
                    $ilance->email->send();

				
				print_notice($phrase['_article_emailed'], $phrase['_the_selected_article_has_been_emailed_to_your_friend_at'].' '.$ilance->GPC['femail'], 'index'.$ilconfig['globalsecurity_extensionmime'].'?cmd=4&amp;id='.$ilance->GPC['id'], $phrase['_return_to_article']);
				exit();
			}
			else
			{
				print_notice($phrase['_captcha_warning'], $phrase['_in_order_to_process_this_form_you_must_enter'], 'javascript:history(-1);', $phrase['_return_to_previous_page']);
				exit();
			}
		}
		break;
	}
	
	
	
	// KB Add Comment
	case '13':
	{
		$file['template'] = DIR_KB_TEMPLATES . 'addcomment.php';
		include(DIR_KB_FUNCTIONS . 'checkifloggedin.php');
		
		if (isset($ilance->GPC['comments']) AND $ilance->GPC['comments'] != "")
		{
			$approved = 1;
			if ($ilance->lancekb->config['moderation'])
			{
				$approved = 0;
			}
			
			$ilance->GPC['name'] = un_htmlspecialchars($ilance->GPC['name']);
			$ilance->GPC['email'] = un_htmlspecialchars($ilance->GPC['email']);
			$ilance->GPC['comments'] = un_htmlspecialchars($ilance->GPC['comments']);
			$ilance->GPC['subject'] = un_htmlspecialchars($ilance->GPC['subject']);
			
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "kbcomments
				(postsid, name, email, title, content, ipaddr, insdate, approved) 
				VALUES(
				'" . intval($ilance->GPC['id']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['name']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['email']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['subject']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['comments']) . "',
				'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
				'" . DATETIME24H . "',
				'" . $approved . "')
			");
			
			refresh(urldecode($ilance->GPC['returnurl']));
			exit();
		}
		break;
	}    
	case '14':
	{
		// construct breadcrumb trail
		$navcrumb = array();
		$navcrumb[HTTP_KB] = $phrase['_knowledge_base'];
		$navcrumb[""] = $phrase['_saved_articles'];
		
		$file['template'] = DIR_KB_TEMPLATES."savedlist.php";
		if (isset($ilance->GPC['clear']) AND $ilance->GPC['clear'])
		{
			$_COOKIE[COOKIE_PREFIX . 'savedarticles'] = '';
			set_cookie('savedarticles', '', false);
			
			$title = "<div class='yellowhlite'><span class='header'>".$phrase['_saved_list_cleared']."</span></div>";
			$msg = $phrase['_your_saved_article_list_has_been_cleared'];
			
			$file['template'] = DIR_KB_TEMPLATES."showmessage.php";
		}
		break;
	}
}

$pprint_array = array('headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','meta_desc','meta_keyw','official_time');

$ilance->template->construct_header('kbheader');
$ilance->template->parse_hash('kbheader', array('ilpage' => $ilpage));
$ilance->template->parse_if_blocks('kbheader');
$ilance->template->pprint('kbheader', $pprint_array);
?>

<div style="font-size:18px; padding-bottom:12px"><?php echo $phrase['_knowledge_base']; ?></div>

<table width="100%" border="0" align="center" cellpadding="2" cellspacing="0">
<tr> 
    <td width="32%" align="left" valign="top"><?php include(DIR_KB_TEMPLATES . "sidelinks.php"); ?></td>
    <td width="68%" align="left" valign="top"><?php include($file['template']); ?></td>
</tr>
</table>

<?php
$ilance->template->construct_footer('kbfooter');
$ilance->template->parse_hash('kbfooter', array('ilpage' => $ilpage));
$ilance->template->parse_if_blocks('kbfooter');
$ilance->template->pprint('kbfooter', array('area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','finaltime','finalqueries'));

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Tue, Jan 11th, 2011
|| ####################################################################
\*======================================================================*/
?>
