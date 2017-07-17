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
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');
error_reporting(E_ALL);
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';
$ilance->auction = construct_object('api.auction');
$ilance->bid = construct_object('api.bid');
$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
  $ilance->subscription = construct_object('api.subscription');
  
  
  
  $ilance->GPC['description'] = !empty($ilance->GPC['description'])?$ilance->GPC['description']:'';
  $wysiwyg_area = print_wysiwyg_editor('description',$ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
  
  $ilance->bbcode = construct_object('api.bbcode');
  
  
  if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
  {
       
	     
        $_SESSION['ilancedata']['currentyear']=CURRENTYEAR;
		
		if(isset($ilance->GPC['subcmd']) and $ilance->GPC['subcmd'] == 'back')
		 {
		    
			
			$show['edit']=true;
		  
		  
		    $sql=$ilance->db->query("select * from ".DB_PREFIX."emaildesign ORDER BY id DESC LIMIT 1");
	
		    $mes=$ilance->db->fetch_array($sql);
		    $message=$mes['message'];
			 //$message = $ilance->bbcode->bbcode_to_html($mes['message']);
		   
		   
		   
		 
	   $wysiwyg_area1 = print_wysiwyg_editor('description', $message, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']);
		
       $pprint_array = array('wysiwyg_area1');
		   ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
			$ilance->template->fetch('main', 'email_design.html',2);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', 'info_val','series_list');
			$ilance->template->parse_loop('main', 'info_feat');
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
	    
		}

		if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd'] == 'area')
		{
		    
            $show['content']=true;
		
			$message = $ilance->GPC['description'];
                                $message = $ilance->bbcode->prepare_special_codes('PHP', $message);
                                $message = $ilance->bbcode->prepare_special_codes('HTML', $message);
                                $message = $ilance->bbcode->prepare_special_codes('CODE', $message);
                                $message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
                                //$message = $ilance->bbcode->strip_bb_tags($message);
                                $message = html_entity_decode($message);
								
															
						
		     $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "emaildesign
                                        (id, message)
                                        VALUES (
                                        NULL,
                                        '" . $ilance->db->escape_string($message) . "')
                                       
                                         ");
                               
		   $lastid=$ilance->db->insert_id();
		   
		   $message = $ilance->db->fetch_field(DB_PREFIX . "emaildesign", "id = '" . $lastid . "'", "message");
		   $message = $ilance->bbcode->bbcode_to_html($message);
		   $pprint_array = array('message');
		   ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
			$ilance->template->fetch('main', 'email_design.html',2);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', 'info_val','series_list');
			$ilance->template->parse_loop('main', 'info_feat');
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		
		}
		
		if(isset($ilance->GPC['var']))
		{
	 
		$file = HTTP_SERVER.'email_template/design_'.$_SESSION['ilancedata']['time'].'.html';
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		
		header("Content-Type: application/force-download");
		header( "Content-Disposition: attachment; filename=".basename($file));
		
		header( "Content-Description: File Transfer");
		@readfile($file);
		exit();
		}
		
		if(isset($ilance->GPC['temp']) and $ilance->GPC['temp'] == 'new_temp')
		{ 
		
		  $sql=$ilance->db->query("select * from ".DB_PREFIX."emaildesign ORDER BY id DESC LIMIT 1");
	
		  $mes=$ilance->db->fetch_array($sql);
		  $message=$mes['message'];
		
		
		  $head = '<table width="890" align="center"   border="0" cellpadding="3" cellspacing="0">
		  <tr></tr>
		  
		  <tr>
			<td height="71" colspan="10" style="text-align:center; font-size:40px; padding-top:20px;background-image:                        url('.HTTPS_SERVER.'/staff/images/top_bg.jpg); background-repeat:repeat-x;">
			<div align="left" style="margin-left:10px; margin-bottom:10px;"><img src="'.HTTPS_SERVER.'staff/images/logo_gc.png" width="354" height="70"                        alt="Greatcollections" /></div>
			</td>
		  </tr>
		   
		  <tr>
			<td width="550" bgcolor="#FFFFFF" style="border-collapse:collapse; border:1px solid #5B5F68; color: #000000; padding-left:150px; padding-right:150px;">
              '.strtr($ilance->bbcode->bbcode_to_html($message),"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","                                                     ").'  
 
 			  </td>
		  </tr>
		  
		  <tr >
			<td  colspan="10" bgcolor="#5B5F68" valign="top" height="120"><div align="center">
			  <table width="912" border="0" cellpadding="0" cellspacing="0">
		  
			   <tr>
		<td style="padding:10px 0; font-size:10px; color:#FFFFFF; text-align:center; font-family:verdana,arial;">
		<p style="margin:0; padding:10px 0; font-family:verdana,arial; font-size:10px; color:#FFFFFF;">This message was sent to <a href="mailto:{EMAIL_ADDRESS}" style="font-family:verdana,arial; font-size:10px; color:#FFFFFF; color:#FFFFFF; text-decoration:none;">{EMAIL_ADDRESS}</a>.<br />Please add <a href="mailto:info@greatcollections.com" style="font-family:verdana,arial; font-size:10px; color:#FFFFFF; color:#FFFFFF; text-decoration:none;">info@greatcollections.com</a> to your address book to ensure our emails reach your inbox!</p>
		
			  <p style="margin:0; padding:10px 0; font-family:verdana,arial; font-size:10px; color:#FFFFFF;">&#169; 2010 - {year} <a href="http://www.greatcollections.com/main.php?referal_name=email01" style="font-family:verdana,arial; font-size:11px; color:#FFFFFF; text-decoration:none; font-size:10px; color:#FFFFFF;">GreatCollections.com</a>, LLC<br />17500 Red Hill Avenue, Suite 160, Irvine, CA 92614-7290<br />
			  Tel: 1.800.44.COINS (+1.949.679.4180)</p>
		</td>
		</tr>
				
				
			  </table>
			</div></td>
		  </tr>
		  
		  </table>';
	    
		  	// $all = $head.''.$ilance->GPC['user'].''.$foot;
	        $_SESSION['ilancedata']['time'] = date('Y-m-d-h-i-s');	  
  echo DIR_SERVER_ROOT."email_template/design_".$_SESSION['ilancedata']['time'].".html";
			$all = $head;
			$handle = $all;
			$f=@fopen(DIR_SERVER_ROOT."email_template/design_".$_SESSION['ilancedata']['time'].".html","w");
			fwrite($f,$handle);
			fclose($f);
			
			exit();

	}

 
  
 
       $pprint_array = array('wysiwyg_area');
  
       ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	    $ilance->template->fetch('main', 'email_design.html',2);
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'info_val','series_list');
		$ilance->template->parse_loop('main', 'info_feat');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit(); 
  
   }
   
   else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

  
?>
  