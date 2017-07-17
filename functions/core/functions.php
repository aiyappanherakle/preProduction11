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
* Core Functions in ILance to perform the majority operations within the Front End and Admin Control Panel.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
/**
* Function for FEATURED AUCTIONS
* Note: Internet Explorer for Mac does not support httponly
*
* @param	string	        cookie name
* @param	mixed	        cookie value
* @param	boolean	        is permanent for 1 year? (default true)
* @param	boolean	        enable secure cookies over SSL
* @param	boolean	        enable httponly cookies in supported browsers? (default false)
* @param        integer         (optional) force cookie to expiry in x days (default 365)
*/
function featured_auction2($row=0 , $grading_service='')
{
 	global $ilance,$show,$ilconfig;
	$column=$row*4;
	$count_gal=1;
	$myfeature.= '<table cellpadding="9" border="0" ><tr>';       
					
	if($grading_service)
	 {
	   $select_featurednew=	$ilance->db->query("SELECT  a.filehash,a.filename,p.Grading_Service, p.project_id, p.currentprice, p.featured, p.project_id, p.project_title,  p.status 
			                            FROM " . DB_PREFIX . "projects as p 
						    LEFT JOIN " . DB_PREFIX . "attachment as a on (a.project_id=p.project_id AND a.visible = '1' AND a.attachtype = 'itemphoto')
					        WHERE p.Grading_Service = '" . $grading_service . "' 
							AND p.featured='1'
                            AND p.status='open'
							ORDER BY rand() LIMIT ".$column."
			   	                      ");
	 }
	 else
	 {
	 $select_featurednew=	$ilance->db->query("SELECT  a.filehash, c.cid,c.project_id,c.project_title,c.filtered_auctiontype,c.currentprice
			                            FROM " . DB_PREFIX . "projects as c
						    LEFT JOIN " . DB_PREFIX . "attachment as a on (a.project_id=c.project_id AND a.visible = '1'
							                                          AND a.attachtype = 'itemphoto')
						    WHERE c.featured = '1'  
						    AND c.project_state = 'product'
						    AND c.status = 'open'
						    GROUP BY c.project_id 
						    ORDER BY RAND()  LIMIT ".$column."
						    ");
	 }
	
			
	        if($ilance->db->num_rows($select_featurednew) >0)
		{
		$show['result']=true;
			
			while($row_pre_fea = $ilance->db->fetch_array($select_featurednew))
			{
				
			                
					if(strlen($row_pre_fea['filehash'])>0)
					{
					   
						 $uselistra = HTTPS_SERVER. 'image/140/170/' . $row_pre_fea['filename'];
						//echo $uselistra;
						if ($ilconfig['globalauctionsettings_seourls'])	
						     $htma ='<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'"></a>';
						else
						     $htma ='<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'"></a>';						
					}
					else
					{
						 
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])	
						     $htma ='<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" alt="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" style="padding-top: 6px;"></a>';
						else
					        $htma ='<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" alt="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" style="padding-top: 6px;"></a>';
					}
				
				
				        $myfeature.= '<td>';	
				
				if($count_gal%4==0)
				  $sep = '';
				else
				  $sep = '<td id="seperator"></td>';
				
				 $yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], '', 'right', $timeintext = 0, $showlivebids = 0, 0);
				 $myfeature.= '<div id="abox01">
						
						<div id="fetit">';
						$myfeature.=$ilconfig['globalauctionsettings_seourls']?
						'<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">':
						'<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'">';
						
						$myfeature.=$row_pre_fea['project_title'].'</a></div>
						<div>&nbsp;</div>
						<div id="textim"><div align="center">'.$htma.'</div></div>
						
					        <div style="height: 50px;padding-top: 6px;">	<div id="fetit" style="float: left; width: 99px;">Currently:<br>
						<span id="amo">$ '.$row_pre_fea['currentprice'].'</span></div>
						<div style="float:left;">';
						
						if($row_pre_fea['filtered_auctiontype'] == 'fixed')
						{
				  			 $image = 'buy_now_but.jpg';
						}
						else
						{
							$image = 'bid_now_butt.jpg';
				   			
						}
						if ($ilconfig['globalauctionsettings_seourls'])	
						$myfeature.= '<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
						else
						$myfeature.= '<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.'" /></a>';
						
						 $sql_idly = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "dailydeal WHERE project_id = '".$row_pre_fea['project_id']."'
						
                                 ");
				   
					if($ilance->db->num_rows($sql_idly) > 0)
					{
					  
					  $daily = '24-Hour Deals Starts';
					
					}
					else
					{					
					  $daily = 'Ends';
					}
						$myfeature.= '</div>
						</div>
						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div><div>&nbsp;</div>
						<div id="fetit">'.$daily.': '.$yutq.'</div>
						</div>';
						
				 if($count_gal%4==0)
				 {
				  if($count_gal==$column)
				  $myfeature.= $sep.'</tr><tr>';
				  else
                                  $myfeature.= $sep.'</tr><tr><td colspan="7"><hr></td></tr><tr>';
			         }
				 else
				 {
				  $myfeature.= $sep;	
				 }
				$count_gal++;
			}
			
		}	
		     
			$myfeature.='</table>';
       
	   return  $myfeature;
	 
}
function random_auction($row=0)
{
 	global $ilance,$show,$ilconfig;
	$column=$row*4;
	$count_gal=1;
	$myfeature.= '<table cellpadding="9" border="0" ><tr>';       
	
	
	$select_featurednew=$ilance->db->query("SELECT  a.filename,a.filehash,p.project_id,p.currentprice,p.project_title,p.status 
						FROM " . DB_PREFIX . "projects as p 
						LEFT JOIN " . DB_PREFIX . "attachment as a on (a.project_id=p.project_id AND a.visible = '1'
						AND a.attachtype = 'itemphoto')
						WHERE p.status='open'
						AND p.bids > 0
						AND a.attachtype IS NOT NULL
						AND p.filtered_auctiontype ='regular'
						ORDER BY rand() LIMIT ".$column."
			   	                ");
	
	 if($ilance->db->num_rows($select_featurednew) >0)
		{
		$show['result']=true;
			
			while($row_pre_fea = $ilance->db->fetch_array($select_featurednew))
			{
				
			                
					if(strlen($row_pre_fea['filehash'])>0)
					{
					   
						 //$uselistra = HTTPS_SERVER. 'attachment.php'.'?cmd=thumb&subcmd=itemphoto&id=' . $row_pre_fea['filehash'] .'&w=170&h=140';
						$uselistra = HTTPS_SERVER. 'image/140/170/' . $row_pre_fea['filename'];
						//echo $uselistra;
						if ($ilconfig['globalauctionsettings_seourls'])	
						     $htma ='<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'"></a>';
						else
						     $htma ='<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$uselistra.'" style="padding-top: 6px;" alt="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'"></a>';						
					}
					else
					{
						 
					    $uselistra = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
						if ($ilconfig['globalauctionsettings_seourls'])	
						     $htma ='<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" alt="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" style="padding-top: 6px;"></a>';
						else
					        $htma ='<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.HTTPS_SERVER.'images/gc/nophoto.gif" alt="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" title="'.$row_pre_fea['project_title'].''.$phrase['_at_gc_image_tag'].'" style="padding-top: 6px;"></a>';
					}
				
				
				        $myfeature.= '<td>';	
				
			
					$sep = '<td></td>';
			
				
				 //$yutq = $ilance->auction->auction_timeleft($row_pre_fea['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'right', $timeintext = 0, $showlivebids = 0, 0);
				$yutq = auction_time_left_new($row_pre_fea,true);
				 $myfeature.= '<div id="abox01" style="height: 310px;">
						
						<div id="fetit">';
						$myfeature.=$ilconfig['globalauctionsettings_seourls']?
						'<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'">':
						'<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'">';
						
						$myfeature.=$row_pre_fea['project_title'].'</a></div>
						<div>&nbsp;</div>
						<div id="textim"><div align="center">'.$htma.'</div></div>
						
					        <div style="height: 50px;padding-top: 6px;">	<div id="fetit" style="float: left; width: 99px;">Currently:<br>
						<span id="amo">$ '.$row_pre_fea['currentprice'].'</span></div>
						<div style="float:left;">';
						
						$image = 'bid_now_butt.jpg';
				   		
						if ($ilconfig['globalauctionsettings_seourls'])	
						$myfeature.= '<a href="'.HTTPS_SERVER.'Coin/'.$row_pre_fea['project_id'].'/'.construct_seo_url_name($row_pre_fea['project_title']).'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';
						else
						$myfeature.= '<a href="'.HTTPS_SERVER.'merch.php?id='.$row_pre_fea['project_id'].'"><img src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . ''.$image.' " /></a>';
						
						
					
						$daily = 'Ends';
					  
						$myfeature.= '</div>
						</div>
						<div id="fetit">GC Item: '.$row_pre_fea['project_id'].'</div><div>&nbsp;</div>
						<div id="fetit">'.$daily.': '.$yutq.'</div>
						</div>';
						
				 if($count_gal%4==0)
				 {
				  if($count_gal==$column)
				  $myfeature.= $sep.'</tr><tr>';
				  else
                                  $myfeature.= $sep.'</tr><tr><td colspan="7"><hr></td></tr><tr>';
			         }
				 else
				 {
				  $myfeature.= $sep;	
				 }
				$count_gal++;
			}
			
		}	
		     
			$myfeature.='</table>';
       
	   return  $myfeature;
	 
	 
}
/**
* Function to create a cookie variable name
* Note: Internet Explorer for Mac does not support httponly
*
* @param	string	        cookie name
* @param	mixed	        cookie value
* @param	boolean	        is permanent for 1 year? (default true)
* @param	boolean	        enable secure cookies over SSL
* @param	boolean	        enable httponly cookies in supported browsers? (default false)
* @param        integer         (optional) force cookie to expiry in x days (default 365)
*/
function set_cookie($name, $value = '', $permanent = true, $allowsecure = true, $httponly = false, $expiredays = 365)
{
	global $ilance;
	$expire = ($permanent) ? TIMESTAMPNOW + 60 * 60 * 24 * $expiredays : 0;
	$httponly = (($httponly AND ($ilance->common->is_browser('ie') AND $ilance->common->is_browser('mac'))) ? false : $httponly);
	$secure = ((PROTOCOL_REQUEST === 'https' AND $allowsecure) ? true : false);
	$name = COOKIE_PREFIX . $name;
	$linenum = 0;
        $filename = 'N/A';
        
        do_set_cookie($name, $value, $expire, '/', '', $secure, $httponly);
}
/**
* Callback function to actually set the cookie called from set_cookie()
*
* @param	string	        cookie name
* @param	string	        cookie value
* @param	int		cookie expire time
* @param	string	        cookie path
* @param	string	        cookie domain
* @param	boolean	        cookie secure via SSL
* @param	boolean	        cookie is http only
*
* @return	boolean	        Returns true on success
*/
function do_set_cookie($name, $value, $expires, $path = '', $domain = '', $secure = false, $httponly = false)
{
	if ($value AND $httponly)
	{
		foreach (array("\014", "\013", ",", ";", " ", "\t", "\r", "\n") AS $badcharacter)
		{
			if (mb_strpos($name, $badcharacter) !== false OR mb_strpos($value, $badcharacter) !== false)
			{
				return false;
			}
		}
		// name and value
		$setcookie = "Set-Cookie: $name=" . urlencode($value);
		// expiry
		$setcookie .= ($expires > 0 ? '; expires=' . gmdate('D, d-M-Y H:i:s', $expires) . ' GMT' : '');
		// path
		$setcookie .= ($path ? "; path=$path" : '');
		// domain
		$setcookie .= ($domain ? "; domain=$domain" : '');
		// secure
		$setcookie .= ($secure ? '; secure' : '');
		// httponly
		$setcookie .= ($httponly ? '; HttpOnly' : '');
		header($setcookie, false);
		return true;
	}
	else
	{
		return setcookie($name, $value, $expires, $path, $domain, $secure);
	}
}
/**
* Function to strip out any email phrases from a string such as a message or comment.
*
* @param       string       message
* 
* @return      string       Message with email phrases blocked
*/
function strip_email_words($message = '')
{
        global $phrase, $ilpage;
        
        $siteemail = SITE_EMAIL;
        
        // doubles checks if site email is in the string if so it skips the replacements
        if (!mb_ereg($siteemail, $message))
        {
                $message = preg_replace("'<a href=\"mailto:(.*)\">(.*)</a>'siU", '[-' . $phrase['_email_blocked'] . '-]', $message);
                $message = preg_replace("![a-z0-9_.-]+@[a-z0-9-]+(\.[a-z]{2,6})+!i", '[-' . $phrase['_email_blocked'] . '-]', $message);
        }
        
        return $message;
}
/**
* Function to strip out any domain name phrases from a string such as a message or comment.
*
* @param       string       message
* 
* @return      string       Message with domain phrases blocked
*/
function strip_domain_words($message = '')
{
        global $phrase, $ilpage;
        
        // doubles checks if site domain is in the string if so it skips the replacements
        $sitedomain = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER);
        
        if (!mb_ereg($sitedomain, $message))
        {        
                $message = preg_replace("!(http://)?w{3}\.[a-z0-9-]+(\.[a-z]{2,6})+!i", '[-' . $phrase['_domain_blocked'] . '-]', $message);
                $message = preg_replace("'<a href=\"(.*)\">(.*)</a>'siU", '[-' . $phrase['_domain_blocked'] . '-]', $message);
        }
        
        return $message;
}
/**
* Function to strip out any vulgar words based on a selection of words created in the admin cp.
*
* @param       string       message
* 
* @return      string       Message with domain phrases blocked
*/
function strip_vulgar_words($message = '', $stripurls = true)
{
        global $ilance, $ilconfig;
        $ilance->bbcode = construct_object('api.bbcode');
        
        // avoid breaking [IMG] bbcode tags and cut them out for a minute..
        $ilance->bbcode->strip_special_codes('IMG', $message, $php_matches);
        
        if ($ilconfig['globalfilters_vulgarpostfilter'])
        {
                $words_blacklist = array();
                $words = mb_split(', ', $ilconfig['globalfilters_vulgarpostfilterlist']);
                if (is_array($words) AND !empty($words))
                {
                        foreach ($words AS $vulgarword)
                        {
                                if (isset($vulgarword) OR !empty($vulgarword))
                                {
                                        $vulgarword = trim($vulgarword);
                                        $message = preg_replace("/\b$vulgarword\b/", "&nbsp;" . $ilconfig['globalfilters_vulgarpostfilterreplace'] . "&nbsp;", $message);
                                }
                        }
                }
        }
        
	if ($stripurls)
	{
		$message = ($ilconfig['globalfilters_emailfilterrfp']) ? strip_email_words($message) : $message;
		$message = ($ilconfig['globalfilters_domainfilterrfp']) ? strip_domain_words($message) : $message;
	}
        
        // restore our [IMG] bbcode tag back into the string
        $ilance->bbcode->restore_special_codes('IMG', $message, $php_matches);
        
        return $message;
}
/**
* Function to print a viewable notice template to the web browser using the regular ILance template parsed with the header and footer
*
* @param       string       header text
* @param       string       body text
* @param       string       return url
* @param       string       return url title
* 
* @return      string       Message with domain phrases blocked
*/
function print_notice($header_text = '', $body_text = '', $return_url = '', $return_name = '', $custom = '', $crumb = '')
{
        global $ilance, $myapi, $phrase, $breadcrumb, $page_title, $area_title, $ilconfig, $ilpage, $show;        
        $header = $header_text; $body = $body_text; $return = $return_url; $returnname = $return_name;
        
        global $header_text, $body_text, $return_url, $return_name, $show;
        $header_text = $header; $body_text = $body; $return_url = $return; $return_name = $returnname;
        
        $show['widescreen'] = false;
        
        $area_title = $header_text;
        $page_title = SITE_NAME . ' - ' . $header_text;
        
        if (is_array($custom) AND !empty($custom))
        {
                $text = '<div style="padding-top:12px"><hr size="1" width="100%" style="color:#cccccc; margin-bottom:6px" /><strong>' . $phrase['_detailed_permissions_information'] . '</strong></div><div style="padding-top:3px"><span class="gray">' . $phrase['_subscription_permission_required_for_this_resource'] . '</span> <span class="blue"><strong>' . ucwords($custom['text']) . '</strong></span></div><div><em>&quot;' . $custom['description'] . '&quot;</em></div>';
                $body_text .= $text;
        }
		
		 if($crumb == '1')
  
        {
           $breadcrumbtrail = '';
		
		   $breadcrumbfinal = '';
		   
		   $pprint_array = array('body_text','header_text','return_url','return_name','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','breadcrumbtrail','breadcrumbfinal');
		   
		}
		
		else
		
		{   
		
        
        $pprint_array = array('body_text','header_text','return_url','return_name','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		}
        
        ($apihook = $ilance->api('print_notice_end')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'print_notice.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
/**
* Function to fetch the short form language identifier used by the marketplace as default (english = eng)
*
* @return      string       Short form language identifier
*/
function fetch_site_slng()
{
return 'eng';
        global $ilance, $myapi, $ilconfig;
        
        $sql = $ilance->db->query("
                SELECT languagecode
                FROM " . DB_PREFIX . "language
                WHERE languageid = '" . $ilconfig['globalserverlanguage_defaultlanguage'] . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $lcode = $ilance->db->fetch_array($sql);
                return mb_substr($lcode['languagecode'], 0, 3);
        }
        
        
}
/**
* Function to fetch the short form language identifier used by the marketplace as default (english = eng)
*
* @param       integer      user id
* 
* @return      string       Short form language identifier
*/
function fetch_user_slng($userid = 0)
{
return 'eng';
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT languageid
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $lang = $ilance->db->fetch_array($sql, DB_ASSOC);
                $sql2 = $ilance->db->query("
                        SELECT languagecode
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . $lang['languageid'] . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql2) > 0)
                {
                        $lcode = $ilance->db->fetch_array($sql2, DB_ASSOC);
                        return mb_substr($lcode['languagecode'], 0, 3);
                }
        }
        
        
}
/**
* Function to generate a unique password salt string mainly used for password hashing
*
* @param       integer      length of salt to generate
* 
* @return      string       Salt string
*/
function construct_password_salt($length = 5)
{
        $salt = '';
        for ($i = 0; $i < $length; $i++)
        {
                $salt .= chr(rand(33, 126));
        }
        $salt = str_replace(",", "_", $salt);
        $salt = str_replace("'", "^", $salt);
        $salt = str_replace('"', '*', $salt);
        $salt = str_replace("\\", '+', $salt);
        $salt = str_replace("\\\\", '-', $salt);
        
        return $salt;
}
/**
* Converts an integer into a UTF-8 string
*
* @param        integer	    Integer to be converted into utf8
*
* @return	string
*/
//june21
function set_watch_user($userid,$project)
{
  global $ilance, $ilconfig;
     $sql = $ilance->db->query("
	    SELECT user_id, watching_project_id, comment
        FROM " . DB_PREFIX . "watchlist
		WHERE state = 'auction'	
		and user_id = '".$userid."'   
		and watching_project_id = '".$project."'
		
       ", 0, null, __FILE__, __LINE__);
	   
	   if ($ilance->db->num_rows($sql) > 0)
        {
                $wat = $ilance->db->fetch_array($sql);
		                   
					
					$to = $wat['comment'];	
						
				return $to; 
					
						   
		   
		}
		
		 
}
function convert_int2utf8($intval)
{
        $intval = intval($intval);
        switch ($intval)
        {
                // 1 byte, 7 bits
                case 0:
                return chr(0);
            
                case ($intval & 0x7F):
                return chr($intval);
        
                // 2 bytes, 11 bits
                case ($intval & 0x7FF):
                return chr(0xC0 | (($intval >> 6) & 0x1F)) . chr(0x80 | ($intval & 0x3F));
        
                // 3 bytes, 16 bits
                case ($intval & 0xFFFF):
                return chr(0xE0 | (($intval >> 12) & 0x0F)) . chr(0x80 | (($intval >> 6) & 0x3F)) . chr (0x80 | ($intval & 0x3F));
        
                // 4 bytes, 21 bits
                case ($intval & 0x1FFFFF):
                return chr(0xF0 | ($intval >> 18)) . chr(0x80 | (($intval >> 12) & 0x3F)) . chr(0x80 | (($intval >> 6) & 0x3F)) . chr(0x80 | ($intval & 0x3F));
        }
}
/**
* Function to return a string where HTML entities have been converted to their original characters
*
* @param	string	     html string to parse
* @param	bool         convert unicode string back from HTML entities?
*
* @return	string
*/
function un_htmlspecialchars($text = '', $parseunicode = false)
{
        if ($parseunicode)
        {
                $text = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $text);
        }
        
        return str_replace(array('&lt;', '&gt;', '&quot;', '&amp;'), array('<', '>', '"', '&'), $text);
}
/**
* Function to show page results within the pagnation function like showing results [first] to [last] of [total] pages emulation
*
* @param	integer	     page number we are currently viewing
* @param        string       per page limit
* @param	string       total pages
*
* @return	array        Returns an array with [first] and [last] page number results
*/
function construct_start_end_array($pagenum = 0, $perpage = 0, $total = 0)
{
        $first = $perpage * ($pagenum - 1);
        $last = $first + $perpage;
        if ($last > $total)
        {
                $last = $total;
        }
        $first++;
        
        return array('first' => number_format($first), 'last' => number_format($last));
}
/**
* Function for printing the prev and next links to allow users to navigate through result listings.
*
* @param       integer        total number of rows
* @param       integer        row limit (per page)
* @param       integer        current page number
* @param       integer        (depreciated)
* @param       string         current page url
* @param       string         custom &page= name
* @param       boolean        include a question mark ? after the $scriptpage url?
*
* @return      string         HTML representation of the page navigator
*/
function print_pagnation($number = 0, $rowlimit = 0, $page = 0, $counter = 0, $scriptpage = '', $custompagename = 'page', $questionmarkfirst = false)
{
        global $ilance, $myapi, $phrase, $ilconfig;
        $html = '';
        
        if (empty($custompagename))
        {
                $custompagename = 'page';
        }
        
        $totalpages = ceil(($number / $rowlimit));
        if ($totalpages == 0)
        {
                $totalpages = 1;
        }
        
        //if ($number > $rowlimit)
        //{
                $html .= '<div style="margin-top:6px"><table cellpadding="4" cellspacing="0" border="0" width="100%" align="center" dir="' . $ilconfig['template_textdirection'] . '"><tr>';
                
                $startend = construct_start_end_array($page, $rowlimit, $number);
                $html .= '<td class="" style="padding:4px"><span style="float:left" class="gray">' . $ilance->language->construct_phrase($phrase['_showing_results_x_to_x_of_x'], array('<strong>' . $startend['first'] . '</strong>', '<strong>' . $startend['last'] . '</strong>', '<strong> ' . number_format($number) . '</strong>')) . '.</span> <span style="float:right"><strong>' . $phrase['_page'] . ' ' . number_format($page) . '</strong> ' . $phrase['_of'] . ' <strong>' . number_format($totalpages) . '</strong></span></td>';
                $html .= '<td class="" width="1" style="padding-left:12px"></td>';
                
                if ($page > 1)
                {
                        $html .= '<td class="" width="1" align="left" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=1&amp;pp=' . $rowlimit . '" title="' . $phrase['_goto_first_page'] . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_left_first.gif" border="0" alt="' . $phrase['_goto_first_page'] . '" /></a></td>';
                        $html .= '<td class="" width="1" align="left" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" title="' . $phrase['_prev_page'] . ': ' . ($page - 1) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_left.gif" border="0" alt="' . $phrase['_prev_page'] . ': ' . ($page - 1) . '" /></a></td>';
                        $html .= '<td class="" width="1" style="padding-right:12px"></td>';
                }
                
                if ($page > 10)
                {
                        $inc = floor(($page - 3) / 3);
                        for ($i = 1; $i < $page - 3; $i += $inc)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                else
                {
                        for ($i = 1; $i < $page - 3; $i++)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                
                for ($i = $page - 3; $i < $page; $i++)
                {
                        if ($i > 0)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                
                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><strong>' . $page . '</strong></td>';
                
                for ($i = $page + 1; $i <= $page + 3; $i++)
                {
                        if ($i > 0 AND $i <= $totalpages)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                
                if (($totalpages - $page) > 10)
                {
                        $temp = '';
                        
                        $inc = floor(($totalpages - ($page + 3)) / 3);
                        for ($i = $totalpages; $i > $page + 3; $i -= $inc)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $temp = '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                        
                        $html .= $temp;
                }
                else if ($totalpages - $page > 3)
                {
                        for ($i = $page + 4; $i <= $totalpages; $i++)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                
                if ($page < $totalpages)
                {
                        $html .= '<td class="" align="right" width="1" style="padding-left:12px"></td>';
                        $html .= '<td class="" align="right" width="1" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="' . $phrase['_next_page'] . ': ' . number_format(($page + 1)) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_right.gif" border="0" alt="' . $phrase['_next_page'] . ': ' . number_format(($page + 1)) . '" /></a></td>';
                        $html .= '<td class="" align="right" width="1" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($totalpages) . '&amp;pp=' . $rowlimit . '" title="' . $phrase['_goto_last_page'] . ': ' . number_format($totalpages) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_right_last.gif" border="0" alt="' . $phrase['_goto_last_page'] . ': ' . number_format($totalpages) . '" /></a></td>';
                }
                
                $html .= '</tr></table></div>';
        //}
        
        return $html;
}
/**
* Function is new fuction specialy for series and search style in SEO <h1> tag bug for printing the prev and next links to allow users to navigate through result listings.
*
* @param       integer        total number of rows
* @param       integer        row limit (per page)
* @param       integer        current page number
* @param       integer        (depreciated)
* @param       string         current page url
* @param       string         custom &page= name
* @param       boolean        include a question mark ? after the $scriptpage url?
*
* @return      string         HTML representation of the page navigator
*/
function print_pagnation_h1_seo($number = 0, $rowlimit = 0, $page = 0, $counter = 0, $scriptpage = '', $custompagename = 'page', $questionmarkfirst = false)
{
        global $ilance, $myapi, $phrase, $ilconfig;
        $html = '';
        
        if (empty($custompagename))
        {
                $custompagename = 'page';
        }
        
        $totalpages = ceil(($number / $rowlimit));
        if ($totalpages == 0)
        {
                $totalpages = 1;
        }
        
        //if ($number > $rowlimit)
        //{
                 $html .= '&nbsp&nbsp<span  class="gray">';
                
                $startend = construct_start_end_array($page, $rowlimit, $number);
                $html .= '' . $ilance->language->construct_phrase($phrase['_showing_results_x_to_x_of_x'], array('<strong>' . $startend['first'] . '</strong>', '<strong>' . $startend['last'] . '</strong>', '<strong> ' . number_format($number) . '</strong>')) . '. <span style="float:right"><strong>' . $phrase['_page'] . ' ' . number_format($page) . '</strong> ' . $phrase['_of'] . ' <strong>' . number_format($totalpages) . '</strong></span>';
               
                
                if ($page > 1)
                {
                        $html .= '<a href="' . $scriptpage . '&amp;' . $custompagename . '=1&amp;pp=' . $rowlimit . '" title="' . $phrase['_goto_first_page'] . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_left_first.gif" border="0" alt="' . $phrase['_goto_first_page'] . '" /></a>';
                        $html .= '<a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" title="' . $phrase['_prev_page'] . ': ' . ($page - 1) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_left.gif" border="0" alt="' . $phrase['_prev_page'] . ': ' . ($page - 1) . '" /></a>';
                       
                }
                
                if ($page > 10)
                {
                        $inc = floor(($page - 3) / 3);
                        for ($i = 1; $i < $page - 3; $i += $inc)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span>';
                        }
                }
                else
                {
                        for ($i = 1; $i < $page - 3; $i++)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span>';
                        }
                }
                
                for ($i = $page - 3; $i < $page; $i++)
                {
                        if ($i > 0)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span>';
                        }
                }
                
                  $html .= '<span style="padding-left:10px;"><strong>' . $page . '</strong></span>';
                
                for ($i = $page + 1; $i <= $page + 3; $i++)
                {
                        if ($i > 0 AND $i <= $totalpages)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span>';
                        }
                }
                
                if (($totalpages - $page) > 10)
                {
                        $temp = '';
                        
                        $inc = floor(($totalpages - ($page + 3)) / 3);
                        for ($i = $totalpages; $i > $page + 3; $i -= $inc)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $temp = '<span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span>';
                        }
                        
                        $html .= $temp;
                }
                else if ($totalpages - $page > 3)
                {
                        for ($i = $page + 4; $i <= $totalpages; $i++)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span>';
                        }
                }
                
                if ($page < $totalpages)
                {
                        
                        $html .= '<a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="' . $phrase['_next_page'] . ': ' . number_format(($page + 1)) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_right.gif" border="0" alt="' . $phrase['_next_page'] . ': ' . number_format(($page + 1)) . '" /></a>';
                        $html .= '<a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($totalpages) . '&amp;pp=' . $rowlimit . '" title="' . $phrase['_goto_last_page'] . ': ' . number_format($totalpages) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_right_last.gif" border="0" alt="' . $phrase['_goto_last_page'] . ': ' . number_format($totalpages) . '" /></a>';
                }
                
                $html .= '</span>';
        //}
        
        return $html;
}
/**
* Function to shorten a string of characters using an argument limiter as the amount of characters to reveal
*
* @param	string	     html string
* @param	integer      limiter amount (ie: 50)
*
* @return	string       HTML representation of the shortened string
*/
function shorten($string = '', $limit)
{
        if (mb_strlen($string) > $limit)
        {
                $string = mb_substr($string, 0, $limit);
                if (($pos = mb_strrpos($string, ' ')) !== false)
                {
                        $string = mb_substr($string, 0, $pos);
                }
                return $string . '...';
        }
        
        return $string;
}
/**
* Function to cut a string of characters apart using an argument limiter as the amount of characters to cut between
*
* @param	string	     html string
* @param	integer      limiter amount (ie: 50)
*
* @return	string       HTML representation of the string which has been cut
*/
function cutstring($string = '', $limit)
{
        if (mb_strlen($string) > $limit)
        {
                $string = mb_substr($string, 0, $limit);
                if (($pos = mb_strrpos($string, ' ')) !== false)
                {
                        $string = mb_substr($string, 0, $pos);
                }
                return $string;
        }
        
        return $string;
}
/**
* Function to log an event based on a particular action engaged by a user data mined within the AdminCP > Audit Manager
*
* @param	integer      user id
* @param        string       script
* @param        string       cmd invoked
* @param        string       sub cmd invoked
* @param        string       message of action performed
*
* @return	nothing
*/
function log_event($userid = 0, $script = '', $cmd = '', $subcmd = '', $otherinfo = '')
{
        global $ilance, $myapi;
        
        $ilance->db->query("
                INSERT INTO " . DB_PREFIX . "audit
                (logid, user_id, script, cmd, subcmd, otherinfo, datetime, ipaddress)
                VALUES
                (NULL,
                '" . intval($userid) . "',
                '" . $ilance->db->escape_string($script) . "',
                '" . $ilance->db->escape_string($cmd) . "',
                '" . $ilance->db->escape_string($subcmd) . "',
                '" . $ilance->db->escape_string($otherinfo) . "',
                '" . TIMESTAMPNOW . "',
                '" . $ilance->db->escape_string(IPADDRESS) . "')
        ", 0, null, __FILE__, __LINE__);
}
/**
* Function to generate a human-readable password where the password length is based on a supplied argument
*
* @param	integer      password character length
*
* @return	string       Generated human-readable password
*/
function construct_password($len = 8)
{
        $vocali = array('a','e','i','o','u');
        $dittonghi = array('ae','ai','ao','au','ea','ei','eo','eu','ia','ie','io','iu','ua','ue','ui','uo');
        $cons = array('b','c','d','f','g','h','k','l','n','m','p','r','s','t','v','z');
        $consdoppie = array('bb','cc','dd','ff','gg','ll','nn','mm','pp','rr','ss','tt','vv','zz');
        $consamiche = array('bl','br','ch','cl','cr','dl','dm','dr','fl','fr','gh','gl','gn','gr','lb','lp','ld','lf','lg','lm','lt','lv','lz','mb','mp','nd','nf','ng','nt','nv','nz','pl','pr','ps','qu','rb','rc','rd','rf','rg','rl','rm','rn','rp','rs','rt','rv','rz','sb','sc','sd','sf','sg','sl','sm','sn','sp','sr','st','sv','tl','tr','vl','vr');
        
        $listavocali = array_merge($vocali, $dittonghi);
        $listacons = array_merge($cons, $consdoppie, $consamiche);
        $nrvocali = sizeof($listavocali);
        $nrconsonanti = sizeof($listacons);
        $loop = $len;
        $password = '';
        
        if (rand(1, 10) > 5)
        {
                $password = $cons[rand(1, sizeof($cons))];
                $password .= $listavocali[rand(1, $nrvocali)];
                $inizioc = true;
                $loop--;
        }
        for ($i = 0; $i < $loop; $i++)
        {
                $qualev = $listavocali[rand(1, $nrvocali)];
                $qualec = $listacons[rand(1, $nrconsonanti)];
                if (isset($inizioc))
                {
                        $password .= $qualec . $qualev;
                }
                else
                {
                        $password .= $qualev . $qualec;
                }
        }
        
        $password = mb_substr($password, 0, $len);
        if (in_array(mb_substr($password, ($len - 2), $len), $consdoppie))
        {
                $password = mb_substr($password, 0, ($len - 1)) . $listavocali[rand(1, $nrvocali)];
        }
        
        return $password;
}
/**
* Function to verify a referral clickthrough based on a supplied referral code being passed as one of the arguments
*
* @param	string       ip address
* @param        string       client browser agent
* @param        string       client referrer location (where click came from)
* @param        string       referral code being clicked
*
* @return	void
*/
function verify_referral_clickthrough($clientip = '', $clientbrowser = '', $clienturl = '', $rid = '')
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT rid
                FROM " . DB_PREFIX . "referral_clickthroughs
                WHERE rid = '" . $ilance->db->escape_string($rid) . "'
                    AND ipaddress = '" . $ilance->db->escape_string($clientip) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) == 0)
        {
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "referral_clickthroughs
                        (rid, date, browser, ipaddress, referrer)
                        VALUES (
                        '" . $ilance->db->escape_string($rid) . "',
                        '" . DATETIME24H . "',
                        '" . $ilance->db->escape_string($clientbrowser) . "',
                        '" . $ilance->db->escape_string($clientip) . "',
                        '" . $ilance->db->escape_string($clienturl) . "')
                ", 0, null, __FILE__, __LINE__);
        }
}
/**
* Function to initialize the referral code tracking system
*
* @return	void
*/
function init_referral_tracker()
{
        global $ilance, $myapi, $ilconfig;
        
        $refrid = (isset($_REQUEST['rid']) AND !empty($_REQUEST['rid'])) ? $_REQUEST['rid'] : '';
        $remote = IPADDRESS;
        $ragent = USERAGENT;
        $rrefer = REFERRER;
        
        if (!empty($refrid))
        {
                $sql = $ilance->db->query("
                        SELECT rid
                        FROM " . DB_PREFIX . "users
                        WHERE rid = '" . $ilance->db->escape_string($refrid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        set_cookie('rid', $refrid, true);
                        verify_referral_clickthrough($remote, $ragent, $rrefer, $refrid);
                }
        }
}
/**
* Function to Update Referal Count
*
* @return	void
*/
function update_referal_count($name)
{
        global $ilance, $myapi, $ilconfig;
        
       $selref = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "referal_id 
                               WHERE referalcode = '".$ilance->db->escape_string($name)."' 
							 ");
						if ($ilance->db->num_rows($selref) > 0)
		                { 		 
							   $resref = $ilance->db->fetch_array($selref);
							   $refcount = $resref['registercount'] + 1;
							   $sqlreff= $ilance->db->query("UPDATE " . DB_PREFIX . "referal_id
                                                             SET registercount = '" .$refcount. "'
                                                             WHERE referalcode = '".$ilance->db->escape_string($name)."'
														  ");
					    }									 
							
}
/**
* Function to hard refresh a page and to show a please wait while we direct you to the specified location message
*
* @param        string        url to send user
* @param        string        custom argument (unused)
*
* @return	void
*/
function refresh($url = '', $custom = '')
{
        global $ilance, $myapi, $ilconfig, $phrase, $ilpage, $headinclude;
        
        ($apihook = $ilance->api('refresh_start')) ? eval($apihook) : false;
        
        if (!empty($custom))
        {
                $url = $custom;
        }
        
        if ($ilconfig['globalfilters_refresh'] == false)
        {
                header("Location: $url");
                exit;
        }
        
        $jsurl = "\'" . $url . "\'; return false";
        
        $html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="' . $ilconfig['template_textdirection'] . '" lang="' . $ilconfig['template_languagecode'] . '">
<head>
<title>' . $phrase['_processing_your_request_dot_dot_dot'] . '</title>
<meta http-equiv="Refresh" content="1; URL=' . $url . '">
<meta http-equiv="Content-Type" content="text/html; charset=' . $ilconfig['template_charset'] . '">
' . $headinclude . '
</head>
<body>
<center>
<div>
<div style="width:540px; padding-top:150px">
<form action="' . $url . '" method="get" accept-charset="UTF-8">
<div class="block-wrapper">
        <div class="block" align="left">
        
                <div class="block-top">
                        <div class="block-right">
                                <div class="block-left"></div>
                        </div>
                </div>
                
                <div class="block-header">' . $phrase['_processing_your_request_dot_dot_dot'] . '</div>
                
                <div class="block-content" style="padding:0px">
                        
                        <table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" dir="' . $ilconfig['template_textdirection'] . '">
                        <tr>
                            <td>
                            
                                <div style="padding-top:3px">
                                    <blockquote>
                                        <p style="font-size:13px"><strong>' . SITE_NAME . ' ' . $phrase['_is_processing_your_request'] . '</strong></p>' . $phrase['_if_you_do_not_wish_to_wait_any_longer'] . ', <span class="blue"><a href="' . $url . '" target="_self">' . $phrase['_click_here'] . '</a></span>.
                                        <!--
                                        <script type="text/javascript">
                                        document.write(\'<div style="margin-top:9px">\');
                                        document.write(\'<input type="submit" class="buttons" style="font-size:13px" value=" ' . $phrase['_continue'] . ' " accesskey="s" onclick="window.location=' . $jsurl . '" />\');
                                        document.write(\'</div>\');
                                        </script>-->
                                    </blockquote>
                                </div>
                                
                                
                                
                            </td>
                        </tr>
                        </table>
                        
                </div>
                
                <div class="block-footer">
                                <div class="block-right">
                                                <div class="block-left"></div>
                                </div>
                </div>
                        
        </div>
</div>
</form>
</div>
</div>
</center>
</body>
</html>';
        
        ($apihook = $ilance->api('refresh_end')) ? eval($apihook) : false;
        
        echo $html;
}
/**
* Function to shorten a string based on a supplied argument length to cut off as well as a custom symbol
* to represent at the end of the string (ie: .....)
*
* @param        string        text
* @param        integer       limiter length
* @param        string        limiter symbol (ie: .....)
*
* @return	string        Returns the formatted text with the ending limiter symbol to represent more text is available
*/
function short_string($text = '', $length, $symbol = ' .....')
{
        $length_text = mb_strlen($text);
        $length_symbol = mb_strlen($symbol);
	
        if ($length_text <= $length OR $length_text <= $length_symbol OR $length <= $length_symbol)
        {
                return($text);
        }
        else
        {
                if ((mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") > mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".") + 25) && (mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") < mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ",") + 25))
                {
                        return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ")) . $symbol);
                }
                else if (mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") < mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".") + 25)
                {
                        return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".")) . $symbol);
                }
                else if (mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") < mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ",") + 25)
                {
                        return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".")) . $symbol);
                }
                else
                {
                        return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ")) . $symbol);
                }
        }
}
/**
* Function to fetch the extension of a filename being passed as the argument
*
* @param        string        filename including the file extension
*
* @return	string        Returns the file extension (ie: gif) without the period
*/
function fetch_extension($filename = '')
{
        $dot = mb_substr(mb_strrchr($filename, '.'), 1);
        return $dot;
}
/**
* Function to fetch and print the total income reported by a particular user
*
* @param        integer       user id
*
* @return	string        Returns the formatted income reported string (ie: USD$50,000.00)
*/
function print_income_reported($userid = 0)
{
        global $ilance, $myapi, $ilconfig, $phrase;
        
	$earnings = '-';
        $sql = $ilance->db->query("
                SELECT income_reported AS earnings, displayfinancials
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $result = $ilance->db->fetch_array($sql, DB_ASSOC);
                if ($result['earnings'] > 0)
                {
                        $earnings = $ilance->currency->format($result['earnings'], $ilconfig['globalserverlocale_defaultcurrency']);
                }
		
		if ($result['displayfinancials'] == '0')
		{
			// is admin viewing?
			//if (empty($_SESSION['ilancedata']['user']['isadmin']) OR $_SESSION['ilancedata']['user']['isadmin'] == '0')
			//{
				$earnings = $phrase['_private'];
			//}
		}
        }
        
        return $earnings;
}
/**
* Function to fetch and print the total income spent by a particular user
*
* @param        integer       user id
*
* @return	string        Returns the formatted income spent string (ie: USD$50,000.00)
*/
function print_income_spent($userid = 0)
{
        global $ilance, $myapi, $ilconfig;
        
        $sql = $ilance->db->query("
                SELECT income_spent AS spent
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $result = $ilance->db->fetch_array($sql, DB_ASSOC);
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
                {
                        $spent = $ilance->currency->format($result['spent'], $ilconfig['globalserverlocale_defaultcurrency']);
                }
                else
                {
                        if (!empty($_SESSION['ilancedata']['user']['currencyid']))
                        {
                                $spent = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $result['spent']);
                        }
                        else
                        {
                                $spent = $ilance->currency->format($result['spent'], $ilconfig['globalserverlocale_defaultcurrency']);
                        }
                }
        }
        else
        {
                $spent = $ilance->currency->format(0, $ilconfig['globalserverlocale_defaultcurrency']);
        }
        
        return $spent;
}
/**
* Function to print the total number of service auction bid proposals awarded for this particular user
*
* @param        integer       user id
* @param        bool          force an update right now?
*
* @return	string        Returns number of service bids awarded
*/
function fetch_service_bids_awarded($userid = 0, $doupdate = false)
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS bidsawarded
                FROM " . DB_PREFIX . "project_bids
                WHERE user_id = '" . intval($userid) . "'
                    AND bidstatus = 'awarded'
                    AND state = 'service'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                if ($doupdate)
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET serviceawards = '" . $res['bidsawarded'] . "'
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                }
                
                return $res['bidsawarded'];
        }
        
        return '-';
}
/**
* Function to print the total number of product auction bids awarded for this particular user
*
* @param        integer       user id
* @param        bool          force an update right now?
*
* @return	string        Returns number of product bids awarded
*/
function fetch_product_bids_awarded($userid = 0, $doupdate = false)
{
        global $ilance, $myapi;
	
        $sql = $ilance->db->query("
		SELECT COUNT(*) AS bidsawarded
		FROM " . DB_PREFIX . "project_bids
		WHERE user_id = '" . intval($userid) . "'
			AND bidstatus = 'awarded'
			AND state = 'product'
	", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                $awards = $res['bidsawarded'];        
                if ($doupdate)
                {
                        $ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET productawards = '" . intval($awards) . "'
				WHERE user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
                }
		
                return $awards;
        }
        
        return 0;
}
/**
* Function to print the total number of service feedback reviews for this particular user
*
* @param        integer       user id
*
* @return	string        Returns number of service feedback reviews reported
*/
function fetch_service_reviews_reported($userid = 0)
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS reviewcount
                FROM " . DB_PREFIX . "feedback
                WHERE for_user_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                $result = $res['reviewcount'];
                
                return $result;
        }
        
        return 0;
}
/**
* Function to print the total number of product feedback reviews for this particular user
*
* @param        integer       user id
*
* @return	string        Returns number of product feedback reviews reported
*/
function fetch_product_reviews_reported($userid = 0)
{
        global $ilance, $myapi;
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS reviewcount
                FROM " . DB_PREFIX . "feedback
                WHERE for_user_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                $result = $res['reviewcount'];
                
                return $result;
        }
        
        return 0;
}
/**
* Function to print the online status of a particular user.  This function is also LanceAlert ready
* where if the user is online and logged into the app it will show the online status of the IM user status
* (away, busy, online, dnd, etc) vs the status of online or offline
*
* @param        integer       user id
* @param        string        offline user color (example: gray)
* @param        string        online user color (example: green)
*
* @return	string        Returns the HTML representation of the online status
*/
function print_online_status($userid = 0, $offlinecolor = '', $onlinecolor = '')
{
        global $ilance, $myapi, $phrase, $ilconfig, $show;
        
        $isonline = '<span class="' . $offlinecolor . '">' . $phrase['_offline'] . '</span>';
        
        // are we online?
        if (isset($show['lancealert']) AND $show['lancealert'])
        {
                // we don't appear to be online the web site, are we connected via lancealert?
                $sqlla = $ilance->db->query("
                        SELECT u.username, s.userID, s.status
                        FROM " . DB_PREFIX . "alert_sessions s,
                        " . DB_PREFIX . "users u
                        WHERE u.username = s.userID
                                AND u.user_id = '" . intval($userid) . "'
                                AND u.status = 'active'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlla) > 0)
                {
                        $resla = $ilance->db->fetch_array($sqlla);
                        switch ($resla['status'])
                        {
                                case '0':
                                {
                                        // online
                                        $isonline = '<a href="lamsgr:SendIM?' . $resla['userID'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_online.gif" border="0" alt="IM Status: Online .. Click to Chat" /></a>';
                                        break;
                                }                            
                                case '1':
                                {
                                        // busy
                                        $isonline = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_online.gif" border="0" alt="IM Status: Busy" />';
                                        break;
                                }                            
                                case '2':
                                {
                                        // do not disturb
                                        $isonline = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_online.gif" border="0" alt="IM Status: Do Not Disturb" />';
                                        break;
                                }                            
                                case '3':
                                {
                                        // away
                                        $isonline = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_online.gif" border="0" alt="IM Status: Away" />';
                                        break;
                                }                            
                                case '4':
                                {
                                        // offline / invisible
                                        $isonline = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_offline.gif" border="0" alt="IM Status: '.$phrase['_offline'].'" />';
                                        break;
                                }
                        }
                }
                else 
                {
                        $sql = $ilance->db->query("
                                SELECT u.user_id, s.title
                                FROM " . DB_PREFIX . "sessions s,
                                " . DB_PREFIX . "users u
                                WHERE u.user_id = '" . intval($userid) . "'
                                        AND u.user_id = s.userid
                                        AND isuser = '1'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $isonline = '<span class="' . $onlinecolor . '">' . $phrase['_online'] . '</span>';
                        }	
                }
        }
        else 
        {
                $sql = $ilance->db->query("
                        SELECT u.user_id, s.title
                        FROM " . DB_PREFIX . "sessions s,
                        " . DB_PREFIX . "users u
                        WHERE u.user_id = '" . intval($userid) . "'
                                AND u.user_id = s.userid
                                AND isuser = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $isonline = '<span class="' . $onlinecolor . '">' . $phrase['_online'] . '</span>';
                }
        }
        
        return $isonline;	
}
/**
* Function to generate a referral code based on a limiter argument
*
* @param        integer       length limiter
*
* @return	string        Returns the formatted referral code
*/
function create_referral_code($length = 6)
{
        $rid = mb_substr(mb_ereg_replace("[^A-Z]", "", crypt(time())) . mb_ereg_replace("[^0-9]", "", crypt(time())) . mb_ereg_replace("[^A-Z]", "", crypt(time())), 0, $length);
        
        return $rid;
}
function construct_form_name($length = 10)
{
        $formname = mb_substr(mb_ereg_replace("[^a-zA-Z]", "", crypt(time())) . mb_ereg_replace("[^0-9]", "", crypt(time())) . mb_ereg_replace("[^a-zA-Z]", "", crypt(time())), 0, $length);
        
        return $formname;
}
/**
* Function to generate a unique user account number for the billing and payments system
*
* @return	string        Returns a formatted account number
*/
function construct_account_number()
{
        $first = rand(100, 999);
        $second = rand(100, 999);
        $third = rand(100, 999);
        $fourth = rand(100, 999);
        $fifth = rand(0, 9);
        
        return $first . $second . $third . $fourth . $fifth;
}
/**
* Function to generate an account bonus feature to a particular user
*
* @param        integer       user id
* @param        string        mode (active, inactive, etc)
*
* @return	string        Returns the amount (if any) of the account bonus rate
*/
function construct_account_bonus($userid = 0, $mode = 'active')
{
        global $ilance, $myapi, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
        
        $sql = $ilance->db->query("
                SELECT first_name, username, email
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                
                $username = stripslashes($res['username']);
                $firstname = stripslashes($res['first_name']);
                $email = $res['email'];
                
                $account_bonus = '0.00';
        
                // let's determine the email sending logic        
                if (isset($mode))
                {
                        switch ($mode)
                        {
                                case 'active':
                                {
                                        // this is an active member registering so we will:
                                        // - create a credit transaction
                                        // - send bonus email to new member
                                        // - send bonus email to admin
                                        // - return the account bonus amount to the calling script
                                        if ($ilconfig['registrationupsell_bonusactive'] AND $ilconfig['registrationupsell_amount'] > 0)
                                        {
                                                $account_bonus = sprintf("%01.2f", $ilconfig['registrationupsell_amount']);
                                                
                                                $ilance->accounting = construct_object('api.accounting');
                                                
                                                $newinvoiceid = $ilance->accounting->insert_transaction(
                                                        0,
                                                        0,
                                                        0,
                                                        intval($userid),
                                                        0,
                                                        0,
                                                        0,
                                                        $ilconfig['registrationupsell_bonusitemname'],
                                                        sprintf("%01.2f", $ilconfig['registrationupsell_amount']),
                                                        sprintf("%01.2f", $ilconfig['registrationupsell_amount']),
                                                        'paid',
                                                        'credit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        DATETIME24H,
                                                        $phrase['_thank_you_for_becoming_a_member_on_our_marketplace_please_enjoy_your_stay'],
                                                        0,
                                                        0,
                                                        1,
                                                        '',
                                                        0,
                                                        0
                                                );
                                                
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET isregisterbonus = '1'
                                                        WHERE invoiceid = '" . intval($newinvoiceid) . "'
                                                ");
                                                
                                                $ilance->email = construct_dm_object('email', $ilance);
                                                
                                                $ilance->email->mail = $email;
                                                $ilance->email->slng = fetch_site_slng();
                        
                                                $ilance->email->get('registration_account_bonus');		
                                                $ilance->email->set(array(
                                                        '{{user}}' => $firstname,
                                                        '{{username}}' => $username,
                                                        '{{bonus_amount}}' => $ilance->currency->format($ilconfig['registrationupsell_amount']),
                                                ));
                                                
                                                $ilance->email->send();
                                                
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                
                                                $ilance->email->get('registration_account_bonus_admin');		
                                                $ilance->email->set(array(
                                                        '{{user}}' => $firstname,
                                                        '{{username}}' => $username,
                                                        '{{bonus_amount}}' => $ilance->currency->format($ilconfig['registrationupsell_amount']),
                                                ));
                                                
                                                $ilance->email->send();
                                        }
                                        break;
                                }
                                case 'unverified':
                                {
                                        break;
                                }                            
                                case 'moderated':
                                {
                                        break;
                                }
                        }
                }
        }
        
        return $account_bonus;
}
/**
* Function to print an action was successful used mainly within the AdminCP
*
* @param        string      success message to display
* @param        string      redirect to url location
*
* @return	string      Returns the HTML representation of the action success template
*/
function print_action_success($notice = '', $admurl = '')
{
	global $ilance, $login_include_admin, $iltemplate, $ilanceversion, $phrase, $v3nav, $page_title, $area_title, $ilconfig, $ilpage, $show;
        
        $notice_temp = $notice; $admurl_temp = $admurl;
        global $notice, $admurl;
        
        $area_title = 'AdminCP - ' . $phrase['_administrative_action_complete'] . ' ' . $phrase['_for'] . ' ' . $_SESSION['ilancedata']['user']['username'];
        $page_title = SITE_NAME . ' - AdminCP - ' . $phrase['_administrative_action_complete'];
        
        ($apihook = $ilance->api('print_action_success')) ? eval($apihook) : false;
        
        $admurl = $admurl_temp; $notice = $notice_temp;
        
        $pprint_array = array('ilanceversion','login_include_admin','notice','admurl','input_style','title','description','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','project_buyer','projects_posted','projects_awarded','project_currency','project_attachment','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_access_success_end')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'action_success.html', 1);
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'v3nav');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
/**
* Function to print an action failed used mainly within the AdminCP
*
* @param        string      error message to display
* @param        string      redirect to url location
*
* @return	string      Returns the HTML representation of the action failed template
*/
function print_action_failed($error = '', $admurl = '')
{
        global $ilance, $myapi, $login_include_admin, $ilanceversion, $phrase, $v3nav, $page_title, $area_title, $ilconfig, $ilpage;
        
        $admurl_temp = $admurl; $error_temp = $error;
        
        global $admurl, $error, $ilance;
        
        $admurl = $admurl_temp; $error = $error_temp;
        
        $pprint_array = array('login_include_admin','ilanceversion','error','admurl','input_style','title','description','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','project_buyer','projects_posted','projects_awarded','project_currency','project_attachment','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','rid','default_exchange_rate','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_action_failed_end')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'action_failed.html', 1);
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'v3nav');
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
/**
* Function to print a human-readable filesize based on bytes being sent as an argument
*
* @param        integer     size in bytes
*
* @return	string      Returns formatted filesize like 1.3KB, 2.5MB, 1.7GB, etc
*/
function print_filesize($bytes = 0)
{
        if (mb_strlen($bytes) <= 9 && mb_strlen($bytes) >= 7)
        {
                $format = number_format($bytes / 1048576,1) . ' MB';
        }
        else if (mb_strlen($bytes) >= 10)
        {
                $format = number_format($bytes / 1073741824,1) . ' GB';
        }
        else
        {
                $format = number_format($bytes / 1024,1) . ' KB';
        }
        
        return $format;
}
/**
* Function to insert a public message from a detailed auction page
*
* @param        integer     project id
* @param        integer     seller id
* @param        integer     from id
* @param        string      user name
* @param        string      message being posted
* @param        integer     is visible?
*
* @return	void
*/
function insert_public_message($projectid = 0, $sellerid = 0, $fromid = 0, $username = '', $message = '', $visible = '1')
{
        global $ilance, $myapi, $ilpage;
        
        $ilance->db->query("
                INSERT INTO " . DB_PREFIX . "messages
                (messageid, project_id, user_id, username, message, date, visible)
                VALUES(
                NULL,
                '" . intval($projectid) . "',
                '" . intval($fromid) . "',
                '" . $ilance->db->escape_string($username) . "',
                '" . $ilance->db->escape_string($message) . "',
                '" . DATETIME24H . "',
                '" . intval($visible) . "')
        ", 0, null, __FILE__, __LINE__);
        
        // fetch seller info
        $seller = fetch_user('username', $sellerid);
        $selleremail = fetch_user('email', $sellerid);
        $auctiontype = fetch_auction('project_state', intval($projectid));
        $ownerid = fetch_auction('user_id', intval($projectid));
        
        // todo: check for seo
        if ($auctiontype == 'service')
        {
                $url = HTTP_SERVER . $ilpage['rfp'] . '?id='.intval($projectid).'#messages';	
        }
        else 
        {
                $url = HTTP_SERVER . $ilpage['merch'] . '?id='.intval($projectid).'#messages';
        }
        
        if ($ownerid != $fromid)
        {
	        $ilance->email = construct_dm_object('email', $ilance);
	        
	        $ilance->email->slng = fetch_user_slng(intval($sellerid));
	        $ilance->email->mail = $selleremail;
	                
	        $ilance->email->get('new_public_message');		
	        $ilance->email->set(array(
	                '{{seller}}' => $seller,
	                '{{sender}}' => $_SESSION['ilancedata']['user']['username'],
	                '{{url}}' => $url,
	        ));
	        
	        $ilance->email->send();
        }
}
/**
* Function to print the latest feedback received based on a particular user
*
* @param        integer     user id
* @param        string      feedback type (service/product)
*
* @return	string      Returns HTML representation of the latest feedback response received
*/
function print_latest_feedback_received($userid = 0, $feedbacktype = '', $shownone = false)
{
        global $ilance, $myapi, $ilpage, $phrase;
        
        $sql = $ilance->db->query("
                SELECT comments
                FROM " . DB_PREFIX . "feedback
                WHERE for_user_id = '" . intval($userid) . "'
                ORDER BY id DESC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                return stripslashes($res['comments']);
        }
        
        return '';
}
/**
* Function to print a valid date and time string based on a unix timestamp
*
* @param        integer     unix timestamp
*
* @return	string      Returns formatted date time string (YYYY-MM-DD HH:MM:SS)
*/
function print_datetime_from_timestamp($time)
{
        return date("Y-m-d H:i:s", $time);
}
/**
* Function wrapper for strtotime
*
* @param        string      date range
*
* @return	string      Returns strtotime formatted string
*/
function print_convert_to_timestamp($str)
{
        return strtotime($str);
}
/**
* Function that will take an date array and rebuild into a valid date time string
*
* @param        array       date array
*
* @return	string      Returns valid date and time string
*/
function print_array_to_datetime($date, $time = '')
{
        if (empty($time))
        {
                $time = '00:00:00';
        }
        return $date[2] . '-' . $date[0] . '-' . $date[1] . ' ' . $time;
}
/**
* Function to handle display the date and time within ILance
*
* @param        string      date and time string
* @param        string      format of string (optional)
* @param        bool        should we show the time zone in the string?
* @param        bool        should we treat the date display with "Yesterday and Today" instead of the actual date?
*
* @return	string      Returns the formatted strftime() date and time string including a timezone identifier if requested
*/
function print_date($datetime, $format = '', $showtimezone = false, $yesterdaytoday = false)
{
        global $ilance, $myapi, $ilconfig, $phrase;
        
        $zone = '';
        if (empty($format))
        {
                $format = $ilconfig['globalserverlocale_globaltimeformat']; // "%d-%B-%d %I:%M:%S %p"
        }
        
        if ($showtimezone)
        {
                $zone = ' GMT' . $ilconfig['globalserverlocale_officialtimezone']; // GMT-5
				if( $ilconfig['globalserverlocale_officialtimezone']==-8)
				$zone=' Pacific Time';
        }
        
        if ($yesterdaytoday)
        {
                // pmbs, messages, etc
                if ($ilconfig['globalserverlocale_yesterdaytodayformat'])
                {
                        $tempdate = date('Y-m-d', $ilance->datetime->fetch_timestamp_from_datetime($datetime));
                        $difference = $ilance->datetime->fetch_timestamp_from_datetime(DATETIME24H) - $ilance->datetime->fetch_timestamp_from_datetime($datetime);
                        
                        if ($difference < 3600)
                        {
                                if ($difference < 120)
                                {
                                        $result = $phrase['_less_an_a_minute_ago'];
                                }
                                else
                                {
                                        $result = $ilance->language->construct_phrase("[x] minutes ago", intval($difference / 60));
                                }
                        }
                        else if ($difference < 7200)
                        {
                                $result = $phrase['_one_hour_ago'];
                        }
                        else if ($difference < 86400)
                        {
                                $result = $ilance->language->construct_phrase("[x] hours ago", intval($difference / 3600));
                        }                        
                }
        }
        
        if (empty($result))
        {
                $result = strftime($format, $ilance->datetime->fetch_timestamp_from_datetime($datetime)) . $zone;
        }
        
        $result = $ilance->common->entities_to_numeric($result);
        
        return $result;
}
/**
* Function to fetch and print a subscription's permission name
*
* @param        string      permission variable to process
*
* @return	string      Returns the subscription permission name
*/
function fetch_permission_name($variable = '')
{
        global $ilance, $myapi;
        
        $result = '';
        $query = $ilance->db->query("
                SELECT accesstext_" . $_SESSION['ilancedata']['user']['slng'] . " AS text, accessdescription_" . $_SESSION['ilancedata']['user']['slng'] . " AS description
                FROM " . DB_PREFIX . "subscription_permissions
                WHERE accessname = '" . $ilance->db->escape_string($variable) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($query) > 0)
        {
                $result = $ilance->db->fetch_array($query, DB_ASSOC);
        }
        
        return $result;
}
/**
* Function to determine if a user has a portfolio setup
*
* @param        integer     user id
*
* @return	bool        Returns true or false
*/
function has_portfolio($userid = 0)
{
        global $ilance, $myapi, $ilconfig;
        
        $sql = $ilance->db->query("
                SELECT u.username
                FROM " . DB_PREFIX . "portfolio as p,
                " . DB_PREFIX . "attachment as a,
                " . DB_PREFIX . "users as u,
                " . DB_PREFIX . "subscription_user as su
                WHERE p.user_id = '" . intval($userid) . "'
                    AND a.user_id = '" . intval($userid) . "'
                    AND u.user_id = '" . intval($userid) . "'
                    AND su.user_id = '" . intval($userid) . "'
                    AND su.active = 'yes'
                    AND p.portfolio_id = a.portfolio_id
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                return true;
        }
        
        return false;
}
/**
* Function to calculate and fetch the age (in years) based on a supplied birthday date
*
* @param        string      date (MM-DD-YYYY)
*
* @return	integer     Returns the age in years
*/
function fetch_age($birthday)
{
        $bday = explode('-', $birthday);
        if ($bday[2] < 1970)
        {
                $years = 1970 - $bday[2];
                $year = $bday[2] + ($years * 2);
                $stamp = mktime(0, 0, 0, $bday[1], $bday[0], $year) - ($years * 31556926 * 2);
        }
        else
        {
                $stamp = mktime(0, 0, 0, $bday[1], $bday[0], $bday[2]);
        }
        $age = floor((time()-$stamp)/31556926);
        
        return $age;
}
/**
* Function to handle parsing PHP code internally for add-on and product support in ILance
* and accepts code with or without <?php and ?> tags
*
* @param        string      php code to parse
*
* @return	mixed       Returns mixed output
*/
function parse_php_in_html($html_str)
{
        global $ilance, $myapi;
        
        preg_match_all("/(<\?php|<\?)(.*?)\?>/si", $html_str, $raw_php_matches);
        $php_idx = 0;
        while (isset($raw_php_matches[0][$php_idx]))
        {
                $raw_php_str = $raw_php_matches[0][$php_idx];
                $raw_php_str = str_replace("<?php", "", $raw_php_str);
                $raw_php_str = str_replace("?>", "", $raw_php_str);
                
                ob_start();
                eval("$raw_php_str;");
                $exec_php_str = ob_get_contents();
                ob_end_clean();
                
                $exec_php_str = str_replace("\$", "\\$", $exec_php_str);
                $html_str = preg_replace("/(<\?php|<\?)(.*?)\?>/si", $exec_php_str, $html_str, 1);
                $php_idx++;
        }
        
        return $html_str;
}
/**
* Function to fetch any field from the user table based on a number of access methods such as by user id, username or email address
*
* @param        string      field to fetch from the user table
* @param        integer     user id (optional; default)
* @param        string      username (optional)
* @param        string      email (optional)
* @param        boolean     show unknown phrase (default true)
*
* @return	string      Returns field value from the user table
*/
//karthik on sep06 for sales tax seller adding 'issalestaxreseller' in  $validfields
function fetch_user($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
        
        // valid user table fields
        $validfields = array('user_id', 'ipaddress', 'iprestrict', 'username', 'password', 'salt', 'secretquestion', 'secretanswer', 'email', 'first_name', 'last_name', 'address', 'address2', 'city', 'state', 'zip_code', 'phone', 'country', 'date_added', 'subcategories', 'status', 'serviceawards', 'productawards', 'servicesold', 'productsold', 'rating', 'score', 'bidstoday', 'bidsthismonth', 'auctiondelists', 'bidretracts', 'lastseen', 'warnings', 'warning_level', 'warning_bans', 'dob', 'rid', 'account_number', 'available_balance', 'total_balance', 'income_reported', 'income_spent', 'startpage', 'styleid', 'project_distance', 'currency_calculation', 'languageid', 'currencyid', 'timezoneid', 'timezone_dst', 'notifyservices', 'notifyproducts', 'notifyservicescats', 'notifyproductscats', 'displayprofile', 'emailnotify', 'displayfinancials', 'vatnumber', 'regnumber', 'dnbnumber', 'companyname', 'usecompanyname', 'rateperhour', 'profilevideourl', 'profileintro', 'autopayment', 'gender', 'issalestaxreseller');
        
        if (!empty($whereusername))
        {
                // fetching field based on username parameter
                $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "users
                                WHERE username = '" . $ilance->db->escape_string($whereusername) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return stripslashes($res[$field]);
                }
        }
        else if (!empty($whereemail))
        {
                // fetching field based on email address parameter
                $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "users
                                WHERE email = '" . $ilance->db->escape_string($whereemail) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return stripslashes($res[$field]);
                }
        }
        else if ($field == 'fullname')
        {
                // fetching field based only on the full name so we can concat the two fields
                $sql = $ilance->db->query("
                        SELECT first_name, last_name
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        return stripslashes($res['first_name']) . ' ' . stripslashes($res['last_name']);
                }       
        }
        else
        {
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
        }
        
        if ($showunknown)
        {
                return $phrase['_unknown'];
        }
        
        return '';
}
//kkk
function insertion_fee_transaction_new($cid = 0, $cattype = 'service', $amount = 0, $pid = 0, $userid = 0, $isbudgetrange = 0, $filtered_budgetid = 0, $isbulkupload = false)
{
                global $ilance, $ilpage, $phrase, $ilconfig;
                
                $ilance->accounting = construct_object('api.accounting');
                $ilance->subscription = construct_object('api.subscription');
                
                $fee = $fee2 = 0;
                $feetitle = '';
                
		// #### process single fee transaction #########################
                if ($isbulkupload == false)
                {
                        // #### PRODUCT INSERTION FEE ##################################
                        if ($cattype == 'product')
                        {
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "insertion_fees
                                        WHERE groupname = '" . $ilance->db->escape_string($cid) . "'
                                            AND state = '" . $ilance->db->escape_string($cattype) . "'
                                        ORDER BY sort ASC
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $found = 0;
                                        while ($rows = $ilance->db->fetch_array($sql))
                                        {
                                                if ($rows['insertion_to'] == '-1')
                                                {
                                                        if ($amount >= $rows['insertion_from'] AND $rows['insertion_to'] == '-1')
                                                        {
                                                                $found = 1;
                                                                $fee += $rows['amount'];
                                                        }
                                                }
                                                else
                                                {
                                                        if ($amount >= $rows['insertion_from'] AND $amount <= $rows['insertion_to'])
                                                        {
                                                                $found = 1;
                                                                $fee += $rows['amount'];
                                                        }
                                                }
                                        }
                                        if ($found == 0)
                                        {
                                                $fee = 0;
                                        }           
                                }
                                else
                                {
                                        $fee = 0;
                                }
                        }
                                                                  
                        // chop trailing ", " from the ending of the generated fee title
                        if (!empty($feetitle))
                        {
                                $feetitle = mb_substr($feetitle, 0, -2);
                        }
                        else if (empty($feetitle))
                        {
                                $feetitle = $phrase['_insertion_fee'];
                        }               
                        
                        // check if we're exempt from insertion fees
                        if (!empty($userid) AND $userid > 0 AND $ilance->subscription->check_access($userid, 'insexempt') == 'yes')
                        {
                                $fee = 0;
                        }
                        
                        // try to debit the account of this user
                        if ($fee > 0)
                        {
				// #### taxes on insertion fees ################
				$ilance->tax = construct_object('api.tax');
				$extrainvoicesql = '';
				if ($ilance->tax->is_taxable(intval($userid), 'insertionfee'))
				{
					// #### fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount(intval($userid), $fee, 'insertionfee', 0);
					
					// #### fetch total amount to hold within the "totalamount" field
					$totalamount = ($fee + $taxamount);
					
					// #### fetch tax bit to display when outputing tax infos
					$taxinfo = $ilance->tax->fetch_amount(intval($userid), $fee, 'insertionfee', 1);
					
					// #### extra bit to assign tax logic to the transaction 
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
				}				
				
                                // does owner have sufficient funds?
                                $sqlaccount = $ilance->db->query("
                                        SELECT available_balance, autopayment
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sqlaccount) > 0)
                                {
                                        $resaccount = $ilance->db->fetch_array($sqlaccount);
                                       
                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                        0,
                                                        intval($pid),
                                                        0,
                                                        intval($userid),
                                                        0,
                                                        0,
                                                        0,
                                                        $phrase['_insertion_fee'] . ' #' . intval($pid) . ' : ' . $feetitle,
                                                        sprintf("%01.2f", $fee),
                                                        sprintf("%01.2f", $fee),
                                                        'paid',
                                                        'debit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        DATETIME24H,
                                                        '',
                                                        0,
                                                        0,
                                                        1
                                                );
                                                
                                                // update invoice mark as insertion fee invoice type
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET $extrainvoicesql
														statement_date= '" . fetch_auction('date_end',$pid) . "',
														isif = '1'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // update auction with insertion fee
                                                // set insertion fee invoice flag as paid in full so this project doesn't show in the pending queue
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET insertionfee = '" . sprintf("%01.2f", $fee) . "',
                                                        isifpaid = '1',
                                                        ifinvoiceid = '" . intval($invoiceid) . "'
                                                        WHERE project_id = '" . intval($pid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // adjust account balance
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET available_balance = available_balance - $fee,
                                                        total_balance = total_balance - $fee
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // track spending habits
                                                insert_income_spent(intval($userid), sprintf("%01.2f", $fee), 'credit');
                                                
                                                // #### REFERRAL SYSTEM TRACKER ############################
                                                update_referral_action('ins', intval($userid));
                                        
                                }
                        }                        
                }
                
		// #### process bulk upload fee transaction ####################
		
        }
function fetch_proof($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
     
        // valid user table fields proof 	value
        $validfields = array('id', 'proof', 'coin_proof', 'value');
        
     
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "coin_proof
                                WHERE value = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return '';
        }
        
        return '';
}
function fetch_coin($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
     
        // valid user table fields
        $validfields = array('id', 'pcgs', 'Grading_Service', 'Grade', 'project_id', 'Buy_it_now', 'consignid');
        
     
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "coins
                                WHERE pcgs = '" . intval($userid) . "'
								and project_id = '" . intval($whereusername) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return '';
        }
        
        return '';
}
//new chnage
function fetch_coin_table($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
//coin_id, user_id, pcgs, Title, Description, Grading_Service, Grade, Quantity, Max_Quantity_Purchase, Certification_No, Condition_Attribute, Cac, Star, Plus, Coin_Series, Pedigee, Site_Id, Minimum_bid, Reserve_Price, Buy_it_now, End_Date, Alternate_inventory_No, Category, Other_information, consignid, coin_listed, in_notes, Service_Level, final_fee_percentage, final_fee_min, listing_fee, referal_id, notes, project_id, status, export, Sets, nocoin, pending, fvf_amount, fvf_id
        global $ilance, $phrase, $myapi;
     
        // valid user table fields
        $validfields = array('id', 'pcgs', 'Grading_Service', 'Grade', 'project_id', 'Buy_it_now', 'consignid', 'coin_id' ,'user_id', 'Title', 'Buy_it_now', 'Minimum_bid', 'Alternate_inventory_No', 'Description', 'Certification_No','End_Date');
        
     
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "coins
                                WHERE coin_id = '" . intval($userid) . "'
								
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return '';
        }
        
        return '';
}
function fetch_coin_consignid($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
     
        // valid user table fields
        $validfields = array('id', 'pcgs', 'Grading_Service', 'Grade', 'project_id', 'Buy_it_now', 'consignid');
        
     
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "coins
                                WHERE coin_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return '';
        }
        
        return '';
}
function fetch_cat($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
     
        // valid user table fields
       $validfields = array('id', 'PCGS', 'coin_detail_proof', 'coin_detail_suffix', 'Orderno','coin_series_denomination_no','coin_series_unique_no');
        
     
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "catalog_coin
                                WHERE PCGS = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return 'GC';
        }
        
        return '';
}
function fetch_user_aff_buyer($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
     
        // valid user table fields
        $validfields = array('id', 'coin_id', 'Site_Id', 'seller_id', 'buyer_id');
        
     
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "affiliate_buyer
                                WHERE coin_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return 'GC';
        }
        
        return '';
}
function fetch_user_siteid($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
        
        // valid user table fields
        $validfields = array('id', 'site_name');
        
     
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "affiliate_listing
                                WHERE id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return 'GC';
        }
        
        return '';
}
//june3
function fetch_buynow_list($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
        
        // valid user table fields
        $validfields = array('project_id', 'buyer_id', 'amount','invoiceid','fvfbuyer');
        
     
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "buynow_orders
                                WHERE project_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return 'GC';
        }
        
        return '';
}
function fetch_user_attach($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
        
        // valid user table fields
        $validfields = array('attachid','attachtype','user_id','portfolio_id','project_id','pmb_id','category_id','date','thumbnail_date','filename','filedata' ,'thumbnail_filedata','filetype','visible','counter','filesize','thumbnail_filesize','filehash','ipaddress','tblfolder_ref','exifdata','invoiceid','isexternal','coin_id');
        
     
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "attachment
                                WHERE coin_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return 'GC';
        }
        
        return '';
}
//new changes mar10
function fetch_date_time_coin($date_fet = '',$pcgs,$coin)
{
        global $ilance, $myapi, $ilconfig, $uncrypted, $show, $phrase;
        
        //start and end time in admin setting
		$start_time =  $ilconfig['projectstarttime'];
		$end_time =  $ilconfig['projectendtime'];
		
		$nextDay=$start_time>$end_time?1:0;
		 
		$dep=explode(':',$start_time);
		 
		$arr=explode(':',$end_time);
		 
		$diff=abs(mktime($dep[0],$dep[1],0,date('m'),date('d'),date('y')) - mktime($arr[0],$arr[1],0,date('m'),date('d')+$nextDay,date('y')));
		 
		$hours=floor($diff/(60*60));
		$mins=floor(($diff-($hours*60*60))/(60));
		$secs=floor(($diff-(($hours*60*60)+($mins*60))));
		if(strlen($hours)<2){$hours="0".$hours;}
		if(strlen($mins)<2){$mins="0".$mins;}
		if(strlen($secs)<2){$secs="0".$secs;}
		$hours.':'.$mins.':'.$secs;
		
		//total min from start and end time
		$totalhours = ($hours * 60) + $mins;
		$date_coin = $date_fet;
		//coin_id, user_id, pcgs, Title, Description, Grading_Service, Grade, Quantity, Max_Quantity_Purchase, Certification_No, Condition_Attribute, Cac, Star, Plus, Coin_Series, Pedigee, Site_Id, Minimum_bid, Reserve_Price, Buy_it_now, End_Date, Alternate_inventory_No, Category, Other_information, consignid, coin_listed, Service_Level, final_fee_percentage, final_fee_min, listing_fee, referal_id, project_id, status, export
		
		
		//now i remove c.Site_Id = '0' and for all site id
		$res20=$ilance->db->query("select c.coin_id,c.pcgs from 
		" . DB_PREFIX . "coins c, 
		" . DB_PREFIX . "catalog_coin cc, 
		" . DB_PREFIX . "catalog_second_level cs, 
		" . DB_PREFIX . "catalog_toplevel cd 
		where date(c.End_Date)='".$date_coin."' and
		c.coin_listed = 'c' and
		c.pcgs=cc.PCGS and
		cc.coin_series_unique_no=cs.coin_series_unique_no and
		cc.coin_series_denomination_no=cd.denomination_unique_no
		group by c.coin_id
		order by cd.denomination_sort,
		cs.coin_series_sort,
		cc.coin_detail_year
		");
		
		//now i remove c.Site_Id = '0' and for all site id
		$res60=$ilance->db->query("select c.coin_id,c.pcgs from 
		" . DB_PREFIX . "coins c, 
		" . DB_PREFIX . "catalog_coin cc, 
		" . DB_PREFIX . "catalog_second_level cs, 
		" . DB_PREFIX . "catalog_toplevel cd 
		where date(c.End_Date)='".$date_coin."' and
		c.coin_listed = 'c' and
		c.project_id = '0' and
		c.pcgs=cc.PCGS and
		cc.coin_series_unique_no=cs.coin_series_unique_no and
		cc.coin_series_denomination_no=cd.denomination_unique_no
		group by c.coin_id
		order by cd.denomination_sort,
		cs.coin_series_sort,
		cc.coin_detail_year
		");
		$count  = (int)$ilance->db->num_rows($res20);
		$count2 = (int)$ilance->db->num_rows($res60);
			
		//gap for total min and count 
		$gap = floor($totalhours/$count);
		$event_time = $start_time;
		//increment gap for min
		for($u=0;$u<$count;$u++)
		{
		   if($u == '0')
		   {
		   $event_length = '0';
		   }
		   else
		   {
		   $event_length = ($gap * $u);
		   }
	
		   $timestamp = strtotime("$event_time");
		   $etime = strtotime("+$event_length minutes", $timestamp);
		   $my_arr[] = $next_time = date('H:i:s', $etime);
		 }
		
		$r=0;
		if($count == $count2)
		{		
		
			//total update coin for without projectid
			while($row_td = $ilance->db->fetch_array($res20))
			{  
			
			
				$con_data = $ilance->db->query("
				UPDATE " . DB_PREFIX . "coins SET End_Date = CONCAT(DATE(End_Date),' ".$my_arr[$r]."') where pcgs='".$row_td['pcgs']."' and coin_id='".$row_td['coin_id']."'");
			
				$r++;
			}
		}
		else
		{ 
			
				while($row_td = $ilance->db->fetch_array($res20))
				{
				
				$text[] = $row_td['pcgs'];
				$coin_id_set[] = $row_td['coin_id'];
				
				}
				
				
				//array search for particular pcgs
				$key = array_search($pcgs, $text);
				
				if($key == '0')
				$kval = '0';
				else
				$kval = ($key - 1);
				
				//coin id for end time
				$coin_id_set=$coin_id_set[$kval];
				
				//print_r($coin_id_set);
				
				//print_r($text);
				
			
				
					if($key == '0')
					{
					$va = '0';
					}
					else
					{
					$va = '1';
					}					
				$k = 0;
				foreach($text as $my)
				{
					if($k == ($key - $va))
					{
					
					//now i remove c.Site_Id = '0' and for all site id
					
					//echo "select time(End_Date) as end from " . DB_PREFIX . "coins where pcgs = '".$my."' and coin_id='".$coin_id_set."' and coin_listed = 'c'";
					
					$con_50 = $ilance->db->query("select time(End_Date) as end,date(End_Date) as mydate from " . DB_PREFIX . "coins where pcgs = '".$my."' and coin_id='".$coin_id_set."' and coin_listed = 'c'");
					$row_50 = $ilance->db->fetch_array($con_50);
					
					if($key == '0')
					{
					$my_str = $start_time;
					}
					else
					{
					
					//start time check if greater than end time for coin
					$dep=explode(':',$start_time);
		 
		            $arr=explode(':',$row_50['end']);
		 
		            $timediff = mktime($dep[0],$dep[1],0,date('m'),date('d'),date('y'));
		            $enddiff = mktime($arr[0],$arr[1],0,date('m'),date('d'),date('y'));
		 
					   if($enddiff >= $timediff)
					   {
					   $my_str = $row_50['end'];
					   }
					   else
					   {					   
					   $my_str = $start_time;
					   }
					
					}
					
					$event_time_1 = $my_str;
					$timestamp_1 = strtotime("$event_time_1");
					
					//add 2min for before date 
					$etime_1 = strtotime("+2 minutes", $timestamp_1);
					$next_time_1 = date('H:i:s', $etime_1);
					
					//endtime returns
					$myinsert = fetch_datetime_recursion($next_time_1,$start_time,$row_50['mydate']);
					
					
					//update last coin
					$con_data = $ilance->db->query("
					UPDATE " . DB_PREFIX . "coins SET End_Date = CONCAT(DATE(End_Date),' ".$myinsert."') where pcgs='".$pcgs."' and coin_id='".$coin."'");
				   } 
				   $k++;	
				}
				
		  }
		
		
}
//new changes
function fetch_datetime_recursion($my,$sta,$mydat)
{
 global $ilance, $myapi, $ilconfig, $uncrypted, $show, $phrase;
 
                    //if time present for add +2min and change to add another +2min
					//now i remove c.Site_Id = '0' and for all site id
					//echo "select time(End_Date) as end from " . DB_PREFIX . "coins where time(End_Date) = '".$my."'  and coin_listed = 'c'";
                    $con_90 = $ilance->db->query("select time(End_Date) as end from " . DB_PREFIX . "coins where time(End_Date) = '".$my."' and date(End_Date)='".$mydat."' and coin_listed = 'c'");
					$row_90 = $ilance->db->fetch_array($con_90);
					
					if($ilance->db->num_rows($con_90) > '0')
					{	
					
					     $dep=explode(':',$sta);
		 
		                 $arr=explode(':',$row_90['end']);
		 
		                 $timediff = mktime($dep[0],$dep[1],0,date('m'),date('d'),date('y'));
						 
		                 $enddiff = mktime($arr[0],$arr[1],0,date('m'),date('d'),date('y'));
		 
					   if($enddiff >= $timediff)
					   {
					   $event_time_1 = $row_90['end'];
					   }
					   else
					   {					   
					   $event_time_1 = $sta;
					   }				
						//$event_time_1 = $row_90['end'];
						//$event_time_2 = $next_time_1;
						$timestamp_2 = strtotime("$event_time_1");
						
						//add 2min for before date 
						$etime_2 = strtotime("+2 minutes", $timestamp_2);
						$next_time_2 = date('H:i:s', $etime_2);
						$myinsert = $next_time_2;
						
						//recursion another endtime						
					    return fetch_datetime_recursion($myinsert);
					 
					}
					else
					{
					
					 //default value of endtime
					 $myinsert = $my;					 
					 return $myinsert;
					 
					}
}
function fetch_user_consign($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
        
        // valid user table fields
        $validfields = array('coin_id', 'consignid', 'Site_Id', 'project_id');
    
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "coins
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
        if ($showunknown)
        {
                return 'GC';
        }
        
        return '';
}
function fetch_cat_title($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
        
        // valid user table fields
        $validfields = array('cid', 'title_eng', 'Site_Id');
    
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "categories
                                WHERE cid = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }        
                }
       
        
       
        
        return '';
}
function fetch_user_shipment_new($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $myapi;
		
		
        
        // valid user table fields
        $validfields = array('project_id', 'user_id', 'ship_id');
     	
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
				            $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "projects
                                WHERE project_id  not in (SELECT item_id FROM " . DB_PREFIX . "shippnig_details WHERE cust_id = '" . intval($userid) . "')
								AND project_id  not in (SELECT project_id FROM " . DB_PREFIX . "cancel_sale WHERE seller_id = '" . intval($userid) . "')
								AND user_id = '" . intval($userid) . "'
								AND (haswinner = '1' OR hasbuynowwinner = '1') group by project_id
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
						        
                                while($res = $ilance->db->fetch_array($sql))
								{
								  $test[] = $res['project_id'];
								  $you = implode(',', $test);
								
								}
                               
							  return $you;
						
						}
                }
       
        
        if ($showunknown)
        {
                return 'GC';
        }
        
        return '';
}
function fetch_time($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
       
	    global $ilance, $phrase, $myapi;
		// valid user table fields
        $validfields = array('item_id', 'cust_id', 'ship_id');
     	
  //coin_id, user_id, pcgs, Title, Description, Grading_Service, Grade, Quantity, Max_Quantity_Purchase, Certification_No, Condition_Attribute, Cac, Star, Plus, Coin_Series, Pedigee, Site_Id, Minimum_bid, Reserve_Price, Buy_it_now, End_Date, Alternate_inventory_No, Category, Other_information, consignid, coin_listed, Service_Level, final_fee_percentage, final_fee_min, listing_fee, referal_id, project_id, status, export
  
  
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
				            $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "coins
                                WHERE  item_id = '" . intval($userid) . "'
								
								
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }  
                }
				
				
       
        
       
        return '';
}
function fetch_user_shipment_click($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
       
	    global $ilance, $phrase, $myapi;
		// valid user table fields
        $validfields = array('item_id', 'cust_id', 'ship_id');
     	
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
				            $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "shippnig_details
                                WHERE  item_id = '" . intval($userid) . "'
								
								
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }  
                }
				
				
       
        
       
        return '';
}
function fetch_user_shipment_click_aff($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
       
	    global $ilance, $phrase, $myapi;
		// valid user table fields
        $validfields = array('item_id', 'cust_id', 'ship_id', 'coin_id');
     	
                // fetching field based on a user id parameter
                // this will also check for valid db fields before we obtain anything relevent
                if (in_array($field, $validfields))
                {
				            $sql = $ilance->db->query("
                                SELECT $field
                                FROM " . DB_PREFIX . "shippnig_details
                                WHERE  coin_id = '" . intval($userid) . "'
								
								
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                return stripslashes($res[$field]);
                        }  
                }
				
				
       
        
       
        return '';
}
//end kkk
/**
* Function to fetch any field from the invoice table
*
* @param        string      field to fetch from the user table
* @param        integer     invoice id
*
* @return	string      Returns field value from the user table
*/
function fetch_invoice($field = '', $invoiceid = 0)
{
        global $ilance, $phrase, $myapi;
        
        // valid invoice table fields
        $validfields = array('invoiceid','parentid','currency_id','currency_rate','subscriptionid','projectid','buynowid','user_id','p2b_user_id','p2b_paymethod','p2b_markedaspaid','storeid','orderid','description','amount','paid','totalamount','istaxable','taxamount','taxinfo','status','invoicetype','paymethod','paymentgateway','ipaddress','referer','createdate','duedate','paiddate','custommessage','transactionid','archive','ispurchasorder','isdeposit','depositcreditamount','iswithdraw','withdrawinvoiceid','withdrawdebitamount','isfvf','isif','isportfoliofee','isenhancementfee','isescrowfee','iswithdrawfee','isp2bfee','isdonationfee','ischaritypaid','charityid','isregisterbonus','indispute');
        
        	// fetching field based on a user id parameter
	// this will also check for valid db fields before we obtain anything relevent
	if (in_array($field, $validfields))
	{
		$sql = $ilance->db->query("
			SELECT $field
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid = '" . intval($invoiceid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return stripslashes($res[$field]);
		}        
	}
        
        return '';
}
/**
* Function to fetch any field from the auction table based on the main auction listing number identifier
*
* @param        string      field to fetch from the auction table
* @param        integer     auction id
*
* @return	string      Returns field value from the auction table
*/
function fetch_auction($field = '', $auctionid = 0)
{
        global $ilance, $phrase, $myapi, $phrase;
        
        $sql = $ilance->db->query("
                SELECT $field
                FROM " . DB_PREFIX . "projects
                WHERE project_id = '" . intval($auctionid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                return stripslashes($res["$field"]);
        }
        
        return $phrase['_unknown'];
}
/**
* Function for fetching the total escrow fee based on a particular amount.
*
* @param       string         amount
*
* @return      string         escrow fee amount
*/
function fetch_provider_escrow_fee($amount = 0)
{
        global $ilance, $myapi, $ilconfig;
        
        $fee = 0;
        if ($ilconfig['escrowsystem_escrowcommissionfees'])
        {
                if ($ilconfig['escrowsystem_providerfixedprice'] > 0)
                {
                        $fee = $ilconfig['escrowsystem_providerfixedprice'];
                }
                else
                {
                        if ($ilconfig['escrowsystem_providerpercentrate'] > 0)
                        {
                                $fee = ($amount*$ilconfig['escrowsystem_providerpercentrate']/100);
                        }
                }       
        }
        return sprintf("%01.2f", $fee);
}
/**
* Function for fetching the total escrow fee (plus applicable taxes) based on a particular amount.
*
* @param       integer        user id
* @param       string         amount
*
* @return      string         escrow fee amount
*/
function fetch_provider_escrow_fee_plus_tax($userid = 0, $amount = 0)
{
        global $ilance, $myapi, $ilconfig;
        
        $fee = 0;
        if ($ilconfig['escrowsystem_escrowcommissionfees'])
        {
                $ilance->tax = construct_object('api.tax');
                if ($ilconfig['escrowsystem_providerfixedprice'] > 0)
                {
                        $fee = $ilconfig['escrowsystem_providerfixedprice'];
                }
                else
                {
                        if ($ilconfig['escrowsystem_providerpercentrate'] > 0)
                        {
                                $fee = ($amount * $ilconfig['escrowsystem_providerpercentrate'] / 100);
                        }
                }
                if ($fee > 0)
                {
                        $taxamount = 0;
                        if ($ilance->tax->is_taxable(intval($userid), 'commission'))
                        {
                                $taxamount = $ilance->tax->fetch_amount(intval($userid), $fee, 'commission', 0);
                        }
                        $fee = ($fee + $taxamount);
                }
        }
        
        return sprintf("%01.2f", $fee);
}
/**
* Function for fetching the escrow tax bit info within the buying and selling activity areas.
*
* @param       integer        user id
* @param       string         amount
* @param       integer        project id (to determine if escrow is enabled for auction)
* @param       boolean        show the phrase (default true)
*
* @return      string         tax information bit
*/
function fetch_escrow_taxinfo_bit($userid = 0, $amount = 0, $projectid = 0, $showphrase = true)
{
        global $ilance, $myapi, $ilconfig, $phrase;
        
        $taxinfo = $phrase['_none'];
        $filter_escrow = fetch_auction('filter_escrow', intval($projectid));
	
        if ($ilconfig['escrowsystem_enabled'] AND $filter_escrow == '1' AND $amount > 0)
        {
                $ilance->tax = construct_object('api.tax');
                if ($ilance->tax->is_taxable(intval($userid), 'commission'))
                {
                        // fetch tax amount to charge for this invoice type
                        $taxinfo = (($showphrase) ? $phrase['_taxes_added_to_the_escrow_fee'] . ' ' : '') . $ilance->tax->fetch_amount(intval($userid), $amount, 'commission', 1);
                }
        }
        
        return $taxinfo;
}
/**
* Function for fetching the total bid count today for a particular user id.
*
* @param       integer        user id
*
* @return      integer        total bid count today
*/
function fetch_bidcount_today($userid = 0)
{
        global $ilance, $myapi;
        $bids = fetch_user('bidstoday', intval($userid));
        return (int)$bids;
}
/**
* Function for fetching the winning user id based on a particular project id.
*
* @param       integer        project id
*
* @return      integer        winning user id
*/
function fetch_project_winnerid($projectid = 0)
{
        global $ilance, $myapi;
    
        $sql = $ilance->db->query("
                SELECT user_id
                FROM " . DB_PREFIX . "project_bids
                WHERE project_id = '".intval($projectid)."'
                        AND bidstatus = 'awarded'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                    $res = $ilance->db->fetch_array($sql);
                    return $res['user_id'];
        }
        
        return 0;
}
/**
* Function for printing only the <option> values for active credit cards on file for the user.
*
* @param       integer        user id
*
* @return      string         HTML representation of the options
*/
function print_active_creditcard_options($userid = 0)
{
        global $ilance, $ilconfig, $phrase;
	
        if ($userid > 0)
        {
                $html = '';
		
		if ($ilconfig['save_credit_cards'] AND $ilconfig['use_internal_gateway'] != 'none')
		{
			$sql = $ilance->db->query("
				SELECT cc_id, creditcard_number, creditcard_type
				FROM " . DB_PREFIX . "creditcards
				WHERE user_id = '" . intval($userid) . "'
					AND creditcard_status = 'active'
					AND authorized = 'yes'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$html .= '<optgroup label="' . $phrase['_active_credit_cards'] . '">';
				
				while ($res = $ilance->db->fetch_array($sql))
				{
					$html .= '<option value="' . $res['cc_id'] . '">';
					
					if ($res['creditcard_type'] == 'visa')
					{
						$html .= $phrase['_credit_card'] . ' (VISA # ';
					}
					else if ($res['creditcard_type'] == 'amex')
					{
						$html .= $phrase['_credit_card'] . ' (AMEX # ';
					}
					else if ($res['creditcard_type'] == 'mc')
					{
						$html .= $phrase['_credit_card'] . ' (MC # ';
					}
					else if ($res['creditcard_type'] == 'disc')
					{
						$html .= $phrase['_credit_card'] . ' (DISC # ';
					}
					
					$dec = $ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
					$dec = str_replace(' ', '', $dec);
					
					$html .= substr_replace($dec, 'XX XXXX XXXX ', 2 , (mb_strlen($dec) - 6)) . ')</option>';
				}
				
				$html .= '</optgroup>';
			}
			
			// option to show credit card form (if saving of cards to db is disabled)
			$html .= '<optgroup label="' . $phrase['_electronic_payment'] . '">';
			$html .= '<option value="ccform">' . $phrase['_pay_by_credit_card'] . '</option>';
			$html .= '</optgroup>';
		}
		else
		{
			if ($ilconfig['use_internal_gateway'] != 'none')
			{
				// option to show credit card form (if saving of cards to db is disabled)
				$html .= '<optgroup label="' . $phrase['_electronic_payment'] . '">';
				$html .= '<option value="ccform">' . $phrase['_pay_by_credit_card'] . '</option>';
				$html .= '</optgroup>';
			}
		}
                
                return $html;
        }
        
        return false;
}
/**
* Function for printing only the <option> values for active bank deposit accounts file for the user.
* Additionally, this function will present a withdrawal fee defined by the admin within the admincp.
* 
* @param       integer        user id
*
* @return      string         HTML representation of the options
*/
function print_active_bankaccount_options($userid = 0)
{
        global $ilance, $ilconfig, $phrase;
        
        if ($userid > 0 AND $ilconfig['enable_bank_deposit_support'])
        {
                $html = '';
                $sql = $ilance->db->query("
                        SELECT beneficiary_account_number, beneficiary_bank_name, bank_account_type
                        FROM " . DB_PREFIX . "bankaccounts
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $feebit = '';
                        if ($ilconfig['bank_withdraw_fee_active'] AND $ilconfig['bank_withdraw_fee'] > 0)
                        {
                                $feebit = ' (+ ' . $ilance->currency->format($ilconfig['bank_withdraw_fee']) . ')';
                        }
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {                
                                //$html .= '<option value="' . $res['beneficiary_account_number'] . '">' . ucwords(mb_strtolower($res['beneficiary_bank_name'])) . ' - ' . ucfirst($res['bank_account_type']) . ' #' . str_repeat('X', (mb_strlen($res['beneficiary_account_number']) - 4)) . mb_substr($res['beneficiary_account_number'], -4, 4) . $feebit . '</option>';
                                $type = $res['bank_account_type'];
                                $bankaccounttype = $phrase["_$type"];
                                $html .= '<option value="' . $res['beneficiary_account_number'] . '">' . ucwords(mb_strtolower($res['beneficiary_bank_name'])) . ' - ' . $bankaccounttype . ' #' . str_repeat('X', (mb_strlen($res['beneficiary_account_number']) - 4)) . mb_substr($res['beneficiary_account_number'], -4, 4) . $feebit . '</option>';
                        }
                        unset($type);
                }
                
                return $html;
        }
        
        return false;
}
/**
* Function for printing only the <option> values for enabled ipn gateway processor methods
*
* @param       string         the custom location we are parsing this function from
*
* @return      string         HTML representation of the options
*/
function print_active_ipn_options($area = '', $role='')
{
        global $ilance, $ilconfig, $phrase;
        
        //EDITED BY TAMIL FOR BUG 1934 ON 11/12/12
		
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] >0 AND $role != 'staff')
		{
			$html = '';
        
			($apihook = $ilance->api('print_active_ipn_options_start')) ? eval($apihook) : false;
			
			if ($ilconfig['paypal_active'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				$html .= '<option value="paypal">'.$phrase['_paypal'].'</option>';
			}
			if ($ilconfig['cc_candeposit'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				$html .= '<option value="paypal card">Credit Card Online (For accounting, its PayPal)</option>';
			}
			if (!empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				$html .= '<option value="Credit Card BofA">Credit Card BofA</option>';
			}			
			
			if ($ilconfig['checkpayout_support'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				$html .= '<option value="check">'.$phrase['_check_money_order'].'</option>';
			}
			if ($ilconfig['enable_bank_deposit_support'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				$html .= '<option value="wire">'.$phrase['_wire'].'</option>';
			}
			if (!empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				$html .= '<option value="cash">Cash</option>';
			}			
			if (!empty($area) AND ($area == 'other' OR $area == 'invoicepayment'))
			{
				$html .= '<option value="others">Others</option>';
			}	
		   
			
			($apihook = $ilance->api('print_active_ipn_options_option')) ? eval($apihook) : false;
			
			if (!empty($html))
			{
					$html = '<optgroup label="' . $phrase['_online_payment'] . '">' . $html . '</optgroup>';
			}
			
			($apihook = $ilance->api('print_active_ipn_options_end')) ? eval($apihook) : false;
			
			return $html;
		}
		else
		{
			$html = '';
        
			($apihook = $ilance->api('print_active_ipn_options_start')) ? eval($apihook) : false;
			
			if ($ilconfig['paypal_active'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				   $html .= '<option value="paypal">'.$phrase['_paypal'].'</option>';
			}
			// murugan changes on feb 15 for payment option changes below authnet - > paypal
			//sekar on oct 12 paymethod
			if ($ilconfig['cc_candeposit'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				   $html .= '<option value="card">'.$phrase['_credit_card_visa_mastercard_discover_american_express'].'</option>';
			}
			if ($ilconfig['checkpayout_support'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				   $html .= '<option value="check">'.$phrase['_check_money_order'].'</option>';
			}
			if ($ilconfig['enable_bank_deposit_support'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				  $html .= '<option value="bank">'.$phrase['_wire'].'</option>';
			}
		   if ($ilconfig['enable_bank_deposit_support'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				 $html .= '<option value="trade">'.$phrase['_trade_against_consignor_proceeds'].'</option>';
			}
			/*if ($ilconfig['stormpay_active'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				  echo '3'.  $html .= '<option value="stormpay">Stormpay</option>';
			}
			if ($ilconfig['cashu_active'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				  echo '4'.  $html .= '<option value="cashu">CashU</option>';
			}
			if ($ilconfig['moneybookers_active'] AND !empty($area) AND ($area == 'deposit' OR $area == 'invoicepayment'))
			{
				   echo '5'. $html .= '<option value="moneybookers">MoneyBookers</option>';
			}*/
			
			($apihook = $ilance->api('print_active_ipn_options_option')) ? eval($apihook) : false;
			
			if (!empty($html))
			{
					$html = '<optgroup label="' . $phrase['_online_payment'] . '">' . $html . '</optgroup>';
			}
			
			($apihook = $ilance->api('print_active_ipn_options_end')) ? eval($apihook) : false;
			
			return $html;
		}
		
       //EDITED BY TAMIL FOR BUG 1934 ON 11/12/12 
        
}
/**
* Function for printing the appropriate payment processor options within the payment menu pulldown's
*
* @param       string         the custom location we are parsing this function from
* @param       string         fieldname of the pulldown selection menu (paymethod is default)
* @param       integer        user id
* @param       string         javascript string to include in the onchange event
*
* @return      string         HTML representation of the pulldown menu
*/
function print_paymethod_pulldown($location = '', $fieldname = 'paymethod', $userid = 0, $javascript = '', $page='')
{
        global $ilance, $myapi, $phrase, $ilconfig;
        
        $html = '';
        
        if (isset($location))
        {
                switch ($location)
                {
                        // #### SUBSCRIPTION MENU ##############################
                        case 'subscription':
                        {
                                break;
                        }                    
                        // #### DEPOSIT MENU ###################################
                        case 'deposit':
                        {
                                $html .= '<select name="' . $fieldname . '" style="font-family: verdana" ' . $javascript . '>';
                                $html .= '<option value="">' . $phrase['_please_select'] . '</option>';
                                
                                // #### ACTIVE CREDIT CARDS ON FILE ############
                                $html .= print_active_creditcard_options($userid);
                                
                                // #### IPN GATEWAYS ###########################
                                $html .= print_active_ipn_options('deposit');
                                $html .= '</select>';
                                break;
                        }                    
                        // #### WITHDRAWAL MENU ################################
                        case 'withdraw':
                        {
                                $html .= '<select name="' . $fieldname . '" style="font-family: verdana" ' . $javascript . '>';
                                if ($ilconfig['checkpayout_support'])
                                {
                                        // any withdraw fees active?
                                        $feebit = '';
                                        if ($ilconfig['check_withdraw_fee_active'] AND $ilconfig['check_withdraw_fee'] > 0)
                                        {
                                                $feebit =  ' (+ ' . $ilance->currency->format($ilconfig['check_withdraw_fee']) . ')';
                                        }
                                        $html .= '<optgroup label="'.$phrase['_postal_mail'].'">';
                                        $html .= '<option value="check">'.$phrase['_check_money_order'] . $feebit . '</option>';
                                        $html .= '</optgroup>';
                                }
                                $html .= '<optgroup label="'.$phrase['_electronic_transfer'].'">';
                                if ($ilconfig['paypal_withdraw_active'])
                                {
                                        // any withdraw fees active?
                                        $feebit = '';
                                        if ($ilconfig['paypal_withdraw_fee_active'] AND $ilconfig['paypal_withdraw_fee'] > 0)
                                        {
                                                $feebit =  ' (+ ' . $ilance->currency->format($ilconfig['paypal_withdraw_fee']) . ')';
                                        }
                                        $html .= '<option value="paypal">'.$phrase['_paypal_money_request'] . $feebit . '</option>';
                                }
                                
                                // #### ACTIVE BANK DEPOSIT ACCOUNTS ON FILE ###################
                                $html .= print_active_bankaccount_options($userid);
                                $html .= '</optgroup>';
                                $html .= '</select>';
                                break;
                        }                    
                        // #### DIRECT PAYMENT MENU ############################
                        case 'invoicepayment':
                        {
                              $html .= '<select name="' . $fieldname . '" style="font-family: verdana" ' . $javascript . '>';
                                
                                // #### ONLINE ACCOUNT BALANCE #################
								// murugan changes on Feb 15 for payment Change
                             /*   $html .= '<optgroup label="' . $phrase['_available_balance'] . '">';
                                $html .= '<option value="account">' . SITE_NAME . ' ' . $phrase['_online_account_instant_payment'] . ' (' . $phrase['_available_balance'] . ': ' . $ilance->currency->format(fetch_user('available_balance', intval($userid))) . ')</option>';
                                $html .= '</optgroup>';*/
                                
                                // #### IPN GATEWAYS ###########################
                                if($page == 'staff')
									$html .= print_active_ipn_options('invoicepayment',$page);
								else
									$html .= print_active_ipn_options('invoicepayment');
                                $html .= '</select>';
                                break;
                        }                    
                        // #### ONLINE ACCOUNT BALANCE ONLY ####################
                        case 'portfolio':
                        case 'account':
                        {
                                $html .= '<select name="' . $fieldname . '" style="font-family: Verdana" ' . $javascript . '>';
                                $html .= '<optgroup label="' . $phrase['_available_balance'] . '">';
                                $html .= '<option value="account">' . SITE_NAME . ' ' . $phrase['_online_account_instant_payment'] . ' (' . $phrase['_available_balance'] . ': ' . $ilance->currency->format(fetch_user('available_balance', intval($userid))) . ')</option>';
                                $html .= '</optgroup>';
                                $html .= '</select>';
                                break;
                        }
                        case 'paidmethod':
                        {
                        	$html = '';
							if ($fieldname == 'paypal')
							{
								$html = $phrase['_paypal'];
							}
							if ($fieldname == 'paypal card')
							{
								$html = 'Credit Card Online (For accounting, its PayPal)';
							}
							if ($fieldname == 'Credit Card BofA')
							{
								$html = 'Credit Card BofA';
							}	
							if ($fieldname == 'card')
							{
								   $html = $phrase['_credit_card_visa_mastercard_discover_american_express'];
							}		
							if ($fieldname == 'trade')
							{
								 $html = $phrase['_trade_against_consignor_proceeds'];
							}
							if ($fieldname == 'check')
							{
								$html = $phrase['_check_money_order'];
							}
							if ($fieldname == 'wire')
							{
								$html = $phrase['_wire'];
							}
							if ($fieldname == 'cash')
							{
								$html = 'Cash';
							}			
							if ($fieldname == 'others')
							{
								$html = 'Others';
							}	
                            break;
                        }

                }
        }
        
        return $html;
}
/**
* Function for fetching data from a url based on the curl library extention in php
*
* @param       string         url
*
* @return      string         HTML representation of the data requested
*/
function fetch_curl_string($url)
{
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        ob_start();
        curl_exec($ch);
        curl_close($ch);
        $string = ob_get_contents();
        ob_end_clean();
        
        return $string;    
}
/**
* Function for determining the entire size used within the database for ILance operations.
*
* @return      string         size in bytes
*/
function fetch_database_size()
{
        global $ilance, $myapi;
        $total = 0;
        $result = $ilance->db->query("SHOW TABLE STATUS", 0, null, __FILE__, __LINE__);
        while ($row = $ilance->db->fetch_array($result))
        { 
            $total += ($row['Data_length']+$row['Index_length']); 
        } 
        return $total;
}
/**
* Function to encrypt a url
*
* @param       array          url array
* 
* @return      string         encoded url
*/
function encrypt_url($array = array())
{
        $encoded = serialize($array);
        $encoded = base64_encode($encoded);
        $encoded = urlencode($encoded);
        
        return $encoded;
}
/**
* Function to decrypt a url
*
* @param       string         encoded url
* 
* @return      array          decoded url array
*/
function decrypt_url($encrypted = '')
{
        if (empty($encrypted))
        {
                $uncrypted = array();
                if ($_GET)
                {
                        foreach ($_GET as $key => $value)
                        {
                                $uncrypted["$key"] = $value;
                        }
                }
                else if ($_POST)
                {
                        foreach ($_POST as $key => $value)
                        {
                                $uncrypted["$key"] = $value;
                        }
                }
                return $uncrypted;
        }
        else
        {
                $uncrypted = base64_decode($encrypted);
                $uncrypted = unserialize($uncrypted);
                return $uncrypted;
        }
}
/**
* Function to fetch the business numbers for a user (VAT or Business Reg #)
*
* @param       integer        user id
* @param       bool           force no formatting
* 
* @return      string         Returns business number(s) for display
*/
function fetch_business_numbers($userid = 0, $noformatting = '')
{
        global $ilance, $myapi, $phrase;
        $sql = $ilance->db->query("
                SELECT regnumber, vatnumber, companyname, dnbnumber
                FROM " . DB_PREFIX . "users
                WHERE user_id = '".intval($userid)."'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                $html = '';
                if (!empty($res['companyname']))
                {
                        $html .= '<div>'.$phrase['_company_name'].': <strong>' . stripslashes($res['companyname']) . '</strong></div>';
                }
                if (!empty($res['regnumber']))
                {
                        $html .= '<div>'.$phrase['_company_registration_number'].': <strong>'.$res['regnumber'].'</strong></div>';
                }
                if (!empty($res['vatnumber']))
                {
                        $html .= '<div>'.$phrase['_vat_registration_number'].': <strong>'.$res['vatnumber'].'</strong></div>';
                }
                if (empty($html))
                {
                        $html .= '<div>'.$phrase['_no_company_registration_numbers_submitted_to_marketplace'].'</div>';
                }
        }
        else
        {
                $html = '--';
        }
        return $html;
}
/**
* Function to create a unique transaction id used within the billing and payment system
*
* @return      string         Returns unique transaction id
*/
function construct_transaction_id()
{
        global $ilance, $myapi, $ilconfig;
        if ($ilconfig['invoicesystem_transactionidlength'] > 0)
        {
                $tid = '';
                for ($i = 1; $i <= $ilconfig['invoicesystem_transactionidlength']; $i++)
                {
                        mt_srand((double)microtime() * 1000000);
                        $num = mt_rand(1, 36);
                        $tid .= $ilance->common->construct_random_value($num);
                }
        }
        return $tid;
}
/**
* Function to print the left currency symbol
*
* @return      string         Returns left currency symbol (US$, $, etc)
*/
function print_left_currency_symbol()
{
        global $ilance, $ilconfig;
        
        return $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'];
}
/**
* Function to print the currency conversion based on a supplied currency id
*
* @param       integer        viewing user's currency id
* @param       integer        dollar amount to process
* @param       integer        listing currency id
*
* @return      string         Returns the formatted amount based on a particular currency id
*/
function print_currency_conversion($currencyid = 0, $amount = 0, $currencyid_item = 0)
{
        global $ilance, $ilconfig, $ilpage;
        
        $html = '';
	
        // #### default currency exchange rate for viewing user ################
	$default_rate = ($currencyid_item == 0)
		? $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['rate']
		: $ilance->currency->currencies[$currencyid_item]['rate'];
		
        // #### convert amount into something php can work with ################
        $amount = $ilance->currency->string_to_number($amount);
	
	// #### viewing user's currency rate ###################################
        $customer_rate = $default_rate;
        if ($currencyid > 0)
        {
                $customer_rate = $ilance->currency->currencies[$currencyid]['rate'];
        }
        
        $price_conversion_rate = ($amount * $customer_rate / $default_rate);
        $price_conversion_rate = sprintf("%01.2f", $price_conversion_rate);
	
	$convert_currencyid = ($currencyid == 0)
		? $ilconfig['globalserverlocale_defaultcurrency']
		: $currencyid;
		
	$convert2_currencyid = ($currencyid_item == 0)
		? $ilconfig['globalserverlocale_defaultcurrency']
		: $currencyid_item;
	
	$converted1 = $ilance->currency->format($price_conversion_rate, $convert_currencyid);
	$converted2 = $ilance->currency->format($amount, $convert2_currencyid);
	
        //echo "$currencyid, $amount, $currencyid_item<br />";
	//echo "$default_rate, $amount, $customer_rate<br />";
	//echo "$converted1, $amount, $converted2<br />";
	
        // if default site currency is same as users show 1 instance only of the conversion
	$html = ($default_rate == $customer_rate OR $amount <= 0)
		? $converted1
		: $converted2 . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['accounting'] . '?cmd=currency-converter&amp;subcmd=process&amp;amount=' . $amount . '&amp;transfer_from=' . $currencyid_item . '&amp;transfer_to=' . $currencyid . '&amp;returnurl=' . urlencode(PAGEURL) . '">' . $converted1 . '</a></span>)</span>';
        
        return $html;
}
/**
* Function to track income reported for a particular user for a certain amount based on a certain action.
*
* @param       integer        user id
* @param       integer        amount to process
* @param       string         action to perform (credit or debit)
*
* @return      nothing
*/
function insert_income_reported($userid = 0, $amount = 0, $action = '')
{
        global $ilance, $myapi;
        
        $amount = sprintf("%01.2f", $amount);
        if ($action == 'credit')
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET income_reported = income_reported + $amount
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
        }
        else if ($action == 'debit')
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET income_reported = income_reported - $amount
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
        }
}
/**
* Function to track income spent for a particular user for a certain amount based on a certain action.
*
* @param       integer        user id
* @param       integer        amount to process
* @param       string         action to perform (credit or debit)
*
* @return      nothing
*/
function insert_income_spent($userid = 0, $amount = 0, $action = '')
{
        global $ilance, $myapi;
        $amount = sprintf("%01.2f", $amount);
        if ($action == 'credit')
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET income_spent = income_spent + $amount
                        WHERE user_id = '".intval($userid)."'
                ", 0, null, __FILE__, __LINE__);
        }
        if ($action == 'debit')
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET income_spent = income_spent - $amount
                        WHERE user_id = '".intval($userid)."'
                ", 0, null, __FILE__, __LINE__);
        }
}
/**
* Function to fetch the total amount of verified credentials for a particular user within a specific category
*
* @param       integer        user id
* @param       integer        category id
*
* @return      integer        Returns integer amount of verified credentials
*/
function fetch_verified_credentials($userid = 0, $cid = 0)
{
        global $ilance, $myapi, $phrase, $ilpage, $ilconfig;
        
        $extracid = '';
        if (isset($cid) AND $cid > 0)
        {
                $extracid = '&amp;cid=' . $cid;	
        }
        
        $html = '-';
        
        $sql = $ilance->db->query("
                SELECT COUNT(isverified) AS verified
                FROM " . DB_PREFIX . "profile_questions q
                LEFT JOIN " . DB_PREFIX . "profile_answers a ON a.questionid = q.questionid
                WHERE a.user_id = '" . intval($userid) . "'
                        AND a.isverified = '1'
                        AND a.invoiceid > 0
                        AND a.verifyexpiry > '" . DATETIME24H . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                if ($res['verified'] == 0)
                {
                        $html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" border="0" alt="" id="' . intval($userid) . '_unverified" />';
                }
                else if ($res['verified'] == 1)
                {
                        $html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" border="0" alt="" id="' . intval($userid) . '_verified" />';
                }
                else if ($res['verified'] > 1)
                {
                        //$html = print_username(intval($userid), 'custom', $bold = 0, '&amp;feedback=2' . $extracid . '#categories', '?feedback=2' . $extracid . '#categories', $res['verified'] . ' ' . $phrase['_verified']);
                        $html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon_stack.gif" border="0" alt="" id="' . intval($userid) . '_verified_stack" />';
                }
        }
        
        return $html;
}
/**
* Function to determine if we can display the financials for a particular user (if they allow it from their profile menu)
*
* @param       integer        user id
*
* @return      bool           Returns true or false
*/
function can_display_financials($userid = 0)
{
        global $ilance, $myapi;
        $sql = $ilance->db->query("
                SELECT displayfinancials
                FROM " . DB_PREFIX . "users
                WHERE user_id = '".intval($userid)."'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                if ($res['displayfinancials'] > 0)
                {
                        return 1;
                }
        }
        return 0;
}
/**
* Function to print an item photo via <img src> for a particular auction id
*
* @param       string         url where the photo should link to
* @param       string         mode (thumb, full, checkup)
* @param       integer        auction id
* @param       integer        border width
* @param       string         border color (default #ffffff)
*
* @return      bool           Returns HTML representation of the item photo via <img src> tag
*/
function print_item_photo($url = 'javascript:void(0)', $mode = '', $projectid = 0, $borderwidth = '0', $bordercolor = '#ffffff')
{
        global $ilance, $myapi, $ilconfig, $ilpage, $phrase;
        
        $html = '';
        
        // query database for product image for preview
        $ufile = $ilance->db->query("
                SELECT attachid, filename, filehash, attachtype
                FROM " . DB_PREFIX . "attachment
                WHERE project_id = '" . intval($projectid) . "'
                        AND (attachtype = 'itemphoto' OR attachtype = 'slideshow')
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($ufile) > 0)
        {
                $pictures = 1;
                while ($rfile = $ilance->db->fetch_array($ufile))
                {
                        if ($rfile['attachtype'] == 'itemphoto')
                        {
                                switch ($mode)
                                {
                                        case 'thumb':
                                        {
                                                $html = '<a href="' . $url . '"><img src="' .HTTPS_SERVER. 'image/72/96/' . $rfile['filename'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>';
						
                                                ($apihook = $ilance->api('foto_thumb')) ? eval($apihook) : false;
						
                                                break;
                                        }
										case 'results_zoom':
										{
											
												 $html = '<a href="' . $url . '"><img src="' .HTTPS_SERVER. 'image/115/82/' . $rfile['filename'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>';
						
												($apihook = $ilance->api('foto_thumb')) ? eval($apihook) : false;

												break;
										}
										case 'thumbgallery':
                                        {
                                                $html = '<a href="' . $url . '"><img src="' .HTTPS_SERVER. 'image/150/150/' . $rfile['filename'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>';
                                                break;
                                        }
										case 'thumbsnapshot':
                                        {
                                                $html = '<a href="' . $url . '"><img src="' .HTTPS_SERVER . 'image/72/96/' . $rfile['filename'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>';
                                                break;
                                        }
                                        case 'full':
                                        {
                                                $html = '<a href="' . $url . '"><img src="' .HTTPS_SERVER.  'image.php?id=' . $rfile['filename'] . '" alt="" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" /></a>';
                                                break;
                                        }
                                        case 'checkup':
                                        {
                                                return '1';
                                                break;
                                        }
                                }
                        }
                        else if ($rfile['attachtype'] == 'slideshow')
                        {
                                $pictures++;
                        }
                }
                
                if ($mode == 'thumb' AND $pictures > 0)
                {
                        $html1 = '
                        <div class="gallery-thumbs-cell">			
                        <div class="gallery-thumbs-entry">
                                <div class="gallery-thumbs-main-entry">
                                        <div class="gallery-thumbs-wide-wrapper">
                                                <div class="gallery-thumbs-wide-inner-wrapper">';
                                                $html1 .= $html;
                                                $html1 .= '<div class="gallery-thumbs-corner-text"><span>' . ($pictures + 1) . ' photos</span></div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                        </div>
                        ';
                        
                        $html = $html1;
                }
				else if ($mode == 'results_zoom' AND $pictures > 0)
                {
                        $html1 = '
                        <div class="gallery-thumbs-cell">			
                        <div class="gallery-thumbs-entry">
                                <div class="gallery-thumbs-main-entry">
                                        <div class="gallery-thumbs-wide-wrapper_search">
                                                <div class="gallery-thumbs-wide-inner-wrapper">';
                                                $html1 .= $html;
                                                $html1 .= '<div class="gallery-thumbs-corner-text"><span>' . ($pictures + 1) . ' photos</span></div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                        </div>
                        ';
                        
                        $html = $html1;
                }
        }
        else 
        {
                $html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
        }
        
        
        return $html;
}
//arsath added for the search page on 05-oct-2010
function print_item_photo_new($url = 'javascript:void(0)', $mode = '', $projectid = 0, $borderwidth = '0', $bordercolor = '#ffffff')
{
        global $ilance, $myapi, $ilconfig, $ilpage, $phrase;
        
        $html = '';
        
        // query database for product image for preview
        $ufile = $ilance->db->query("
                SELECT attachid, filename, filehash, attachtype
                FROM " . DB_PREFIX . "attachment
                WHERE project_id = '" . intval($projectid) . "'
                        AND (attachtype = 'itemphoto' OR attachtype = 'slideshow')
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($ufile) > 0)
        {
                $pictures = 0;
                while ($rfile = $ilance->db->fetch_array($ufile))
                {
                        if ($rfile['attachtype'] == 'itemphoto')
                        {
                                switch ($mode)
                                {
                                        case 'thumb':
                                        {
                                                $html = '<a href="' . $url . '"><img src="../' . $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=results&amp;id=' . $rfile['filehash'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>';
						
                                                ($apihook = $ilance->api('foto_thumb')) ? eval($apihook) : false;
						
                                                break;
                                        }
										case 'thumbgallery':
                                        {
                                                $html = '<a href="' . $url . '"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=resultsgallery&amp;id=' . $rfile['filehash'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>';
                                                break;
                                        }
										case 'thumbsnapshot':
                                        {
                                                $html = '<a href="' . $url . '"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=resultssnapshot&amp;id=' . $rfile['filehash'] . '" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" class="gallery-thumbs-image-cluster" /></a>';
                                                break;
                                        }
                                        case 'full':
                                        {
                                                $html = '<a href="' . $url . '"><img src="' . $ilpage['attachment'] . '?id=' . $rfile['filehash'] . '" alt="" border="' . $borderwidth . '" alt="" style="border-color:' . $bordercolor . '" /></a>';
                                                break;
                                        }
                                        case 'checkup':
                                        {
                                                return '1';
                                                break;
                                        }
                                }
                        }
                        else if ($rfile['attachtype'] == 'slideshow')
                        {
                                $pictures++;
                        }
                }
                
                if ($mode == 'thumb' AND $pictures > 0)
                {
                        $html1 = '
                        <div class="gallery-thumbs-cell">			
                        <div class="gallery-thumbs-entry">
                                <div class="gallery-thumbs-main-entry">
                                        <div class="gallery-thumbs-wide-wrapper">
                                                <div class="gallery-thumbs-wide-inner-wrapper">';
                                                $html1 .= $html;
                                                $html1 .= '<div class="gallery-thumbs-corner-text"><span>' . ($pictures + 1) . ' photos</span></div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                        </div>
                        ';
                        
                        $html = $html1;
                }
        }
        else 
        {
                $html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
        }
        
        
        return $html;
}
/**
* Function to fetch a particular role id for a user
*
* @param       integer        user id
*
* @return      bool           Returns integer role id value
*/
function fetch_user_roleid($userid = 0)
{
        global $ilance, $myapi;
        $sqlroles = $ilance->db->query("
                SELECT roleid
                FROM " . DB_PREFIX . "subscription_user
                WHERE user_id = '".intval($userid)."'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sqlroles) > 0)
        {
                $roles = $ilance->db->fetch_array($sqlroles);
                return $roles['roleid'];
        }
        return 0;
}
/**
* Function to print a particular role title
*
* @param       integer        role id
*
* @return      string         Returns the role title
*/
function print_role($roleid = 0)
{
        global $ilance, $myapi, $phrase;
        $sqlroles = $ilance->db->query("
                SELECT title
                FROM " . DB_PREFIX . "subscription_roles
                WHERE roleid = '".intval($roleid)."'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sqlroles) > 0)
        {
                $roles = $ilance->db->fetch_array($sqlroles);
                return stripslashes($roles['title']);
        }
        return $phrase['_no_role'];	
}
/**
* Function to fetch a particular role count within the subscription table
*
* @param       integer        role id
*
* @return      string         Returns the role count
*/
function fetch_subscription_role_count($roleid = 0)
{
        global $ilance, $myapi, $phrase;
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS total
                FROM " . DB_PREFIX . "subscription
                WHERE roleid = '".intval($roleid)."'
                        AND active = 'yes'
                        AND visible = '1'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                return (int)$res['total'];
        }
        return 0;
}
/**
* Function to print the role pulldown menu with selected options as the roles
*
* @param       string         selected role option
* @param       bool           show "none selected" option
* @param       bool           show role plan count beside role name
* @param       bool           are we generating the pulldown via admincp?
*
* @return      string         Returns HTML representation of the role pulldown menu
*/
function print_role_pulldown($selected = '', $shownoneselected = 0, $showplancount = 0, $adminmode = 0)
{
        global $ilance, $myapi, $phrase;
        $sqlroles = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "subscription_roles
                WHERE active = '1'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sqlroles) > 0)
        {
                $html = '<select name="roleid" style="font-family: verdana">';
                if (isset($shownoneselected) AND $shownoneselected)
                {
                        $html .= '<option value="-1" selected="selected">'.$phrase['_tie_this_subscription_plan_to_a_role'].':</option>';
                }
                $roleattach = '';
                while ($roles = $ilance->db->fetch_array($sqlroles))
                {
                        if (isset($adminmode) AND $adminmode)
                        {
                                if (isset($showplancount) AND $showplancount)
                                {
                                        // fetch total number of subscription plans tied to this role
                                        $roleattach = fetch_subscription_role_count($roles['roleid']);
                                        if (isset($selected) AND $selected == $roles['roleid'])
                                        {
                                                $html .= '<option value="'.$roles['roleid'].'" selected="selected">'.stripslashes($roles['title']).' - '.stripslashes($roles['purpose']).' - ' . $roleattach.' '.$phrase['_subscription_plans'].'</option>';
                                        }
                                        else 
                                        {
                                                $html .= '<option value="'.$roles['roleid'].'">'.stripslashes($roles['title']).' - '.stripslashes($roles['purpose']).' - ' . $roleattach.' '.$phrase['_subscription_plans'].'</option>';
                                        }
                                }
                                else
                                {
                                        // fetch total number of subscription plans tied to this role
                                        $roleattach = fetch_subscription_role_count($roles['roleid']);
                                        if (isset($selected) AND $selected == $roles['roleid'])
                                        {
                                                $html .= '<option value="'.$roles['roleid'].'" selected="selected">'.stripslashes($roles['title']).' - '.stripslashes($roles['purpose']).'</option>';
                                        }
                                        else 
                                        {
                                                $html .= '<option value="'.$roles['roleid'].'">'.stripslashes($roles['title']).' - '.stripslashes($roles['purpose']).'</option>';
                                        }    
                                }        
                        }
                        else
                        {
                                if (isset($showplancount) AND $showplancount)
                                {
                                        // fetch total number of subscription plans tied to this role
                                        $roleattach = fetch_subscription_role_count($roles['roleid']);
                                        if (isset($selected) AND $selected == $roles['roleid'])
                                        {
                                                if ($roleattach > 0)
                                                {
                                                        $html .= '<option value="'.$roles['roleid'].'" selected="selected">'.stripslashes($roles['title']).' - '.stripslashes($roles['purpose']).' - ' . $roleattach.' '.$phrase['_subscription_plans'].'</option>';
                                                }
                                        }
                                        else 
                                        {
                                                if ($roleattach > 0)
                                                {
                                                        $html .= '<option value="'.$roles['roleid'].'">'.stripslashes($roles['title']).' - '.stripslashes($roles['purpose']).' - ' . $roleattach.' '.$phrase['_subscription_plans'].'</option>';
                                                }
                                        }
                                }
                                else
                                {
                                        // fetch total number of subscription plans tied to this role
                                        $roleattach = fetch_subscription_role_count($roles['roleid']);
                                        if (isset($selected) AND $selected == $roles['roleid'])
                                        {
                                                if ($roleattach > 0)
                                                {
                                                        $html .= '<option value="'.$roles['roleid'].'" selected="selected">'.stripslashes($roles['title']).' - '.stripslashes($roles['purpose']).'</option>';
                                                }
                                        }
                                        else 
                                        {
                                                if ($roleattach > 0)
                                                {
                                                        $html .= '<option value="'.$roles['roleid'].'">'.stripslashes($roles['title']).' - '.stripslashes($roles['purpose']).'</option>';
                                                }
                                        }    
                                }    
                        }
                }
                $html .= '</select>';
        }
        else 
        {
                $html .= $phrase['_no_roles_to_select'];	
        }
        return $html;
}
/**
* Function to increment a category's view count + 1.  This function now supports recursively tracking all views
* within a parent and child relationship.
*
* @param       integer        category id
* @param       string         mode (add or subtract) default add
*
* @return      nothing
*/
function add_category_viewcount($cid = 0, $mode = 'add')
{
        global $ilance;
        
	$sql = $ilance->db->query("
		SELECT views, parentid
		FROM " . DB_PREFIX . "categories
		WHERE cid = '" . intval($cid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$total = (int)$res['views'];
		
		if ($mode == 'add')
		{
			$total = ($res['views'] + 1);
		}
		else if ($mode == 'subtract') 
		{
			$total = ($res['views'] - 1);
			if ($total < 0)
			{
				$total = 0;
			}
		}        
		// if we have subcategories within this parent lets count the logic recursively
		if ($res['parentid'] > 0)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET views = '" . intval($total) . "'
				WHERE cid = '" . intval($cid) . "'
			", 0, null, __FILE__, __LINE__);
			
			add_category_viewcount($res['parentid'], $mode);
		}
		else 
		{   
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET views = '" . intval($total) . "'
				WHERE cid = '" . intval($cid) . "'
			", 0, null, __FILE__, __LINE__);
		}
		add_denomination_view_count($cid);
	}
}
function add_denomination_view_count($cid)
{
global $ilance;
$result=$ilance->db->query("select coin_series_denomination_no,coin_series_unique_no from ".DB_PREFIX."catalog_coin where PCGS='".$cid."'");
if($ilance->db->num_rows($result))
{
$line=$ilance->db->fetch_array($result);
$ilance->db->query("update ".DB_PREFIX."catalog_toplevel set traffic_count=traffic_count+1 where denomination_unique_no='".$line['coin_series_denomination_no']."'");
$ilance->db->query("update ".DB_PREFIX."catalog_second_level set traffic_count=traffic_count+1 where coin_series_unique_no='".$line['coin_series_unique_no']."'");
}
}
/**
* Function to handle the auction counts within category logic.  This function is usually called after a new auction is added or removed from the system.
* Additionally, this function works recursively.
*
* @param       integer        category id
* @param       string         mode (add or subtract)
*
* @return      nothing
*/
function build_category_count($cid = 0, $mode = 'add', $notes = '')
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT auctioncount, parentid
                FROM " . DB_PREFIX . "categories
                WHERE cid = '" . intval($cid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);                
                $total = (int)$res['auctioncount'];
                
                if ($mode == 'add')
                {
                        $total = ($res['auctioncount'] + 1);
                }
                else if ($mode == 'subtract') 
                {
                        $total = ($res['auctioncount'] - 1);
                        if ($total < 0)
                        {
                                $total = 0;
                        }
                }        
                // if we have subcategories within this parent lets count the logic recursively
                if ($res['parentid'] > 0)
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET auctioncount = '" . intval($total) . "'
                                WHERE cid = '" . intval($cid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        build_category_count($res['parentid'], $mode, "build_category_count(): $mode increment count category id $res[parentid]");
                }
                else 
                {   
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET auctioncount = '" . intval($total) . "'
                                WHERE cid = '" . intval($cid) . "'
                        ", 0, null, __FILE__, __LINE__);
                }
			build_catalog_count($cid, $mode);
        }
}
function build_catalog_count($cid,$mode)
{
global $ilance;
$result=$ilance->db->query("select coin_series_denomination_no,coin_series_unique_no from ".DB_PREFIX."catalog_coin where PCGS='".$cid."'");
if($ilance->db->num_rows($result)>0 and $mode=='add')
{
$line=$ilance->db->fetch_array($result);
$ilance->db->query("update ".DB_PREFIX."catalog_toplevel set auction_count=auction_count+1 where denomination_unique_no=".$line['coin_series_denomination_no']);
$ilance->db->query("update ".DB_PREFIX."catalog_second_level set auction_count=auction_count+1 where coin_series_unique_no=".$line['coin_series_unique_no']);
}else if($ilance->db->num_rows($result)>0 and $mode=='subtract')
{
$line=$ilance->db->fetch_array($result);
$ilance->db->query("update ".DB_PREFIX."catalog_toplevel set auction_count=auction_count-1 where denomination_unique_no=".$line['coin_series_denomination_no']);
$ilance->db->query("update ".DB_PREFIX."catalog_second_level set auction_count=auction_count-1 where coin_series_unique_no=".$line['coin_series_unique_no']);
}
}

function reduce_catalog_count($denomination_id,$series_id,$pcgs)
{
	global $ilance;
	$ilance->db->query("UPDATE ".DB_PREFIX."catalog_toplevel set auction_count=auction_count-1,auction_count_hist=auction_count_hist+1 where denomination_unique_no=".$denomination_id , 0, null, __FILE__, __LINE__);
	$ilance->db->query("UPDATE ".DB_PREFIX."catalog_second_level set auction_count=auction_count-1,auction_count_hist=auction_count_hist+1 where coin_series_unique_no=".$series_id , 0, null, __FILE__, __LINE__);
	$ilance->db->query("UPDATE " . DB_PREFIX . "catalog_coin SET auction_count = auction_count-1,auction_count_hist=auction_count_hist+1  WHERE pcgs = '" . intval($pcgs) . "'", 0, null, __FILE__, __LINE__);
	$ilance->db->query("UPDATE " . DB_PREFIX . "categories SET auctioncount = auctioncount-1  WHERE cid = '" . intval($pcgs) . "'", 0, null, __FILE__, __LINE__);
}
/**
* Function to process and construct a valid RSS feed
*
* @param       string         feed url
* @param       boolean        show details (default true)
* @param       string         headline title css style
* @param       string         detailed style css
* @param       integer        max number of items to capture from rss feed
*
* @return      nothing
*/
function construct_feed($feed_url, $showdetail = true, $headlinestyle, $detailstyle, $max = 10) 
{
        global $show_detail, $headline_style, $detail_style, $max, $count, $insideitem, $insideimage, $code2;
        
        $insideitem = false;
        $insideimage = false;
        $count = 0;
        $show_detail = $showdetail;
        $headline_style = $headlinestyle;
        $detail_style = $detailstyle;
        $xml_parser = xml_parser_create();
        
        xml_set_element_handler($xml_parser, 'construct_feed_start_element', 'construct_feed_end_element');
        xml_set_character_data_handler($xml_parser, 'construct_feed_character_data');
        
        // fopen method
        $fp = @fopen($feed_url, 'r') or die('Error reading RSS data.');
        if ($fp)
        {
                while ($data = fread($fp, 4096))
                //echo $data;
                xml_parse($xml_parser, $data, feof($fp)) or die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
                fclose($fp);
        }
        else
        {
                $code2 .= '<span class="' . $detail_style . '">Syndicated content not available</span>';
        }
        
        xml_parser_free($xml_parser);
}
/**
* Callback function to process rss item start tag element data for the construct_feed() function
*
* @param       object         xml parser object
* @param       string         xml tag name (ITEM or IMAGE)
* @param       string         xml tag attributes
*
* @return      nothing
*/
function construct_feed_start_element($parser, $name, $attrs)
{
        global $insideitem, $tag, $title, $description, $link, $image, $insideimage, $code2;
        
        if ($insideitem OR $insideimage)
        {
                $tag = $name;
        }
        if ($name == 'ITEM')
        {
                $insideitem = true;
        }
        if ($name == 'IMAGE')
        {
                $insideimage = true;
        }
}
/**
* Callback function to process rss item end tag element data for the construct_feed() function
*
* @param       object         xml parser object
* @param       string         xml tag name (URL OR ITEM)
*
* @return      nothing
*/
function construct_feed_end_element($parser, $name)
{
        global $insideitem, $tag, $title, $description, $link, $image, $insideimage, $show_detail, $headline_style, $detail_style, $count, $max, $code2;
        
        if ($name == 'URL')
        {
                $code2 .= '<img src="' . trim($image) . '" border="0" /><br /><br />';
                $insideimage = false;
                $image = '';
        }
        else if ($name == 'ITEM' AND $count < $max)
        {
                $count++;
                $code2 .= '<div style="padding-bottom:3px"><span class="blue" style="font-size:13px"><a href="' . $link . '" target="_blank"><strong>' . trim($title) . '</strong></a></span></div>';
                if ($show_detail)
                { 
                        $code2 .= '<div style="padding-bottom:9px">' . trim($description) . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                }
                else
                {
                        $code2 .= '<br />';
                }
                
                $title = $description = $link = '';
                $insideitem = false;
        }
        else if ($count >= $max)
        {
                $title = $description = $link = '';
                $insideitem = false;
        }
}
/**
* Callback function to process rss item character data for the construct_feed() function
*
* @param       object         xml parser object
* @param       string         xml tag name
*
* @return      nothing
*/
function construct_feed_character_data($parser, $data)
{
        global $insideitem, $tag, $title, $description, $link, $image, $insideimage, $code2;
        
        if ($insideimage)
        {
                switch ($tag)
                {
                        case 'URL':
                        {
                                $image .= $data;
                                break;
                        }
                }
        }        
        if ($insideitem)
        {
                switch ($tag)
                {
                        case 'TITLE':
                        {
                                $title .= trim($data);
                                break;
                        }                    
                        case 'DESCRIPTION':
                        {
                                $description .= trim($data);
                                break;
                        }                    
                        case 'LINK':
                        {
                                if (!is_string($link))
                                {
                                    $link = '';
                                }
                                $link .= trim($data);
                                break;
                        }
                }
        }
}
/**
* Function to break up a long string with no spaces based on a supplied character limit
* Upgraded to be aware of multibyte UTF-8 encoded strings
*
* @param       string         text
* @param       integer        chracter limit to break up
*
* @return      string         Formatted text
*/
function print_string_wrap($text = '', $limit = 50)
{
        global $ilance, $myapi;
        
        if ($limit > 0 AND !empty($text))
        {
                return mb_ereg_replace('#(?>[^\s&/<>"\\-\[\]]|&[\#a-z0-9]{1,7};){' . $limit . '}#iu', '$0  ', $text);
        }
        else
        {
                return $text;
        }
}
/**
* Function to process and print out a username bit with icons based on various bits of information
*
* @param       integer        user id
*
* @return      string         Formatted text
*/
function construct_username_bits($userid = 0)
{
        global $ilance, $myapi, $phrase;
        
        $html = '';
        $pattern = '';
        
        $roles = $ilance->db->query("
                SELECT " . DB_PREFIX . "subscription_roles.custom,
                " . DB_PREFIX . "subscription_roles.roletype,
                " . DB_PREFIX . "subscription_roles.roleusertype
                FROM " . DB_PREFIX . "subscription_user
                LEFT JOIN " . DB_PREFIX . "subscription_roles ON (" . DB_PREFIX . "subscription_user.roleid = " . DB_PREFIX . "subscription_roles.roleid)
                WHERE " . DB_PREFIX . "subscription_user.user_id = '" . intval($userid) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($roles) > 0)
        {
                $role = $ilance->db->fetch_array($roles, DB_ASSOC);
                if (!empty($role['custom']))
                {
                        $pattern = $role['custom'];
                }
        }
        if (!empty($pattern))
        {
                // [fbscore] [stars] [fbpercent] [store] [verified] [subscription]
                $ilance->feedback = construct_object('api.feedback');
                $ilance->subscription =  construct_object('api.subscription');
                        
                $memberinfo = array();
                $memberinfo = $ilance->feedback->datastore(intval($userid));
                
                $pattern = str_replace('[fbscore]', $memberinfo['score'], $pattern);
                $pattern = str_replace('[fbpercent]', '<a href="' . print_username(intval($userid), 'url', 0, '', '') . '" title="' . $phrase['_total_positive_feedback_percentile'] . '">' . $memberinfo['pcnt'] . '%</a>', $pattern);
                $pattern = str_replace('[rating]', '<a href="' . print_username(intval($userid), 'url', 0, '', '') . '" title="' . $phrase['_total_feedback_rating_out_of_500'] . '">' . $memberinfo['rating'] . '</a>', $pattern);
                $pattern = str_replace('[stars]', $ilance->feedback->print_feedback_icon($memberinfo['score']), $pattern);
                $pattern = str_replace('[store]', '', $pattern);
                $pattern = str_replace('[verified]', '', $pattern);
                $pattern = str_replace('[subscription]', $ilance->subscription->print_subscription_icon(intval($userid)), $pattern);
                
                $html .= $pattern;
        }
        
        return $html;
}
/**
* Function to fetch a valid country id from the datastore based on an actual country name along with a short language identifier
*
* @param       string         country name
* @param       string         short language identifier
*
* @return      integer        Returns the country id
*/
function fetch_country_id($countryname = '', $slng = 'eng')
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT locationid
                FROM " . DB_PREFIX . "locations
                WHERE location_" . $ilance->db->escape_string($slng) . " = '" . $ilance->db->escape_string($countryname) . "' OR location_eng = '" . $ilance->db->escape_string($countryname) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                return intval($res['locationid']);
        }
        
        return '500';
}
/**
* Function to fetch a user's id from the datastore based on an actual username
*
* @param       string         user name
*
* @return      integer        Returns the user id
*/
function fetch_userid($username)
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT user_id
                FROM " . DB_PREFIX . "users
                WHERE username = '" . $ilance->db->escape_string($username) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $user = $ilance->db->fetch_array($sql);
                return $user['user_id'];
        }
        
        return 0;
}
/**
* Function to fetch an admin's username from the datastore based on an actual user/admin id
*
* @param       integer        user/admin id
*
* @return      string         Returns the admin user name
*/
function fetch_adminname($adminid)
{
        global $ilance, $phrase, $myapi, $phrase;
        
        $sql = $ilance->db->query("
                SELECT username
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($adminid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                return stripslashes($res['username']);
        }
        
        return $phrase['_unknown'];
}
/**
* Function to print a user's username based on seo and other elements such as icons, subscription info, etc
*
* @param       integer        user id
* @param       string         mode
* @param       boolean        is bold? (default false)
* @param       string         extra info
* @param       string         extra seo info
* @param       string         display name
*
* @return      string         Returns a formatted version of the user's username
*/
function print_username($userid, $mode = 'href', $bold = 0, $extra = '', $extraseo = '', $displayname = '')
{
        global $ilance, $myapi, $ilpage, $ilconfig;
        
        $username = fetch_user('username', intval($userid));
        $html = '';
        
        // modes: href and plain
        if ($mode == 'href')
        {
                $displayname = $username;
                // bold usernames?
                if (!empty($bold) AND $bold)
                {
                        $displayname = '<strong>'.$username.'</strong>';
                }
                // does admin use SEO urls?
                if ($ilconfig['globalauctionsettings_seourls'])
                {
                        $html .= '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . construct_seo_url_name($username) . $extraseo . '" rel="nofollow">' . $displayname . '</a> ' . construct_username_bits(intval($userid));
                }
                else
                {
                        $html .= '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . intval($userid) . $extra . '" rel="nofollow">' . $displayname . '</a> ' . construct_username_bits(intval($userid));
                }    
        }
        else if ($mode == 'plain')
        {
                $displayname = $username;
                // bold usernames?
                if (!empty($bold) AND $bold)
                {
                        $username = '<strong>'.$displayname.'</strong>';
                }
                $html = $username;
        }
        else if ($mode == 'custom')
        {
                // bold usernames?
                if (!empty($bold) AND $bold)
                {
                        $displayname = '<strong>'.$displayname.'</strong>';
                }
                // does admin use SEO urls?
                if ($ilconfig['globalauctionsettings_seourls'])
                {
                        $html .= '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . construct_seo_url_name($username) . $extraseo . '" rel="nofollow">' . $displayname . '</a>';
                }
                else
                {
                        $html .= '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . intval($userid) . $extra . '" rel="nofollow">' . $displayname . '</a>';
                }
        }
        else if ($mode == 'url')
        {
                // does admin use SEO urls?
                if ($ilconfig['globalauctionsettings_seourls'])
                {
                        $html .= ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . construct_seo_url_name($username) . $extraseo;
                }
                else
                {
                        $html .= ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . intval($userid) . $extra;
                }
        }
        
        return $html;
}
/**
* Function to print a user's country based on a supplied user id and a short language identifier to display the proper country name in the appropriate language
*
* @param       integer        user id
* @param       string         short language identifier (default eng)
*
* @return      string         Returns the user's country name
*/
function print_user_country($userid, $slng = 'eng')
{
        global $ilance, $myapi, $phrase;
        
        $countryid = fetch_user('country', intval($userid));
        
        $sql = $ilance->db->query("
                SELECT location_$slng AS countryname
                FROM " . DB_PREFIX . "locations
                WHERE locationid = '" . $countryid . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                return stripslashes($res['countryname']);
        }
        
        return $phrase['_unknown'];
}
/**
* Function to print a country name based on a supplied country id and a short language identifier to display the proper country name in the appropriate language
*
* @param       integer        country id
* @param       string         short language identifier (default eng)
* @param       boolean        short form output? (default false)
*
* @return      string         Returns the user's country name
*/
function print_country_name($countryid, $slng = 'eng', $shortform = false)
{
        global $ilance, $myapi, $phrase;
        
        if (empty($slng))
        {
                $slng = 'eng';
        }
        
        $sql = $ilance->db->query("
                SELECT location_$slng AS countryname, cc
                FROM " . DB_PREFIX . "locations
                WHERE locationid = '" . intval($countryid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                if ($shortform)
                {
                        return $res['cc'];
                }
                
                return $res['countryname'];
        }
        
        return $phrase['_unknown'];
}
/**
* Function to fetch a user's email address from the datastore based on an actual user id
*
* @param       string         unit type (D, M or Y) 
*
* @return      string         Returns the actual unit type phrase in the appropriate language
*/
function print_unit($unit = '')
{
        global $phrase;
        
        if (!empty($unit))
        {
                switch ($unit)
                {
                        case 'D':
                        {
                                return $phrase['_unit_d'];
                                break;
                        }                    
                        case 'M':
                        {                        
                                return $phrase['_unit_m'];
                                break;
                        }                    
                        case 'Y':
                        {
                                return $phrase['_unit_y'];
                                break;
                        }
                }
        }
}
/**
* Function to print escrow fees (escrow commission fees must be enabled)
*
* @param       string         mode (as_service_provider, as_service_buyer, as_merchant_provider, as_merchant_buyer or as_admin)
* @param       string         amount
* @param       integer        listing id #
*
* @return      string         Returns formatted escrow fees amount (if applicable)
*/
function print_escrow_fees($mode = '', $amount = 0, $pid = 0)
{
        global $ilance, $myapi, $ilconfig;
        
	$currencyid = fetch_auction('currencyid', $pid);
	
        if ($ilconfig['escrowsystem_escrowcommissionfees'])
        {
                if (isset($mode) AND !empty($mode))
                {
                        switch ($mode)
                        {
                                case 'as_service_provider';
                                {
                                        if ($ilconfig['escrowsystem_providerfixedprice'] > 0 OR $ilconfig['escrowsystem_providerpercentrate'] > 0)
                                        {
                                                $html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $amount, $currencyid);
                                        }
                                        else
                                        {
                                                $html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);   
                                        }
                                        break;
                                }                            
                                case 'as_service_buyer';
                                {
                                        if ($ilconfig['escrowsystem_servicebuyerfixedprice'] > 0 OR $ilconfig['escrowsystem_servicebuyerpercentrate'] > 0)
                                        {
                                                $html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $amount, $currencyid);
                                        }
                                        else
                                        {
                                                $html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);   
                                        }
                                        break;
                                }                            
                                case 'as_merchant_provider';
                                {
                                        if ($ilconfig['escrowsystem_merchantfixedprice'] > 0 OR $ilconfig['escrowsystem_merchantpercentrate'] > 0)
                                        {
                                                $html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $amount, $currencyid);
                                        }
                                        else
                                        {
                                                $html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);   
                                        }
                                        break;
                                }                            
                                case 'as_merchant_buyer';
                                {
                                        if ($ilconfig['escrowsystem_bidderfixedprice'] > 0 OR $ilconfig['escrowsystem_bidderpercentrate'] > 0)
                                        {
                                                $html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $amount, $currencyid);
                                        }
                                        else
                                        {
                                                $html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);   
                                        }
                                        break;
                                }                            
                                case 'as_admin';
                                {
                                        $html = $ilance->currency->format($amount, $currencyid);
                                        break;
                                }
                        }
                        
                        return $html;
                }
        }
        else
        {
                return print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);
        }
}
/**
* Function to calculate the final value fee based on an amount, category id, category type and bid amount type
*
* @param       string         amount
* @param       integer        category id
* @param       string         category type (servicebuyer or serviceprovider)
* @param       string         bid amount type
*
* @return      string         Returns formatted final value feee (if applicable)
*/
// Murugan Changes On Nov 15 For Subscription Based FVF
//function calculate_final_value_fee($bidamount = 0, $cid = 0, $cattype = '', $bidamounttype = '')
function calculate_final_value_fee($bidamount = 0, $userid = 0, $cattype = '', $bidamounttype = '')
{
        
		global $ilance, $myapi, $ilconfig, $phrase;
        // murugan changes nov 15
        //$cid = intval($cid);
		$userid = intval($userid);
        $tiers = $price = $total = $remaining = $fvf = 0;
        
        $bidamount = $ilance->currency->string_to_number($bidamount);
        
        if (isset($cattype) AND ($cattype == 'servicebuyer' OR $cattype == 'serviceprovider'))
        {
                $cattype = 'service';
        }
        
        // first check if admin uses fixed fees in this category
        if ($ilance->categories->usefixedfees($cid) AND !empty($bidamounttype))
        {
                // admin charges a fixed fee within this category to service providers
                // let's determine if the bid amount type logic is configured
                if ($bidamounttype != 'entire' AND $bidamounttype != 'item' AND $bidamounttype != 'lot')
                {
                        // bid amount type passes accepted commission types
                        // let's output our fixed commission amount
                        $fvf = $ilance->categories->fixedfeeamount($cid);
                        $fvf = sprintf("%01.2f", $fvf);
                        
                        return $fvf;
                }
        }
        else
        {
                // fetch final value group for this category
            // murugan changes nov 15
		       /* $categories = $ilance->db->query("
                        SELECT finalvaluegroup
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . $cid . "'
                ", 0, null, __FILE__, __LINE__);*/
				
				$subid=$ilance->db->query(" 
								SELECT user.subscriptionid, user.user_id, sub.subscriptiongroupid, perm.value
                                FROM " . DB_PREFIX . "subscription_user user
                                LEFT JOIN " . DB_PREFIX . "subscription sub ON (sub.subscriptionid = user.subscriptionid)
                                LEFT JOIN " . DB_PREFIX . "subscription_permissions perm ON (perm.subscriptiongroupid = sub.subscriptiongroupid)
                                WHERE user.user_id = '" . intval($userid) . "'
								 AND sub.active = 'yes'
                                        AND user.active = 'yes'
                                        AND perm.subscriptiongroupid = sub.subscriptiongroupid
                                        AND perm.accessname = 'fvffees' ");
										
				 // murugan changes nov 15						
               // if ($ilance->db->num_rows($categories) > 0)
				 if ($ilance->db->num_rows($subid) > 0)
                {
                         
						 // murugan changes nov 15
						//$cats = $ilance->db->fetch_array($categories);
						$cats = $ilance->db->fetch_array($subid);
                        //if (!empty($cats['finalvaluegroup']))
						if (!empty($cats['value'] ))
                        {
                                
								/*$finalvalues = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "finalvalue
                                        WHERE groupname = '" . trim($cats['finalvaluegroup']) . "'
                                                AND state = '" . $ilance->db->escape_string($cattype) . "'
                                        ORDER BY finalvalue_from ASC
                                ", 0, null, __FILE__, __LINE__);*/
								
								 $finalvalues = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "finalvalue
                                        WHERE lower(groupname) = '" . strtolower($cats['value']) . "'
                                                AND state = '" . $ilance->db->escape_string($cattype) . "'
                                        ORDER BY finalvalue_from ASC
                                ", 0, null, __FILE__, __LINE__);
				
                                $totaltiers = (int)$ilance->db->num_rows($finalvalues);
				
                                if ($totaltiers == 1)
                                {
                                        // #### SINGLE FVF TIER LOGIC ##########
                                        $fees = $ilance->db->fetch_array($finalvalues);
                                        
                                        if ($bidamount >= $fees['finalvalue_from'])
                                        {
                                                if ($fees['amountfixed'] > 0)
                                                {
                                                        $fvf += $fees['amountfixed'];
                                                        $fv   = $fees['amountfixed'];
                                                }
                                                else
                                                {
                                                        $fvf += ($bidamount * $fees['amountpercent'] / 100);
                                                        $fv   = ($bidamount * $fees['amountpercent'] / 100);
                                                }
                                        }
                                        
                                        if (isset($fvf))
                                        {
                                                $fvf = sprintf("%01.2f", $fvf);
                                                return $fvf;
                                        }
                                }
                                else
                                {
                                        // #### MULTIPLE FVF TIER LOGIC ########
                                        if ($totaltiers > 0)
                                        {
                                                while ($fees = $ilance->db->fetch_array($finalvalues))
                                                {
                                                        $tiers++;
                                                        if ($fees['finalvalue_to'] != '-1')
                                                        {
                                                                if ($bidamount >= $fees['finalvalue_from'] AND $bidamount <= $fees['finalvalue_to'])
                                                                {
                                                                        $bid = ($bidamount - ($fees['finalvalue_to'] - $fees['finalvalue_from']));
                                                                        
                                                                        if ($tiers == 1)
                                                                        {
                                                                                if ($fees['amountfixed'] > 0)
                                                                                {
                                                                                        // fixed
                                                                                        $fvf += $fees['amountfixed'];
                                                                                        $fv   = $fees['amountfixed'];
                                                                                }
                                                                                else
                                                                                {
                                                                                        // percentage
                                                                                        $fvf += ($bidamount * $fees['amountpercent'] / 100);
                                                                                        $fv   = ($bidamount * $fees['amountpercent'] / 100);
                                                                                }
                                                                        }
                                                                        else
                                                                        {
                                                                                if ($fees['amountfixed'] > 0)
                                                                                {
                                                                                        // fixed
                                                                                        $fvf += $fees['amountfixed'];
                                                                                        $fv   = $fees['amountfixed'];
                                                                                }
                                                                                else
                                                                                {
                                                                                        // percent
                                                                                        $fvf += ($remaining * $fees['amountpercent'] / 100);
                                                                                        $fv   = ($remaining * $fees['amountpercent'] / 100);    
                                                                                }
                                                                        }
                                                                        
                                                                        break;
                                                                }
                                                                else
                                                                {
                                                                        if ($fees['amountfixed'] > 0)
                                                                        {
                                                                                // fixed
                                                                                $fvf += $fees['amountfixed'];
                                                                                $fv   = $fees['amountfixed'];
                                                                        }
                                                                        else
                                                                        {
                                                                                // percent
                                                                                $fvf += (($fees['finalvalue_to'] - $fees['finalvalue_from']) * $fees['amountpercent'] / 100);
                                                                                $fv   = (($fees['finalvalue_to'] - $fees['finalvalue_from']) * $fees['amountpercent'] / 100);
                                                                        }
                                                                        
                                                                        // calculate remaining bid amount for next tier
                                                                        $bid = ($bidamount - ($fees['finalvalue_to'] - $fees['finalvalue_from']));
                                                                        $remaining = ($bid - $fees['finalvalue_from']);
                                                                }
                                                        }
                                                        else
                                                        {
                                                                if ($bidamount >= $fees['finalvalue_from'])
                                                                {
                                                                        if ($fees['amountfixed'] > 0)
                                                                        {
                                                                                $fvf += $fees['amountfixed'];
                                                                                $fv   = $fees['amountfixed'];
                                                                        }
                                                                        else
                                                                        {
                                                                                $fvf += ($remaining * $fees['amountpercent'] / 100);
                                                                                $fv   = ($remaining * $fees['amountpercent'] / 100);
                                                                        }
                                                                }
                                                        }
                                                }
                                                
                                                if (isset($fvf))
                                                {
                                                        $fvf = sprintf("%01.2f", $fvf);
                                                        return $fvf;
                                                }
                                        }    
                                }            
                        }
                }
        }
        
        return 0;
}
function calculate_final_value_fee_new($bidamount = 0, $projectid = 0, $cattype = '', $bidamounttype = '')
{
		global $ilance, $myapi, $ilconfig, $phrase;
		$projectid = intval($projectid);
        $tiers = $price = $total = $remaining = $fvf = 0;
        $bidamount = $ilance->currency->string_to_number($bidamount);
        if (isset($cattype) AND ($cattype == 'servicebuyer' OR $cattype == 'serviceprovider'))
        {
                $cattype = 'service';
        }
				$sub = $ilance->db->query("SELECT fvf_id FROM " .DB_PREFIX. "coins WHERE coin_id = '".$projectid."'");
				$subres = $ilance->db->fetch_array($sub);
				$fees['finalvalue_from'] = 1000;
				$condition = 5;
				if($bidamount <= $fees['finalvalue_from'])
				{
					$fvfees += ($bidamount * $subres['fvf_id'] / 100);
                    $fv = ($bidamount * $subres['fvf_id'] / 100);
					
					$addfvf = ($bidamount * 10/100);
					if($addfvf >= $condition)
					{
					  $newfvf = $addfvf + $fvfees;
					}
					else
					{
						$newfvf = $condition + $fvfees;
					}
					$fvf = $newfvf;
				}
				else
				{
					$addfvf = ($bidamount * 10/100);
					$fvf = $addfvf;
				}
        if (isset($fvf))
		{
				$fvf = sprintf("%01.2f", $fvf);
				return $fvf;
		}
}
/**
* Function to calculate the escrow fee based on an amount and a particular logic type
*
* @param       string         amount
* @param       string         logic type (merchantbuynow, bidderbuynow, servicebuyer, serviceprovider, productmerchant or productbidder)
*
* @return      string         Returns formatted final value feee (if applicable)
*/
function calculate_escrow_fee($amount = 0, $logictype = '')
{
	global $ilance, $myapi, $ilconfig;
        
	$fee = 0;
	if ($ilconfig['escrowsystem_escrowcommissionfees'] AND $logictype != '')
	{
		$ilance->escrow = construct_object('api.escrow');
                
		$logic = $ilance->escrow->fetch_escrow_commission_logic($logictype);
		if ($logic == 'fixed')
		{
			// fixed service commission logic
			$fee = $ilance->escrow->fetch_escrow_commission($logictype);
		}
		else
		{
			// percentage of the overall cost logic
			$fee = ($amount * $ilance->escrow->fetch_escrow_commission($logictype) / 100);
		}
                
                $fee = $ilance->currency->string_to_number($fee);
	}
        
	return $fee;
}
/**
* Function to calculate the escrow fee based on an amount and a particular logic type
*
* @param       string         fee type (fvf, ins or esc)
* @param       string         amount
* @param       string         category type
* @param       integer        category id
* @param       string         bid amount type
*
* @return      string         Returns formatted final value feee (if applicable)
*/
// Murugan changes On Nov 12 for subscrption Based FVF
//function fetch_calculated_amount($feetype = 'fvf', $amount = 0, $cattype = '', $cid = 0, $bidamounttype = '')
function fetch_calculated_amount($feetype = 'fvf', $amount = 0, $cattype = '', $userid = 0, $bidamounttype = '')
{
	global $ilance;
        
	if (!isset($bidamounttype) OR empty($bidamounttype))
	{
                $bidamounttype = '';
	}
        
	$value = 0;
	
	($apihook = $ilance->api('fetch_calculated_amount_start')) ? eval($apihook) : false;
	
	switch ($feetype)
	{
		case 'fvf':
                {
                        //$value = calculate_final_value_fee($amount, $cid, $cattype, $bidamounttype);
						$value = calculate_final_value_fee($amount, $userid, $cattype, $bidamounttype);
                        break;
                }	
		case 'ins':
                {
                        // to be added
                        break;
                }	    
		case 'esc':
                {
                        $value = calculate_escrow_fee($amount, $cattype);
                        break;
                }
	}
	
	($apihook = $ilance->api('fetch_calculated_amount_end')) ? eval($apihook) : false;
	
	return $value;
}
/**
* Function to print an invoice type phrase based on the currently selected language
*
* @param       string         invoice type (subscription, commission, p2b, buynow, credential, debit, credit, escrow, refund or storesubscription)
*
* @return      string         Returns formatted final value feee (if applicable)
*/
function print_transaction_type($invoicetype = '')
{
	global $ilance, $myapi, $phrase, $ilconfig;
        
        $html = '';
	if (isset($invoicetype) AND !empty($invoicetype))
	{
		switch ($invoicetype)
		{
			case 'p2b':
                        {
                                $html = $phrase['_generated_invoice'];
                                break;
                        }
			case 'buynow':
                        {
                                $html = $phrase['_buy_now'];
                                break;
                        }
                        case 'storesubscription':
			case 'subscription':
                        case 'commission':
			case 'credential':
			case 'debit':
                        case 'escrow':
                        {
                                $html = $phrase['_account_debit'];
                                break;
                        }
			case 'credit':
                        {
                                $html = $phrase['_account_credit'];
                                break;
                        }
			case 'refund':
                        {
                                $html = $phrase['_refund'];
                                break;
                        }
		}
                
                ($apihook = $ilance->api('print_transaction_type_end')) ? eval($apihook) : false;
	}
        
        return $html;
}
/**
* Function to determine if we can display the profile of a particular user
*
* @param       integer        user id
*
* @return      string         Returns true or false
*/
function display_profile($userid = 0)
{
        global $ilance, $myapi;
        
        return fetch_user('displayprofile', intval($userid));
}
/**
* Function to determine if a particular user has answered any profile questions.
*
* @param       integer        user id
*
* @return      string         Returns true or false
*/
function has_answered_profile_questions($userid = 0)
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT questionid
                FROM " . DB_PREFIX . "profile_answers
                WHERE user_id = '".intval($userid)."'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $sql2 = $ilance->db->query("
                                SELECT groupid
                                FROM " . DB_PREFIX . "profile_questions
                                WHERE questionid = '".$res['questionid']."'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql2) > 0)
                        {
                                while ($res2 = $ilance->db->fetch_array($sql2))
                                {
                                        $sql3 = $ilance->db->query("
                                                SELECT cid
                                                FROM " . DB_PREFIX . "profile_groups
                                                WHERE groupid = '".$res2['groupid']."'
                                                        AND canremove = '1'
                                                        AND visible = '1'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql3) > 0)
                                        {
                                                return 1;
                                        }
                                }
                        }
                }
        }
        
        return 0;
}
/**
* Function to print the subscription renewal date/timestamp based on days.
*
* @param       integer        days
*
* @return      string         Returns datetime stamp (ie: 2007-02-01 22:00:00)
*/
function print_subscription_renewal_datetime($days)
{
        global $ilconfig;
        
        $date = gmdate('Y-m-d', mktime(0, 0, 0, gmdate('m', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('d', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))+intval($days), gmdate('Y', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))));
        $time = gmdate('H:i:s', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']));
        
        return "$date $time";
}
/**
* Function to fetch buyers invited to a product auction event.
*
* @param       integer        project id
*
* @return      string         Returns usernames separated by a line break
*/
function fetch_member_invite_list($projectid = 0)
{
        global $ilance, $myapi;
        
        $html = '';
        
        $sql = $ilance->db->query("
                SELECT i.buyer_user_id, u.username
                FROM " . DB_PREFIX . "project_invitations AS i,
                " . DB_PREFIX . "users AS u
                WHERE i.buyer_user_id = u.user_id
                        AND project_id = '".intval($projectid)."'
                GROUP BY buyer_user_id
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {            
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $html .= $res['username'] . "\n";   
                }
        }
        
        return $html;
}
/**
* Callback function for convert_urlencoded_unicode() which will also use iconv library if installed
*
* @param       string         hexidecimal character
* @param       string         character set
*
* @return      string         Returns a numeric entity
*/
function convert_unicode_char_to_charset($unicodeint, $charset)
{
        $isutf8 = (mb_strtolower($charset) == 'utf-8');
        if ($isutf8)
        {
                return convert_int2utf8($unicodeint);
        }
        if (function_exists('iconv'))
        {
                // convert this character -- if unrepresentable, it should fail
                $output = @iconv('UTF-8', $charset, convert_int2utf8($unicodeint));
                if ($output !== false AND $output !== '')
                {
                        return $output;
                }
        }
        
        return "&#$unicodeint;";
}
/**
* Function to conver a urlencoded string into unicode for formatting purposes
*
* @param       string         text
*
* @return      string         Returns a formatted unicode string
*/
function convert_urlencoded_unicode($text)
{
        global $ilconfig;
        
        $isutf8 = (mb_strtolower($ilconfig['template_charset']) == 'utf-8' OR mb_strtolower($ilconfig['template_charset']) == 'utf8');
        $return = preg_replace('#%u([0-9A-F]{1,4})#ie', "convert_unicode_char_to_charset(hexdec('\\1'), \$ilconfig['template_charset'])", $text);
        if (!$isutf8)
        {
                $return = preg_replace('#&([a-z]+);#i', '&amp;$1;', $return);
                $return = @html_entity_decode($return, ENT_NOQUOTES, $ilconfig['template_charset']);
        }
        
        return $return;
}
/**
* Function to print the WYSIWYG / BBcode editor
*
* @param       string         field name
* @param       string         message
* @param       string         wysiwyg editor instance id
* @param       boolean        enable wysiwyg?
* @param       boolean        show switch mode (bbedit to wysiwyg) button?
* @param       boolean        is html? (default false)
* @param       string         width of wysiwyg editor
* @param       string         height of wysiwyg editor
*
* @return      string         Returns usernames separated by a line break
*/
function print_wysiwyg_editor($fieldname = '', $text = '', $instanceid = 'bbeditor', $enablewysiwyg = '1', $showswitchmode = '1', $ishtml = false, $width = '', $height = '')
{
        global $ilance, $myapi, $ilconfig, $phrase, $headinclude, $show;
        
        $cssclass = 'ilance_wysiwyg';
        
        $show['footerwysiwygpopup'] = true;
        
        if (isset($text))
        { // bbcode coming from db or preview
                $text = htmlspecialchars_uni($text);
        }
        
        $html = '';
        if ($instanceid == 'bbeditor')
        {
                // we'll only show one css style since there may be multiple instances of the bbeditor being loaded on same page
                $headinclude .= '
<style id="wysiwyg_html" type="text/css">
<!--
' . $ilance->styles->css_cache['csswysiwyg'] . '
//-->
</style>
';
        }
        
$html .= '<div class="' . $cssclass . '"><textarea style="visibility: hidden; position: absolute; top: 0; left: 0;" name="' . $fieldname . '" id="' . $fieldname . '_id" rows="1" cols="1" tabindex="2">' . $text . '</textarea>';
if ($instanceid == 'bbeditor')
{
        $headinclude .= '
<script type="text/javascript" language="Javascript">
<!--
var view_richtext = ' . (int)$enablewysiwyg . ';
var show_switch = ' . (int)$showswitchmode . ';
var show_mode_editor = ' . (int)$showswitchmode . ';
if (view_richtext == 0)
{
        show_mode_editor = 0;
}
//-->
</script>
<script type="text/javascript" src="' . $ilconfig['template_relativeimagepath'] . DIR_FUNCT_NAME . '/javascript/functions_wysiwyg.js"></script>';
}
$html .= '
<script type="text/javascript" language="Javascript">
<!--';
if ($instanceid == 'bbeditor')
{
        $html .= '
function fetch_bbeditor_data()
{
        prepare_bbeditor_wysiwygs();            
        var bbcode_output = fetch_js_object(\'bbeditor_bbcode_ouput_' . $instanceid . '\').value;
        fetch_js_object(\'' . $fieldname . '_id\').value = bbcode_output;
}
';
}
else
{
        $html .= '
function fetch_bbeditor_data_' . $instanceid . '()
{
        prepare_bbeditor_wysiwygs();            
        var bbcode_output = fetch_js_object(\'bbeditor_bbcode_ouput_' . $instanceid . '\').value;
        fetch_js_object(\'' . $fieldname . '_id\').value = bbcode_output;
}
';
}
$html .= '
var bbcodetext = fetch_js_object(\'' . $fieldname . '_id\').value;
print_wysiwyg_editor(\'max\', \'' . $instanceid . '\', bbcodetext, \'100%\', \'250px\');
//-->
</script></div>';
    
        ($apihook = $ilance->api('print_wysiwyg_editor_end')) ? eval($apihook) : false;
    
        return $html;
}
/**
* Function to determine if a supplied email address is valid based on it's apperence
*
* @param       string         email address
*
* @return      string         Returns true or false if email address is valid
*/
function is_valid_email($email = '')
{
        return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s\'"<>]+\.+[a-z]{2,6}))$#si', $email);
}
/**
* Function to deactivate a particular subscription plan for a specific user id
*
* @param       string         user id
*
* @return      void
*/
function deactivate_subscription_plan($userid = 0)
{
        global $ilance, $myapi;
        
        $ilance->db->query("
                UPDATE " . DB_PREFIX . "subscription_user
                SET active = 'no'
                WHERE user_id = '".intval($userid)."'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);    
}
/**
* Function to activate a particular subscription plan for a specific user id
*
* @param       string         user id
* @param       string         start date
* @param       string         renew date
* @param       boolean        is recurring? (default false)
* @param       integer        invoice id
* @param       integer        subscription id
* @param       string         payment method
* @param       integer        role id
* @param       string         cost
*
* @return      void
*/
function activate_subscription_plan($userid = 0, $startdate = '', $renewdate = '', $recurring = 0, $invoiceid = 0, $subscriptionid = 0, $paymethod = '', $roleid = 0, $cost = 0)
{
        global $ilance, $myapi, $ilconfig, $phrase;
        
        // do we already have a subscription in the database for this member?
        $sql = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "subscription_user
                WHERE user_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                // we do ! let's change it ..
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "subscription_user
                        SET active = 'yes',
                        cancelled = '0',
                        startdate = '" . $ilance->db->escape_string($startdate) . "',
                        renewdate = '" . $ilance->db->escape_string($renewdate) . "',
                        recurring = '" . intval($recurring) . "',
                        roleid = '" . intval($roleid) . "',
                        subscriptionid = '" . intval($subscriptionid) . "',
                        invoiceid = '" . intval($invoiceid) . "',
                        autopayment = '1'
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
        }
        else
        {
                // we will create a new subscription for this user
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "subscription_user
                        (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, cancelled, recurring, invoiceid, roleid)
                        VALUES(
                        NULL,
                        '" . intval($subscriptionid) . "',
                        '" . intval($userid) . "',
                        '" . $ilance->db->escape_string($paymethod) . "',
                        '" . $ilance->db->escape_string($startdate) . "',
                        '" . $ilance->db->escape_string($renewdate) . "',
                        '1',
                        'yes',
                        '0',
                        '" . intval($recurring) . "',
                        '" . intval($invoiceid) . "',
                        '" . intval($roleid) . "')
                ", 0, null, __FILE__, __LINE__);
        }
        
	$existing = array(
                '{{provider}}' => fetch_user('username', intval($userid)),
                '{{invoice_id}}' => intval($invoiceid),
                '{{invoice_amount}}' => $ilance->currency->format($cost),
		'{{paymethod}}' => $paymethod,
		'{{startdate}}' => $startdate,
		'{{renewdate}}' => $renewdate,
		'{{subscriptionid}}' => $subscriptionid,
		'{{roleid}}' => $roleid,
        );
	
        $ilance->email = construct_dm_object('email', $ilance);
        
        $ilance->email->mail = SITE_EMAIL;
        $ilance->email->slng = fetch_site_slng();
        $ilance->email->get('subscription_paid_via_paypal_admin');		
        $ilance->email->set($existing);
        $ilance->email->send();
        
        $ilance->email->mail = fetch_user('email', intval($userid));
        $ilance->email->slng = fetch_user_slng(intval($userid));
        $ilance->email->get('subscription_paid_via_paypal');		
        $ilance->email->set($existing);
        $ilance->email->send();
}
/**
* Function to cancel a particular subscription plan for a specific user id
*
* @param       integer        user id
* @param       integer        invoice id (optional)
* @param       string         payment gateway (optional)
*
* @return      void
*/
function cancel_subscription_plan($userid = 0, $invoiceid = 0, $paymentgateway = '')
{
        global $ilance, $myapi, $ilconfig, $phrase;
        
        $ilance->db->query("
                UPDATE " . DB_PREFIX . "subscription_user
                SET cancelled = '1',
                autopayment = '0',
                recurring = '0'
                WHERE user_id = '" . intval($userid) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        
        if ($ilconfig['authnet_enabled'] AND isset($invoiceid) AND $invoiceid > 0 AND !empty($paymentgateway) AND $paymentgateway == 'authnet')
        {
                $ilance->accounting = construct_object('api.accounting');
	
                $ilance->authorizenet = construct_object('api.authorizenet', $ilance->GPC);
                $ilance->authorizenet->error_email = SITE_EMAIL;
                $ilance->authorizenet->timeout = 120;
                
                $subscriptionId = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $invoiceid . "'", "custommessage");
                $data['subscriptionId'] = $subscriptionId;
                
                // #### build our special cancellation recurring subscription xml data
                $xml = $ilance->authorizenet->build_recurring_subscription_xml('cancel', $data);
                $method = 'curl'; // curl or fsockopen can be used
                unset($data);
                
                // #### post and fetch gateway response ################################
                if ($xml != '')
                {
                        $gatewayresponse = $ilance->authorizenet->send_response($method, $xml, 'https://api.authorize.net', '/xml/v1/request.api'); 
                        if ($gatewayresponse != '')
                        {
                                $refId = $resultCode = $code = $text = '';
                                list($refId, $resultCode, $code, $text, $subscriptionId) = $ilance->authorizenet->parse_return($gatewayresponse);
                                
                                if (strtolower($resultCode) == 'ok')
                                {
                                        // #### COMPLETED!!
                                }
                                else
                                {
                                        $ilance->authorizenet->error_out('Warning: Authorize.Net subscription cancellation gateway response: resultcode: ' . $resultCode . ', code: ' . $code . ', text: ' . $text . ', subscriptionId: ' . $subscriptionId);
                                        return false;
                                }
                        }
                        else
                        {
                                $ilance->authorizenet->error_out('Warning: could not communicate with Authorize.Net (no gateway response) to cancel subscription via PHP function: ' . $method . ' in functions.php (try curl or fsockopen)');
                                return false;
                        }
                }
                else
                {
                        $ilance->authorizenet->error_out('Warning: function build_recurring_subscription_xml() could not construct a valid xml response in functions.php to cancel recurring subscription payment at merchant gateway');
                        return false;
                }
        }
        
	$existing = array(
                '{{user}}' => $_SESSION['ilancedata']['user']['username'],
                '{{comment}}' => $ilance->GPC['comment']
        );
	
        $ilance->email = construct_dm_object('email', $ilance);
                
        $ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
        $ilance->email->get('member_cancelled_subscription');		
        $ilance->email->set($existing);
        $ilance->email->send();
        
        $ilance->email->mail = SITE_EMAIL;
        $ilance->email->slng = fetch_site_slng();
        $ilance->email->get('member_cancelled_subscription_admin');		
        $ilance->email->set($existing);
        $ilance->email->send();
        
        return true;
}
/**
* Function to determine if a user's subscription is cancelled based on a supplied user id
*
* @param       string         user id
*
* @return      boolean        Returns true if cancelled, false if not
*/
function is_subscription_cancelled($userid = 0)
{
        global $ilance, $myapi;
        $sql = $ilance->db->query("
                SELECT cancelled
                FROM " . DB_PREFIX . "subscription_user
                WHERE user_id = '".intval($userid)."'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                return intval($res['cancelled']);
        }
	
        return 0;
}
/**
* Function to determine if a subscription plan's permission is setup or not
*
* @param       string         subscription id
*
* @return      boolean        Returns true if ready, false if not
*/
function is_subscription_permissions_ready($subscriptiongroupid = 0)
{
        global $ilance, $ilconfig, $myapi;
        
        // make sure this function supports older versions of ILance
        if ($ilconfig['current_version'] >= '3.1.4')
        {
                $table = DB_PREFIX . "subscription_permissions";
        }
        else
        {
                $table = DB_PREFIX . "subscription_group_titles";
        }
        
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS permissioncount
                FROM $table
                WHERE subscriptiongroupid = '" . intval($subscriptiongroupid) . "'
                ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                if ($res['permissioncount'] > 0)
                {
                        return true;
                }
        }
        
        return false;
}
/**
* Function to add valid subscription permissions into the subscription datastore
* 
* @param       string         access text
* @param       string         access text description
* @param       string         access name
* @param       string         access type
* @param       string         access default value
* @param       boolean        can access permission be removed? (default true)
* @param       boolean        is original framework access? (default true)
*
* @return      boolean        Returns true or false
*/
function add_subscription_permissions($accesstext = '', $accessdescription = '', $accessname = '', $accesstype = '', $value = '', $canremove = 1)
{
        global $ilance, $myapi, $ilconfig;
        
        // make sure this function supports older versions of ILance
        if ($ilconfig['current_version'] >= '3.1.4')
        {
                $table = DB_PREFIX . "subscription_permissions";
        }
        else
        {
                $table = DB_PREFIX . "subscription_group_titles";
        }
        
        $sql = $ilance->db->query("
                SELECT *
                FROM $table
                WHERE accessname = '" . $ilance->db->escape_string($accessname) . "'
                LIMIT 1
                ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                return false;
        }
        else
        {
                // collect existing languages
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        // loop through existing language rows (maybe english, polish, etc)
                        $extraquery = $extraquery2 = '';
                        
                        while ($languages = $ilance->db->fetch_array($sql))
                        {
                                $extraquery  .= 'accesstext_' . mb_strtolower(mb_substr($languages['languagecode'], 0, 3)) . ', accessdescription_' . mb_strtolower(mb_substr($languages['languagecode'], 0, 3)) . ', ';
                                $extraquery2 .= "'" . $ilance->db->escape_string($accesstext) . "', '" . $ilance->db->escape_string($accessdescription) . "',";
                        }
                        
                        $sqlcreate = $ilance->db->query("
                                SELECT subscriptiongroupid, title, description, canremove
                                FROM " . DB_PREFIX . "subscription_group
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlcreate) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sqlcreate))
                                {
                                        // has the admin finished setting up this subscription permissions?
                                        if (is_subscription_permissions_ready($res['subscriptiongroupid']))
                                        {
                                                // create new permission for each subscription groups available
                                                $ilance->db->query("
                                                        INSERT INTO $table
                                                        (id, subscriptiongroupid, accessname, $extraquery accesstype, value, canremove, original, iscustom, visible)
                                                        VALUES(
                                                        NULL,
                                                        '" . $res['subscriptiongroupid'] . "',
                                                        '" . $ilance->db->escape_string($accessname) . "',
                                                        $extraquery2
                                                        '" . $accesstype . "',
                                                        '" . $value . "',
                                                        '" . $canremove . "',
                                                        '1',
                                                        '0',
                                                        '1')
                                                ", 0, null, __FILE__, __LINE__);        
                                        }
                                }
                                
                                return 1;
                        }
                }
        }
}
/**
* Function to determine if a project id being specified is actually a valid auction listing id
* 
* @param       integer        project id
*
* @return      boolean        Returns true or false
*/
function is_valid_project_id($projectid = 0)
{
        global $ilance;
        
        $sql = $ilance->db->query("
                SELECT user_id
                FROM " . DB_PREFIX . "projects
                WHERE project_id = '" . intval($projectid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                return 1;
        }
        
        return 0;
}
/**
* Function to fetch a project's owner user id
* 
* @param       integer        project id
*
* @return      boolean        Returns user id identifier (or zero if cannot be found)
*/
function fetch_project_ownerid($projectid = 0)
{
        global $ilance, $myapi;
        $sql = $ilance->db->query("SELECT user_id FROM " . DB_PREFIX . "projects WHERE project_id = '".intval($projectid)."' LIMIT 1", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                return $res['user_id'];
        }
        return 0;
}
/**
* Function to determine if a particular auction event has sealed bidding enabled
* 
* @param       integer        project id
*
* @return      boolean        Returns true or false
*/
function is_sealed_auction($projectid = 0)
{
        global $ilance;
        
        $sql = $ilance->db->query("
                SELECT bid_details
                FROM " . DB_PREFIX . "projects
                WHERE project_id = '" . intval($projectid) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                if ($res['bid_details'] != 'open')
                {
                        return 1;
                }
        }
        
        return 0;
}
/**
* Function to determine if a particular auction event is by invitation only
* 
* @param       integer        project id
*
* @return      boolean        Returns true or false
*/
function is_inviteonly_auction($projectid = 0)
{
        global $ilance, $myapi;
        $sql = $ilance->db->query("SELECT project_details FROM " . DB_PREFIX . "projects WHERE project_id = '".intval($projectid)."' LIMIT 1", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                if ($res['project_details'] == 'invite_only')
                {
                        return 1;
                }
        }
        return 0;
}
/**
* Function to determine if a transaction id being passed already exists within the invoice and billing system
* 
* @param       string         transaction id
*
* @return      boolean        Returns true or false
*/
function is_duplicate_txn_id($txn_id = '')
{
        global $ilance;
        
        $sql = $ilance->db->query("
                SELECT invoiceid
                FROM " . DB_PREFIX . "invoices
                WHERE custommessage = '" . $ilance->db->escape_string($txn_id) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                return true;
        }
        
        return false;
}
/**
* Function to fetch the buy now order count for a particular product auction event
* 
* @param       integer        project id
*
* @return      integer        Returns count
*/
function fetch_buynow_ordercount($projectid = 0)
{
        global $ilance, $myapi;
        
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS total
                FROM " . DB_PREFIX . "buynow_orders
                WHERE project_id = '" . intval($projectid) . "'
                        AND status != 'cancelled'
        ", 0, null, __FILE__, __LINE__);
        $res = $ilance->db->fetch_array($sql);
        
        return (int)$res['total'];
}
/**
* Function to print out in verbose terms the auction bit (type of auction).  This function now takes service and product into consideration.
*
* @param       integer        project id
*
* @return      string         Returns phrase based on the auction event type
*/
function print_auction_bit($projectid = 0, $filtered_auctiontype = '', $project_details = '', $project_state = '', $buynow_price = '', $buynow_qty = '', $reserve = 0, $cid = 0)
{
	global $ilance, $myapi, $phrase, $show, $ilconfig;
        
        $html = '';
        
        if ($project_state == 'product')
        {
                if ($project_details == 'unique')
                {
                        $html = $phrase['_lowest_unique_bid__single_item'];
                }
                else
                {
                        if ($filtered_auctiontype == 'fixed')
                        {
                                $html = $phrase['_fixed_price_event__multiple_buy_now_option'];
                        }
                        else if ($filtered_auctiontype == 'regular')
                        {
                                $other = '';
                                if ($reserve > 0)
                                {
                                        $other = $phrase['_plus_reserve_price'];
                                }
                                if ($buynow_price AND $buynow_qty > 0)
                                {
                                        $other .= ' ' . $phrase['_plus_buy_now_option'];        
                                }
                                
                                $html = $phrase['_regular_auction__single_item'] . ' ' . $other;
                        }    
                }
        }
        else if ($project_state == 'service')
        {
                $html = $phrase['_reverse_auction'];        
        }
        
        ($apihook = $ilance->api('print_auction_bit_end')) ? eval($apihook) : false;
        
	return $html;
}
/**
* Function to determine the escrow fee (including tax if any) for a seller based on a userid and amount
*
* @param       integer        user id
* @param       integer        amount
*
* @return      string         Returns formatted fee including applicable taxes
*/
function fetch_merchant_escrow_fee_plus_tax($userid = 0, $amount = 0)
{
        global $ilance, $myapi, $ilconfig;
        
        $fee = 0;
        if ($ilconfig['escrowsystem_escrowcommissionfees'])
        {
                $ilance->tax = construct_object('api.tax');
                                                    
                // escrow commission fees to auction owner enabled
                if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
                {
                        // fixed escrow cost to merchant
                        $fee = $ilconfig['escrowsystem_merchantfixedprice'];
                }
                else
                {
                        if ($ilconfig['escrowsystem_merchantpercentrate'] > 0)
                        {
                                // percentage rate of total winning bid amount
                                // which would be the same as the amount being forwarded into escrow
                                $fee = ($amount * $ilconfig['escrowsystem_merchantpercentrate'] / 100);
                        }
                }
                if ($fee > 0)
                {
                        $taxamount = 0;
                        if ($ilance->tax->is_taxable(intval($userid), 'commission'))
                        {
                                // fetch tax amount to charge for this invoice type
                                $taxamount = $ilance->tax->fetch_amount(intval($userid), $fee, 'commission', 0);
                        }
                        
                        // exact amount to charge merchant
                        $fee = ($fee + $taxamount);
                }
        }
        
        return sprintf("%01.2f", $fee);
}
/**
* Function to determine the escrow fee (including tax if any) for a product buyer based on a userid and amount
*
* @param       integer        user id
* @param       integer        amount
*
* @return      string         Returns formatted fee including applicable taxes
*/
function fetch_product_bidder_escrow_fee_plus_tax($userid = 0, $amount = 0)
{
    global $ilance, $myapi, $ilconfig;
    
    $fee = 0;
    if ($ilconfig['escrowsystem_escrowcommissionfees'])
    {
                $ilance->tax = construct_object('api.tax');
                
                // escrow commission fees to auction owner enabled
                if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
                {
                        // fixed escrow cost to provider for release of funds
                        $fee = $ilconfig['escrowsystem_bidderfixedprice'];
                }
                else
                {
                        if ($ilconfig['escrowsystem_bidderpercentrate'] > 0)
                        {
                                // percentage rate of total winning bid amount
                                // which would be the same as the amount being forwarded into escrow
                                $fee = ($amount * $ilconfig['escrowsystem_bidderpercentrate'] / 100);
                        }
                }
                
                if ($fee > 0)
                {
                        $taxamount = 0;
                        if ($ilance->tax->is_taxable(intval($userid), 'commission'))
                        {
                                // fetch tax amount to charge for this invoice type
                                $taxamount = $ilance->tax->fetch_amount(intval($userid), $fee, 'commission', 0);
                        }
                        
                        // exact amount to charge provider for release of funds
                        $fee = ($fee + $taxamount);
                }
    }
    
    return sprintf("%01.2f", $fee);
}
/**
* Function to determine the escrow fee amount for a seller based on a particular amount
*
* @param       integer        amount
*
* @return      string         Returns formatted fee amount
*/
function fetch_merchant_escrow_fee($amount = 0)
{
        global $ilance, $myapi, $ilconfig;
        $fee = 0;
        if ($ilconfig['escrowsystem_escrowcommissionfees'])
        {
                // escrow commission fees to auction owner enabled
                if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
                {
                        // fixed escrow cost to merchant
                        $fee = $ilconfig['escrowsystem_merchantfixedprice'];
                }
                else
                {
                        if ($ilconfig['escrowsystem_merchantpercentrate'] > 0)
                        {
                                // percentage rate of total winning bid amount
                                // which would be the same as the amount being forwarded into escrow
                                $fee = ($amount * $ilconfig['escrowsystem_merchantpercentrate'] / 100);
                        }
                }       
        }
        
        return sprintf("%01.2f", $fee);
}
/**
* Function to determine the escrow fee amount for a product buyer based on a particular userid and amount
*
* @param       integer        user id
* @param       integer        amount
*
* @return      string         Returns formatted fee amount
*/
function fetch_product_bidder_escrow_fee($userid = 0, $amount = 0)
{
        global $ilance, $myapi, $ilconfig;
        
        $fee = 0;
        if ($ilconfig['escrowsystem_escrowcommissionfees'])
        {
                // escrow commission fees to auction owner enabled
                if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
                {
                        // fixed escrow cost to provider for release of funds
                        $fee = $ilconfig['escrowsystem_bidderfixedprice'];
                }
                else
                {
                        if ($ilconfig['escrowsystem_bidderpercentrate'] > 0)
                        {
                                // percentage rate of total winning bid amount
                                // which would be the same as the amount being forwarded into escrow
                                $fee = ($amount * $ilconfig['escrowsystem_bidderpercentrate'] / 100);
                        }
                }        
        }
        
        return sprintf("%01.2f", $fee);
}
/**
* Function to determine the escrow fee amount for a service buyer based on a particular amount
*
* @param       integer        amount
*
* @return      string         Returns formatted fee amount
*/
function fetch_service_buyer_escrow_fee($amount = 0)
{
    global $ilance, $myapi, $ilconfig;
    
    $fee = 0;
    if ($ilconfig['escrowsystem_escrowcommissionfees'])
    {
                // escrow commission fees to auction owner enabled
                if ($ilconfig['escrowsystem_servicebuyerfixedprice'] > 0)
                {
                        // fixed escrow cost to merchant
                        $fee = $ilconfig['escrowsystem_servicebuyerfixedprice'];
                }
                else
                {
                        if ($ilconfig['escrowsystem_servicebuyerpercentrate'] > 0)
                        {
                                // percentage rate of total winning bid amount
                                // which would be the same as the amount being forwarded into escrow
                                $fee = ($amount * $ilconfig['escrowsystem_servicebuyerpercentrate'] / 100);
                        }
                }       
    }
    
    return sprintf("%01.2f", $fee);
}
/**
* Function to compile and process ascending sorting based on a keywords array
*
* @param       array          first tag array
* @param       array          second tag array
*
* @return      string         tag count
*/
function cloud_tags_asort($tag1, $tag2)
{
        if ($tag1['tag_count'] == $tag2['tag_count'])
        {
                return 0;
        }
        
        return ($tag1['tag_count'] < $tag2['tag_count']) ? -1 : 1;
}
/**
* Function to compile and process alpha sorting based on a keywords array
*
* @param       array          first tag array
* @param       array          second tag array
*
* @return      string         Returns HTML formatted top search keywords cloud
*/
function cloud_tags_alphasort($tag1, $tag2)
{
        if ($tag1['tag_name'] == $tag2['tag_name'])
        {
                return 0;
        }
        
        return ($tag1['tag_name'] < $tag2['tag_name']) ? -1 : 1;
}
/**
* Function to compile and process an array with top keywords for presentation
*
* @return      string         Returns HTML formatted top search keywords cloud
*/
function process_cloud_tags($tags)
{
        $tag_sizes = 7;
        usort($tags, 'cloud_tags_asort');
        if (count($tags) > 0)
        {
                $total_tags = count($tags);
                $min_tags = $total_tags / $tag_sizes;
        
                $bucket_count = 1;
                $bucket_items = $tags_set = 0;
                foreach ($tags as $key => $tag)
                {
                        $tag_count = $tag['tag_count'];
                        
                        if (($bucket_items >= $min_tags) and $last_count != $tag_count AND $bucket_count < $tag_sizes)
                        {
                                $bucket_count++;
                                $bucket_items = 0;
                                $remaining_tags = $total_tags - $tags_set;
                                $min_tags = $remaining_tags / $bucket_count;
                        }
                        
                        $tags[$key]['tag_class'] = 'tag' . $bucket_count;
                        $bucket_items++;
                        $tags_set++;
                        $last_count = $tag_count;
                }
                
                usort($tags, 'cloud_tags_alphasort');
        }
        
        return $tags;
}
/**
* Function to print a HTML formatted top search keywords tag cloud with the most searched in various font sizes and attributes
*
* @return      string         Returns HTML formatted top search keywords cloud
*/
function print_tag_cloud()
{
        global $ilance, $myapi, $ilconfig, $ilpage, $show;
        
	if ($ilconfig['enablepopulartags'] == false)
	{
		return;
	}
        $badwords = explode(', ', $ilconfig['globalfilters_vulgarpostfilterlist']);
        $tags = array();
	$counter = 0;
        $html = '';
        
        $sql = $ilance->db->query("
                SELECT keyword AS tag_name, count AS tag_count, searchmode
                FROM " . DB_PREFIX . "search
                WHERE count > " . $ilconfig['populartagcount'] . "
                ORDER BY count
                LIMIT " . $ilconfig['populartaglimit'] . "
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_assoc($sql))
                {
                        $tags[] = $res;
                }
        }
        
        $newtags = process_cloud_tags($tags);
        if (!empty($newtags))
        {
                foreach ($newtags as $array)
                {
                        $counter++;
                        if ($counter < 30)
                        {
                                if (!in_array(stripslashes(mb_strtolower($array['tag_name'])), $badwords))
                                {
                                        $html .= '<a href="' . $ilpage['search'] . '?q=' . urlencode(stripslashes(html_entity_decode($array['tag_name']))) . '&amp;mode=' . urlencode($array['searchmode']) . '" class="' . $array['tag_class'] . '">' . mb_strtolower(stripslashes($array['tag_name'])) . '</a> &nbsp; ';
                                }
                        }
                }
		
                $show['tagcloud'] = 1;
        }
        else
        {
                $show['tagcloud'] = 0;
        }
        
        return $html;
}
/**
* Function to print an auction event status phrase
*
* @param       string         status type (draft, open, closed, expired, delisted, wait_approval, approval_accepted, frozen, finished or archived)
*
* @return      string         Returns auction event status phrase
*/
function print_auction_status($status = '')
{
        global $phrase;
        
        if ($status == 'wait_approval')
        {
                $text = $phrase['_waiting_approval'];        
        }
	else if ($status == 'expired')
        {
		$text = $phrase['_ended'];
	}
        else
        {
                $text = $phrase["_$status"];
        }
        
        return $text;
}
/**
* Function to update a specific referred user (from a rid referral) with a particular action being taken
*
* @param       string         referral action type (postauction, awardauction, fvf, ins, lanceads, portfolio, credential, enhancement or subscription)
* @param       integer        user id
* @param       boolean        don't dispatch email on completion (default false)
*
* @return      nothing
*/
function update_referral_action($type = '', $userid = 0, $dontsendemail = 0)
{
        global $ilance, $myapi, $phrase, $ilconfig, $show;
        
        if ($ilconfig['referalsystem_active'])
        {
                $sql = $ilance->db->query("
                        SELECT referred_by
                        FROM " . DB_PREFIX . "referral_data
                        WHERE user_id = '".intval($userid)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        $sql2 = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '".$res['referred_by']."'
                                        AND status = 'active'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql2) > 0)
                        {
                                $res2 = $ilance->db->fetch_array($sql2);                
                                $username = fetch_user('username', $userid);
                                switch ($type)
                                {
                                        // #### POST AUCTION TRACKER ###############################
                                        case 'postauction':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "referral_data
                                                        SET postauction = postauction + 1
                                                        WHERE user_id = '".intval($userid)."'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $event = 'The referred user ('.$username.') (who originally referred by '.$res2['username'].') posted a valid auction.';
                                                break;
                                        }                                    
                                        // #### AWARD AUCTION TRACKER ##############################
                                        case 'awardauction':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "referral_data
                                                        SET awardauction = awardauction + 1
                                                        WHERE user_id = '".intval($userid)."'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $event = 'The referred user ('.$username.') (who originally referred by '.$res2['username'].') awarded a valid bid for their auction.';
                                                break;
                                        }                                    
                                        // #### FINAL VALUE FEE TRACKER ############################
                                        case 'fvf':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "referral_data
                                                        SET payfvf = payfvf + 1
                                                        WHERE user_id = '".intval($userid)."'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $event = 'The referred user ('.$username.') (who originally referred by '.$res2['username'].') paid a final value commission fee.';
                                                break;
                                        }                                    
                                        // #### INSERTION FEE TRACKER ##############################
                                        case 'ins':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "referral_data
                                                        SET payins = payins + 1
                                                        WHERE user_id = '".intval($userid)."'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $event = 'The referred user ('.$username.') (who originally referred by '.$res2['username'].') paid a listing insertion fee.';
                                                break;
                                        }                                    
                                        // #### LANCEADS TRACKER ###################################
                                        case 'lanceads':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "referral_data
                                                        SET paylanceads = paylanceads + 1
                                                        WHERE user_id = '".intval($userid)."'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $event = 'The referred user ('.$username.') (who originally referred by '.$res2['username'].') paid to activate an advertising campaign.';
                                                break;
                                        }                                    
                                        // #### FEATURE PORTFOLIO TRACKER ##########################
                                        case 'portfolio':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "referral_data
                                                        SET payportfolio = payportfolio + 1
                                                        WHERE user_id = '".intval($userid)."'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $event = 'The referred user ('.$username.') (who originally referred by '.$res2['username'].') paid for featured portfolio status.';
                                                break;
                                        }
                                        // #### CREDENTIAL VERIFICATION TRACKER ####################
                                        case 'credential':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "referral_data
                                                        SET paycredential = paycredential + 1
                                                        WHERE user_id = '".intval($userid)."'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $event = 'The referred user ('.$username.') (who originally referred by '.$res2['username'].') paid to have credentials verified.';
                                                break;
                                        }                                    
                                        // #### AUCTION ENHANCEMENTS TRACKER #######################
                                        case 'enhancements':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "referral_data
                                                        SET payenhancements = payenhancements + 1
                                                        WHERE user_id = '".intval($userid)."'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $event = 'The referred user ('.$username.') (who originally referred by '.$res2['username'].') paid listing enhancements for their auction.';
                                                break;
                                        }                                    
                                        // #### SUBSCRIPTION TRACKER ###############################
                                        case 'subscription':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "referral_data
                                                        SET paysubscription = paysubscription + 1
                                                        WHERE user_id = '".intval($userid)."'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                $event = 'The referred user ('.$username.') (who originally referred by '.$res2['username'].') paid for a valid subscription.';
                                                break;
                                        }
                                }
                                
                                // are we constructing new auction from an API call?
                                if ($dontsendemail == 0)
                                {
                                        // no api being used, proceed to dispatching email
                                        $ilance->email = construct_dm_object('email', $ilance);
                                        
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        
                                        $ilance->email->get('referral_payout_pending_admin');		
                                        $ilance->email->set(array(
                                                '{{username}}' => fetch_user('username', intval($userid)),
                                                '{{main_referral}}' => $res2['username'],
                                                '{{main_referral_id}}' => $res2['user_id'],
                                                '{{event}}' => $event,
                                        ));
                                        
                                        $ilance->email->send();
                                }
                        }
                }
        }
}
/**
* Function to determine if a question is multiple choice
*
* @param       integer        question id
* @param       string         mode (register, project, product or profile)
*
* @return      boolean        Returns true or false
*/
function is_question_multiplechoice($qid = 0, $mode = '')
{
        global $ilance;
        
        if (empty($mode) OR $qid == 0)
        {
                return 0;
        }
        
        switch ($mode)
        {
                case 'register':
                {
                        $table = DB_PREFIX . 'register_questions';
                        break;
                }        
                case 'project':
                {
                        $table = DB_PREFIX . 'project_questions';
                        break;
                }        
                case 'product':
                {
                        $table = DB_PREFIX . 'product_questions';
                        break;
                }        
                case 'profile':
                {
                        $table = DB_PREFIX . 'profile_questions';
                        break;
                }
        }
        
        $sql = $ilance->db->query("
                SELECT inputtype
                FROM $table
                WHERE questionid = '" . intval($qid) . "'
        ", 0, null, __FILE__, __LINE__);    
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                if ($res['inputtype'] == 'multiplechoice')
                {
                        return 1;
                }
        }
        
        return 0;
}
/**
* Function to fetch the user account balance including income reported and income spent
*
* @param       integer        user id
*
* @return      array          Returns array with user account details
*/
function fetch_user_balance($userid = 0)
{
        global $ilance;
        
        $sql = $ilance->db->query("
                SELECT account_number, available_balance, total_balance, income_reported, income_spent
                FROM " . DB_PREFIX . "users
                WHERE user_id = '".intval($userid)."'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                return $res;
        }
        
        return array();
}
/**
* Function to calculate the sum of the total users logged into the marketplace
*
* @param       integer        user id
*
* @return      string         Returns total members online count in phrase format (ie: 3 members online)
*/
function members_online()
{
        global $ilance, $myapi, $phrase;
        
        $sql = $ilance->db->query("
                SELECT token
                FROM " . DB_PREFIX . "sessions
                GROUP BY token
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                if ($ilance->db->num_rows($sql) == 1)
                {
                        return (int)$ilance->db->num_rows($sql) . ' ' . $phrase['_member_online'];
                }
                else
                {
                        return (int)$ilance->db->num_rows($sql) . ' ' . $phrase['_members_online'];
                }
        }
        
        return $phrase['_one_member_online'];
}
/**
* Function to determine if a particular user has placed a bid on a specific auction id
*
* @param       integer        project id
* @param       integer        user id
*
* @return      bool           Returns true for yes, false for no
*/
function is_bid_placed($projectid = 0, $userid = 0)
{
	global $ilance;
        
	$sql = $ilance->db->query("
		SELECT user_id
                FROM " . DB_PREFIX . "project_bids
		WHERE user_id = '".intval($userid)."'
		    AND project_id = '".intval($projectid)."'
		    AND bidstate != 'retracted'
		    AND bidstatus != 'declined'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		return true;
	}
        
	return false;
}
/**
* Function to print the countries pulldown menu using the dynamic javascript method of pre-populating states as well.
*
* @param       string         field name of the country pulldown menu
* @param       string         field name of the state pulldown menu
* @param       string         field name of the city pulldown menu
* @param       bool           do javascript (default false)
* @param       string         javascript country checkbox fieldname
* @param       string         javascript state checkbox fieldname
* @param       string         javascript city checkbox fieldname
* @param       string         form id
*
* @return      string         Returns the Country pulldown menu
*/
function print_js_countries_pulldown($fieldname = '', $statefieldname = '', $cityfieldname = '', $dojs = false, $jscountryname = '', $jsstatename = '', $jscityname = '', $formid = '')
{
        global $ilance, $phrase;
        $javascript = '';
        if ($dojs)
        {
                $javascript = 'javascript: document.' . $formid . '.' . $jscountryname . '.checked=true; document.' . $formid . '.' . $jsstatename . '.checked=true; document.' . $formid . '.' . $jscityname . '.checked=true;';
        }
        //$html = '<select name="' . $fieldname . '" id="' . $fieldname . '" onchange="populate_states_pulldown(document.' . $formid . '.' . $fieldname . ', document.' . $formid . '.' . $statefieldname . ', \'\'); populate_cities_pulldown(document.' . $formid . '.' . $statefieldname . ', document.' . $formid . '.' . $cityfieldname . ', \'\'); ' . $javascript . '" style="font-family: verdana"><option></option></select>';
        $html = '<select name="' . $fieldname . '" id="' . $fieldname . '" onchange="populate_states_pulldown(document.' . $formid . '.' . $fieldname . ', document.' . $formid . '.' . $statefieldname . ', \'\'); ' . $javascript . '" style="font-family: verdana"><option></option></select>';
        
        return $html;
}
/**
* Function to print the states pulldown menu using the dynamic javascript.
*
* @param       string         field name of the state pulldown menu
* @param       string         field name of the city pulldown menu
* @param       bool           do javascript (default false)
* @param       string         javascript country checkbox fieldname
* @param       string         javascript state checkbox fieldname
* @param       string         javascript city checkbox fieldname
* @param       string         form id
*
* @return      string         Returns the States/Provinces pulldown menu
*/
function print_js_states_pulldown($fieldname = '', $cityfieldname = '', $dojs = false, $jscountryname = '', $jsstatename = '', $jscityname = '', $formid = '')
{
        global $ilance, $phrase;
        
        $javascript = '';
        if ($dojs)
        {
                $javascript = 'onchange="javascript: document.' . $formid . '.' . $jsstatename . '.checked=true; document.' . $formid . '.' . $jscountryname . '.checked=true; document.' . $formid . '.' . $jscityname . '.checked=true;"';
        }
        
        //$html = '<select name="' . $fieldname . '" id="' . $fieldname . '" onchange="populate_cities_pulldown(document.' . $formid . '.' . $fieldname . ', document.' . $formid . '.' . $cityfieldname . ', \'\'); ' . $javascript . '" style="font-family: verdana"><option></option></select>';
        $html = '<select name="' . $fieldname . '" id="' . $fieldname . '" ' . $javascript . ' style="font-family: verdana"><option></option></select>';
        
        return $html;
}
/**
* Function to print the states pulldown menu using the dynamic javascript.
*
* @param       string         field name of the city pulldown menu
* @param       bool           do javascript (default false)
* @param       string         javascript country checkbox fieldname
* @param       string         javascript state checkbox fieldname
* @param       string         javascript city checkbox fieldname
* @param       string         form id
*
* @return      string         Returns the Cities within a selected province pulldown menu
*/
function print_js_cities_pulldown($fieldname = '', $dojs = false, $jscountryname = '', $jsstatename = '', $jscityname = '', $formid = '')
{
        global $ilance, $phrase;
        
        $javascript = '';
        if ($dojs)
        {
                $javascript = 'onchange="javascript: document.' . $formid . '.' . $jsstatename . '.checked=true; document.' . $formid . '.' . $jscountryname . '.checked=true; document.' . $formid . '.' . $jscityname . '.checked=true;"';
        }
        
        $html = '<select name="' . $fieldname . '" id="' . $fieldname . '" ' . $javascript . ' style="font-family: verdana; width:185px"><option></option></select>';
        
        return $html;
}
/**
* Function to print the ending javascript for the dynamic country and states pulldown menu.
*
* @param       string         field name of the country pulldown menu
* @param       string         field name of the state pulldown menu
* @param       string         javascript selected country name
* @param       string         javascript selected state name
* @param       string         form id
* @param       string         focus field
*
* @return      string         Returns the Javascript
*/
function print_js_pulldown_end($countryfieldname = '', $statefieldname = '', $cityfieldname = '', $selectedcountry = '', $selectedstate = '', $selectedcity = '', $formid = '', $focus = '')
{
        global $ilance, $ilconfig, $js_end;
        
        $js_end .= "<script type=\"text/javascript\">\n<!--\n";
        $js_end .= "populate_countries_pulldown(document.$formid.$countryfieldname, document.$formid.$countryfieldname, '" . addslashes($selectedcountry) . "');\n";
        $js_end .= "populate_states_pulldown(document.$formid.$countryfieldname, document.$formid.$statefieldname, '" . addslashes($selectedstate) . "');\n";
        //$js_end .= "populate_cities_pulldown(document.$formid.$statefieldname, document.$formid.$cityfieldname, '" . addslashes($selectedcity) . "');\n";
        if (isset($focus) AND !empty($focus))
        {
                $js_end .= "document.$formid.$focus.focus();\n";
        }
        $js_end .= "//-->\n</script>";
        
        return $js_end;
}
/**
* Function to print the ending javascript for the dynamic country and states pulldown menu.
*
* @param       integer        user id
* @param       integer        project id
* @param       string         attachment type (default is 'project')
*
* @return      string         Returns the file list
*/
function fetch_inline_attachment_filelist($userid = 0, $projectid = 0, $attachtype = 'project', $printimage = false)
{
          
        global $ilance, $ilconfig, $ilpage, $phrase;
		//herakle kkk
						if($_SESSION['ilancedata']['user']['isstaff'] == '1')
						$pro_val_id = $projectid;
						else
						$pro_val_id = intval($projectid);
        
        $html = '';
        $sql = $ilance->db->query("
                SELECT attachid, visible, filename, filesize, filehash
                FROM " . DB_PREFIX . "attachment
                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                        AND project_id = '" . $pro_val_id . "'
                        " . (($userid > 0) ? "AND user_id = '" . intval($userid) . "'" : '') . "
        ");
        while ($res = $ilance->db->fetch_array($sql))
        {
                $moderated = '';
                if ($res['visible'] == 0)
                {
                        $moderated = '[' . $phrase['_review_in_progress'] . ']';
                        $attachment_link = $res['filename'];
                }
                else
                {
                        if ($printimage)
                        {
                                $attachment_link = '<img src="' . HTTP_SERVER . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res['filehash'] . '" border="0" alt="" />';
                        }
                        else
                        {
                                $attachment_link = '<a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a>';
                        }
                }
                
                $html .= '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" border="0" alt="' . $res['filename'] . '" /> ' . $attachment_link . ' (' . $res['filesize'] . ' ' . $phrase['_bytes'] . ') ' . $moderated . '</div>';
        }
        
        return $html;
}
/**
* Function to print the ending javascript for the dynamic country and states pulldown menu.
*
* @param       integer        user id
* @param       integer        video width (default 290px)
* @param       integer        video height (default 240px)
*
* @return      string         Returns the profile video
*/
function print_profile_video($userid = 0, $videowidth = '320', $videoheight = '240')
{
        global $ilance, $show, $ilconfig, $phrase;
        
        $uniqueid = rand(1, 9999);
        $html = '';
        
        $profilevideourl = fetch_user('profilevideourl', $userid);
        $profilevideourl = parse_youtube_video_url($profilevideourl);
        if (!empty($profilevideourl))
        {
                $show['profilevideo'] = true;
                
                $html = '<div id="videoapplet-' . $uniqueid . '"></div>
<script type="text/javascript">
<!--
var fo = new FlashObject("' . $profilevideourl . '", "videoapplet-' . $uniqueid . '", "' . $videowidth . '", "' . $videoheight . '", "8,0,0,0", "#ffffff");
fo.addParam("movie", "' . $profilevideourl . '");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("swLiveConnect", "true");
fo.addParam("menu", "false");
fo.addParam("wmode", "transparent");
fo.write("videoapplet-' . $uniqueid . '");
//-->
</script>';
        }
        else
        {
                $show['profilevideo'] = false;
        }
        
        return $html;
}
/**
* Function to print the ending javascript for the dynamic country and states pulldown menu.
*
* @param       integer        user id
* @param       integer        video width (default 290px)
* @param       integer        video height (default 240px)
* @param       string         additional custom script code
*
* @return      string         Returns the profile video
*/
function print_listing_video($projectid = 0, $videowidth = '290', $videoheight = '240', $scriptextra = '')
{
        global $ilance, $show, $ilconfig, $phrase;
        
        $uniqueid = rand(1, 9999);
        $html = '';
        
        $videourl = fetch_auction('description_videourl', $projectid);
        $videourl = parse_youtube_video_url($videourl);
        if (!empty($videourl))
        {
                $show['videodescription'] = true;
                
                $html = '<div id="videoapplet-description"></div>
<script type="text/javascript">
<!--
var fo = new FlashObject("' . $videourl . '", "videoapplet-description", "' . $videowidth . '", "' . $videoheight . '", "8,0,0,0", "#ffffff");
fo.addParam("movie", "' . $videourl . '");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("swLiveConnect", "true");
fo.addParam("menu", "false");
fo.addParam("wmode", "transparent");
' . $scriptextra . '
//-->
</script>';
        }
        else
        {
                $show['videodescription'] = false;
        }
        
        return $html;
}
        
/**
* Function to respond as true or false based on the supplied category being the last category (leaf) in the category tree.
*
* @param       integer        category id
*
* @return      string         Returns true or false
*/
function is_last_category($cid = 0)
{
        global $ilance;
        
        $sql = $ilance->db->query("
                SELECT cid
                FROM " . DB_PREFIX . "categories
                WHERE parentid = '" . intval($cid) . "'
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                return false;
        }
        else
        {
                return true;
        }
}
/**
* Function to respond as true or false based on the supplied category being the last category (leaf) in the category tree.
*
* @param       integer        category id
*
* @return      string         Returns true or false
*/
function is_postable_category($cid = 0)
{
        global $ilance;
        
        $sql = $ilance->db->query("
                SELECT canpost
                FROM " . DB_PREFIX . "categories
                WHERE cid = '" . intval($cid) . "'
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
		$res = $ilance->db->fetch_array($sql);
                return $res['canpost'];
        }
	
	return false;
}
/**
* Fetches the flash gallery xml config to be processed by the flash gallery applet
*
* @param        string       configuration type to pre-load (recentlyviewed, portfolio, favoriteseller)
*
* @return	string
*/
function fetch_flash_gallery_xml_items($mode = 'recentlyviewed', $userid = 0)
{
        global $ilance, $ilconfig, $phrase, $ilpage;
        $ilance->auction = construct_object('api.auction');
        $xml = '';
        
        switch ($mode)
        {
                case 'portfolio':
                {
                        //$sql = $ilance->db->query("");
                        break;
                }        
                case 'favoriteseller':
                {
                        $sql = $ilance->db->query("
                                SELECT p.user_id, p.project_id, p.project_title, p.currentprice, p.currencyid, a.attachid, a.filehash
                                FROM " . DB_PREFIX . "projects p
                                LEFT JOIN " . DB_PREFIX . "attachment a ON p.project_id = a.project_id
                                WHERE p.visible = '1'
                                        AND a.visible = '1'
                                        AND p.user_id = '" . intval($userid) . "'
                                        AND p.status = 'open'
                                        AND a.attachtype = 'itemphoto'
                                        " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                LIMIT 20
                        ", 0, null, __FILE__, __LINE__);
                        break;
                }
                case 'recentlyviewed':
                {
                        if (!empty($_COOKIE[COOKIE_PREFIX . 'productauctions']))
                        {
                                $productsarr = explode('|', $_COOKIE[COOKIE_PREFIX . 'productauctions']);
                                for ($i = 0; $i < count($productsarr); $i++)
                                {
                                        if (isset($pcookiesql))
                                        {
                                                if (count($productsarr) == $i)
                                                {
                                                        $pcookiesql .= " OR p.project_id = '" . intval($productsarr[$i]) . "'  ";
                                                }
                                                else
                                                {
                                                        $pcookiesql .= " OR p.project_id = '" . intval($productsarr[$i]) . "' ";
                                                }
                                        }
                                        else
                                        {
                                                if (count($productsarr) == 1)
                                                {
                                                        $pcookiesql = " AND p.project_id = '" . intval($productsarr[$i]) . "' ";
                                                }
                                                else
                                                {
                                                        $pcookiesql = " AND p.project_id = '" . intval($productsarr[$i]) . "' ";
                                                }
                                        }
                                }
                                
                                $sql = $ilance->db->query("
                                        SELECT p.user_id, p.project_id, p.project_title, p.currentprice, p.currencyid, a.attachid, a.filehash
                                        FROM " . DB_PREFIX . "projects p
                                        LEFT JOIN " . DB_PREFIX . "attachment a ON p.project_id = a.project_id
                                        WHERE p.visible = '1'
                                                AND a.visible = '1'
                                                $pcookiesql
                                                AND p.status = 'open'
                                                AND a.attachtype = 'itemphoto'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                        LIMIT 20
                                ", 0, null, __FILE__, __LINE__);
                        }
                        break;
                }
        }
        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
        {
$xml .= '<item>
<thumb>' . (($res['attachid'] > 0) ? $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res['filehash'] . '&amp;subcmd=portfolio' : $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif') . '</thumb>
<image>' . (($res['attachid'] > 0) ? $ilpage['attachment'] . '?id=' . $res['filehash'] . '&amp;subcmd=portfolio' : $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif') . '</image>
<price>' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</price>
<title><![CDATA[<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . stripslashes($res['project_title']) . '</a>]]></title>
<product_url><![CDATA[' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . ']]></product_url>
<watch_url><![CDATA[' . HTTP_SERVER . $ilpage['watchlist'] . '?id=' . $res['project_id'] . '&action=watch]]></watch_url>
<stop_watch_url><![CDATA[' . HTTP_SERVER . $ilpage['watchlist'] . '?id=' . $res['project_id'] . '&action=unwatch]]></stop_watch_url>
<time_left><![CDATA[' . $ilance->auction->auction_timeleft($res['project_id'], $ilance->styles->fetch_css_element('panelbackground', $property = 'background', $csstype = 'csscommon'), 'left', $timeintext = 0, $showlivebids = 0, $forcenoflash = 1) . ']]></time_left>
<watch_status>1</watch_status>
</item>
';
        }
        
        return $xml;
}

function auction_time_left_new($result,$showfullformat)
{
global $ilance, $myapi, $ilconfig, $ilconfig, $phrase;

                                $dif = $result['mytime'];
                                $ndays = floor($dif / 86400);
                                $dif -= $ndays * 86400;
                                $nhours = floor($dif / 3600);
                                $dif -= $nhours * 3600;
                                $nminutes = floor($dif / 60);
                                $dif -= $nminutes * 60;
                                $nseconds = $dif;
                                $sign = '+';
                                if ($result['mytime'] < 0)
                                {
                                        $result['mytime'] = - $result['mytime'];
                                        $sign = '-';
                                }
                                if ($sign == '-')
                                {
                                        // expired
                                        $timeleft = $phrase['_ended'];
                                        $expiredauction = 1;
                                }
                                else
                                {
                                        if ($ndays != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $ndays    . $phrase['_d_shortform'] . ', ';	
                                                        $timeleft .= $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];
                                                }
                                                else
                                                {
                                                        $timeleft = $ndays . $phrase['_d_shortform'] . ', ' . $nhours . $phrase['_h_shortform'];
                                                }
                                        }
                                        else if ($nhours != '0')
                                        {
                                                if ($showfullformat)
                                                {
                                                        $timeleft  = $nhours   . $phrase['_h_shortform'] . ', ';
                                                        $timeleft .= $nminutes . $phrase['_m_shortform'] . ', ';
                                                        $timeleft .= $nseconds . $phrase['_s_shortform'];        
                                                }
                                                else
                                                {
                                                        $timeleft = $nhours . $phrase['_h_shortform'] . ', ' . $nminutes . $phrase['_m_shortform'];
                                                }
                                        }
                                        else
                                        {
                                                if ($nminutes != '0')
                                                {
                                                        $timeleft = '<span style="color:#FF0000; font-weight: bold">' . $nminutes . $phrase['_m_shortform'] . ', ' . $nseconds . $phrase['_s_shortform'] . '</span>';
                                                }
                                                else
                                                {
                                                        $timeleft = '<span style="color:#FF0000; font-weight: bold">' . $nseconds . $phrase['_s_shortform'] . '</span>';
                                                }
                                        }
                                }
                          return $timeleft;
}
/**
* Produces the flash based applet picture gallery
*
* @param        string       configuration type to preload
*
* @return	string
*/
function print_flash_gallery($config = 'recentlyviewed', $userid = 0)
{
        global $ilance, $phrase, $ilconfig;
        
        return '';
        $uniqueid = rand(1, 9999);
        
        $html = '
<div id="galleryapplet-' . $uniqueid . '"></div>
<script type="text/javascript">
<!--
var fo = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/gallery.swf", "galleryapplet-' . $uniqueid . '", "600", "200", "8,0,0,0", "#ffffff");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("swLiveConnect", "true");
fo.addParam("flashvars", "config_file=' . urlencode(((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'ajax.php?do=flashgallery&config=' . $config . '&userid=' . $userid . '&s=' . session_id() . '&token=' . TOKEN) . '");
fo.addParam("menu", "false");
fo.write("galleryapplet-' . $uniqueid . '");
//-->
</script>';
        
        return $html;
}
/**
* Fetches the flash gallery xml config to be processed by the flash gallery applet
*
* @param        string       configuration type to pre-load (recentlyviewed, portfolio, favoriteseller)
*
* @return	string
*/
function fetch_flash_stats_xml_items($mode = 'connections')
{
        global $ilance;
        $xml = '';
        
        switch ($mode)
        {
                case 'referrals':
                {
                        $xml = '
<values_count type="number">2</values_count>
<v1_bg_color type="hex">0x006699</v1_bg_color>
<v1_alpha type="number">30</v1_alpha>
<v1_line_thickness type="number">1</v1_line_thickness>
<v1_line_color type="hex">0x000000</v1_line_color>
<v1_value_pointer_color type="hex">0x000000</v1_value_pointer_color>
<v1_value_pointer_alpha type="number">30</v1_value_pointer_alpha>
<v2_bg_color type="hex">0xFF9966</v2_bg_color>
<v2_alpha type="number">30</v2_alpha>
<v2_line_thickness type="number">1</v2_line_thickness>
<v2_line_color type="hex">0x000000</v2_line_color>
<v2_value_pointer_color type="hex">0x000000</v2_value_pointer_color>
<v2_value_pointer_alpha type="number">30</v2_value_pointer_alpha>
</config>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="10000" value2="13321" pin="" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="50000" value2="23321" pin="" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="60000" value2="23321" pin="" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="80000" value2="23321" pin="" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="90000" value2="23321" pin="" />
</items>
</chart>';
                        break;
                }
                case 'connections':
                {
                        $xml = '
<values_count type="number">2</values_count>
<v1_bg_color type="hex">0x999999</v1_bg_color>
<v1_alpha type="number">30</v1_alpha>
<v1_line_thickness type="number">1</v1_line_thickness>
<v1_line_color type="hex">0x000000</v1_line_color>
<v1_value_pointer_color type="hex">0x000000</v1_value_pointer_color>
<v1_value_pointer_alpha type="number">30</v1_value_pointer_alpha>
<v2_bg_color type="hex">0xFF9900</v2_bg_color>
<v2_alpha type="number">30</v2_alpha>
<v2_line_thickness type="number">1</v2_line_thickness>
<v2_line_color type="hex">0x000000</v2_line_color>
<v2_value_pointer_color type="hex">0x000000</v2_value_pointer_color>
<v2_value_pointer_alpha type="number">30</v2_value_pointer_alpha>
</config>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="11223" value2="23321" pin="" />
<item value1="13223" value2="41421" pin="test" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="11223" value2="23321" pin="" />
<item value1="13223" value2="41421" pin="test" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="51223" value2="63321" pin="" />
<item value1="1323" value2="41321" pin="test" />
</items>
</chart>';
                        $guestcount = $membercount = $adminscount = $spidercount = 0;
                        
                        $guestscount = $ilance->db->query_fetch("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "sessions WHERE userid = '0' AND isrobot = '0'");
                        $membercount = $ilance->db->query_fetch("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "sessions WHERE userid > 0 AND isrobot = '0' AND isadmin = '0'");
                        $adminscount = $ilance->db->query_fetch("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "sessions WHERE userid > 0 AND isadmin > 0");
                        $spidercount = $ilance->db->query_fetch("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "sessions WHERE userid = '0' AND isrobot = '1'");
                        
                        $xml .= '<item value="' . (int)$guestscount['count'] . '" label="Guests" />' . "\n";
                        $xml .= '<item value="' . (int)$membercount['count'] . '" label="Members" />' . "\n";
                        $xml .= '<item value="' . (int)$adminscount['count'] . '" label="Admins" />' . "\n";
                        $xml .= '<item value="' . (int)$spidercount['count'] . '" label="Crawlers" />';
                        
                        break;
                }
                case 'totalusers':
                {
                        $xml = '
<values_count type="number">2</values_count>
<v1_bg_color type="hex">0x999999</v1_bg_color>
<v1_alpha type="number">30</v1_alpha>
<v1_line_thickness type="number">1</v1_line_thickness>
<v1_line_color type="hex">0x000000</v1_line_color>
<v1_value_pointer_color type="hex">0x000000</v1_value_pointer_color>
<v1_value_pointer_alpha type="number">30</v1_value_pointer_alpha>
<v2_bg_color type="hex">0xFF9900</v2_bg_color>
<v2_alpha type="number">30</v2_alpha>
<v2_line_thickness type="number">1</v2_line_thickness>
<v2_line_color type="hex">0x000000</v2_line_color>
<v2_value_pointer_color type="hex">0x000000</v2_value_pointer_color>
<v2_value_pointer_alpha type="number">30</v2_value_pointer_alpha>
</config>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="11223" value2="23321" pin="" />
<item value1="13223" value2="41421" pin="test" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="11223" value2="23321" pin="" />
<item value1="13223" value2="41421" pin="test" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="51223" value2="63321" pin="" />
<item value1="1323" value2="41321" pin="test" />
</items>
</chart>';
                        
                        //print '<item value1="" value2=""  value3="" value4="" pin="" />\n";
                        
                        break;
                }
        }
        
        return $xml;
}
/**
* Produces the flash based applet stats component
*
* @param        string       configuration type to preload
* @param        string       specific flash applet to load
*
* @return	string
*/
function print_flash_stats($config = 'connections', $applet = 'stats')
{
        return '';
        $uniqueid = rand(1, 9999);
        
        $html = '
<div id="' . $applet . 'applet-' . $uniqueid . '"></div>
<script type="text/javascript">
<!--
var fo = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/' . $applet . '.swf", "' . $applet . 'applet-' . $uniqueid . '", "100%", "260", "9", "#ffffff");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("swLiveConnect", "true");
fo.addParam("flashvars", "config_file=' . urlencode(((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'ajax.php?do=' . $applet . '&config=' . $config . '&s=' . session_id() . '&token=' . TOKEN) . '");
fo.addParam("menu", "false");
fo.write("' . $applet . 'applet-' . $uniqueid . '");
//-->
</script>';
        
        return $html;
}
/**
* Produces the phrase enabled or disabled based on a supplied boolean value
*
* @return	string
*/
function print_boolean($value)
{
        global $phrase;
        
        return ($value == 1 ? $phrase['_enabled'] : $phrase['_disabled']);
}
/**
* Fetches the IP address of the current visitor
*
* @return	string
*/
function fetch_ip_address()
{
        return $_SERVER['REMOTE_ADDR'];
}
/**
* Fetches a proxy IP address of visitor, will use regular ip if proxy cannot be detected.
*
* @return	string
*/
function fetch_proxy_ip_address()
{
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CLIENT_IP']))
        {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $match))
        {
                foreach ($match[0] AS $ipaddress)
                {
                        if (!preg_match("#^(10|172\.16|192\.168)\.#", $ipaddress))
                        {
                                $ip = $ipaddress;
                                break;
                        }
                }
        }
        else if (isset($_SERVER['HTTP_FROM']))
        {
                $ip = $_SERVER['HTTP_FROM'];
        }
        return $ip;
}
/**
* Function to init server overload checkup on Linux/Unix machines
*
* @return	boolean     returns true or false if server is overloaded
*/
function init_server_overload_checkup()
{
        global $ilconfig;
        
        // start server too busy
        $serveroverloaded = false;
        if (PHP_OS == 'Linux' AND isset($ilconfig['serveroverloadlimit']) AND $ilconfig['serveroverloadlimit'] > 0 AND @file_exists('/proc/loadavg') AND $loadaverage = @file_get_contents('/proc/loadavg'))
        {
                $avg = explode(' ', $loadaverage);
                if (trim($avg[0]) > $ilconfig['serveroverloadlimit'])
                {
                        $serveroverloaded = true;
                }
        }
        
        return $serveroverloaded;
}
/**
* Function to determine if the current user is a search engine or real user based on the crawlers.xml robot file
*
* @return	boolean     returns true or false if server is overloaded
*/
function is_search_crawler()
{
        global $show;
        
        $ilance->xml = construct_object('api.xml');
                
        $xml = array();
        
        $handle = opendir(DIR_XML);
        while (($file = readdir($handle)) !== false)
        {
                if (!preg_match('#^crawlers.xml$#i', $file, $matches))
                {
                        continue;
                }
                $xml = $ilance->xml->construct_xml_array('UTF-8', 1, $file);
        }
        ksort($xml);
	if (is_array($xml['crawler']))
	{
		foreach ($xml['crawler'] AS $crawler)
		{
			if (defined('USERAGENT') AND USERAGENT != '' AND preg_match("#" . preg_quote($crawler['agent'], '#') . "#si", USERAGENT))
			{
                                $show['searchenginename'] = $crawler['title'];
				return true;
			}
		}
        }
    
        unset($handle, $xml);
    
        return false;
}
/**
* Function to fetch the title of the crawler found within the robot file
*
* @return	string     Returns name of connected crawler
*/
function fetch_search_crawler_title($agent)
{
        $ilance->xml = construct_object('api.xml');
                
        $xml = array();
        
        $handle = opendir(DIR_XML);
        while (($file = readdir($handle)) !== false)
        {
                if (!preg_match('#^crawlers.xml$#i', $file, $matches))
                {
                        continue;
                }
                $xml = $ilance->xml->construct_xml_array('UTF-8', 1, $file);
        }
        ksort($xml);
	if (is_array($xml['crawler']) AND isset($agent) AND $agent != '')
	{
		foreach ($xml['crawler'] AS $crawler)
		{
			if (preg_match("#" . preg_quote($crawler['agent'], '#') . "#si", $agent))
			{
				return $crawler['title'];
			}
		}
        }
    
        unset($handle, $xml);
    
        return 'Crawler';
}
/**
* Function to fetch the entire list of pre-defined pages to breadcrumb titles.  For example, rfp.php would display RFP, PMB would display Private Message, etc.
*
* @return	nothing
*/
function fetch_breadcrumb_titles()
{
	global $ilance, $phrase, $ilconfig;
	
	// important breadcrumb titles
	// this list should be updated with the list in class.ilance.inc.php where $ilpage = array( begins..
	$ilcrumbs = array(
		'invoicepayment' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_invoicing'],
		'login' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_login'],
		'payment' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_payments'],
		'attachment' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_attachment'],
		'buying' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_buying'],
		'rfp' . $ilconfig['globalsecurity_extensionmime'] => 'RFP',
		'pmb' . $ilconfig['globalsecurity_extensionmime'] => 'PMB',
		'feedback' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_feedback'],
		'members' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_members'],
		'portfolio' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_portfolios'],
		'merch' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_products'],
		'main' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_main'],
		'watchlist' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_watchlist'],
		'upload' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_upload'],
		'preferences' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_preferences'],
		'subscription' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_subscription'],
		'accounting' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_accounting'],
		'messages' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_messages'],
		'notify' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_notify'],
		'abuse' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_abuse'],
		'search' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_search'],
		'rss' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_rss_feeds'],
		'registration' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_registration'],
		'selling' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_selling'],
		'dailydeal' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_dailydeal'],
		'index' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_main'],
		'workspace' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_workspace'],
		'mediashare' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_workspace'],
		'compare' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_compare'],
		'campaign' . $ilconfig['globalsecurity_extensionmime'] => 'Campaign',
                'ajax' . $ilconfig['globalsecurity_extensionmime'] => 'Ajax',
                'nonprofits' . $ilconfig['globalsecurity_extensionmime'] => 'Nonprofits',
		'escrow' . $ilconfig['globalsecurity_extensionmime'] => 'Escrow',
		// admin control panel
		'components' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_products'],
		'connections' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_connections'],
		'distribution' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_distribution'],
		'language' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_languages'],
		'settings' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_settings'],
		'subscribers' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_customers'],
		'dashboard' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_dashboard'],
		'template' . $ilconfig['globalsecurity_extensionmime'] => $phrase['_templates'],
	);
	
	($apihook = $ilance->api('fetch_breadcrumb_titles_end')) ? eval($apihook) : false;
	
	return $ilcrumbs;
}
/**
* Function to print out the abuse type pulldown menu letting a user select a pre-defined abuse type
*
* @param        string      abuse type (listing, bid, portfolio, profile)
* @param        integer     abuse iddentifier (listing id, bid id, profile id, portfolio id, etc)
*
* @return	boolean     returns true or false if string is seralized
*/
function print_abuse_type_pulldown($abusetype = 'listing', $abuseid = 0)
{
        global $ilance, $phrase;
        
        $s1 = $s2 = $s3 = $s4 = $s5 = $s6 = $s7 = $t1 = $t2 = $t3 = $t4 = $t5 = $t6 = $t7 = $option1 = $option2 = $option3 = $option4 = $option5 = $option6 = $option7 = '';
        switch ($abusetype)
        {
                case 'listing':
                {
                        $s1 = 'selected="selected"';
                        if ($abuseid > 0)
                        {
                                $t1 = '(' . $phrase['_listing_id'] . ': ' . $abuseid . ')';       
                        }
                        $option1 = '<option value="listing" ' . $s1 . '>' . $phrase['_listing_abuse'] . ' ' . $t1 . '</option>';
                        break;
                }        
                case 'bid':
                {
                        $s2 = 'selected="selected"';
                        if ($abuseid > 0)
                        {
                                $t2 = '(' . $phrase['_bid_id'] . ': ' . $abuseid . ')';       
                        }
                        $option2 = '<option value="bid" ' . $s2 . '>' . $phrase['_bidding_abuse'] . ' ' . $t2 . '</option>';
                        break;
                }        
                case 'portfolio':
                {
                        $s3 = 'selected="selected"';
                        if ($abuseid > 0)
                        {
                                $t3 = '(' . $phrase['_attachment_id'] . ': ' . $abuseid . ')';       
                        }
                        $option3 = '<option value="portfolio" ' . $s3 . '>' . $phrase['_portfolio_abuse'] . ' ' . $t3 . '</option>';
                        break;
                }        
                case 'profile':
                {
                        $s4 = 'selected="selected"';
                        if ($abuseid > 0)
                        {
                                $t4 = '(' . $phrase['_profile_id'] . ': ' . $abuseid . ')';       
                        }
                        $option4 = '<option value="profile" ' . $s4 . '>' . $phrase['_profile_abuse'] . ' ' . $t4 . '</option>';
                        break;
                }        
                case 'feedback':
                {
                        $s5 = 'selected="selected"';
                        if ($abuseid > 0)
                        {
                                $t5 = '(' . $phrase['_feedback_id'] . ': ' . $abuseid . ')';       
                        }
                        $option5 = '<option value="feedback" ' . $s5 . '>' . $phrase['_feedback_abuse'] . ' ' . $t5 . '</option>';
                        break;
                }        
                case 'pmb':
                {
                        $s6 = 'selected="selected"';
                        if ($abuseid > 0)
                        {
                                $t6 = '(' . $phrase['_pmb_id'] . ': ' . $abuseid . ')';       
                        }
                        $option6 = '<option value="pmb" ' . $s6 . '>' . $phrase['_pmb_abuse'] . ' ' . $t6 . '</option>';
                        break;
                }
		case 'forum':
                {
                        $s6 = 'selected="selected"';
                        if ($abuseid > 0)
                        {
                                $t7 = '(Topic/Thread ID: ' . $abuseid . ')';       
                        }
                        $option7 = '<option value="forum" ' . $s7 . '>Forum Topic Abuse ' . $t7 . '</option>';
                        break;
                }
        }
        
        $html = '<select name="abusetype" style="font-family: verdana">' . $option1 . '' . $option2 . '' . $option3 . '' . $option4 . '' . $option5 . '' . $option6 . '' . $option7 . '</select>';
        
        return $html;
}
/**
* Function to determine if a string being supplied is already php serailzed() or not
*
* @return	boolean     returns true or false
*/
function is_serialized($data = '') 
{ 
        return (@unserialize($data) !== false); 
}
/**
* Function to determine if a user's profile within the selected category exists or not
*
* @param        integer     user id
* @param        integer     category id
* 
* @return	boolean     returns true or false if profile category is prepared
*/
function is_profile_cat_prepared($userid = 0, $cid = 0)
{
        global $ilance;
        
        return true;
}
/**
* Function to determine if a bidder is invited to an invite only project
*
* @param        integer     user id
* @param        integer     project id
*
* @return	boolean     returns true or false if bidder is invited
*/
function is_bidder_invited($userid = 0, $projectid = 0)
{
        global $ilance, $ilconfig;
        
        $sql = $ilance->db->query("
                SELECT user_id
                FROM " . DB_PREFIX . "projects 
                WHERE project_id = '" . $projectid . "'
                        AND project_state = 'product'
                        AND project_details = 'invite_only'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                
                $invite = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE project_id = '" . $projectid . "'
                                AND buyer_user_id = '" . $userid . "'
                                AND seller_user_id = '" . $res['user_id'] . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($invite) > 0)
                {
                        return true;
                }
                else
                {
                        return false;
                }
        }
        
        return true;
}
/**
* Function to print the various charities within a pulldown menu
*
* @param       integer      (optional) charity id to be as default selection
*
* @return      string       Returns HTML formatted presentation of a pulldown menu
*/
function print_charities_pulldown($charityid = 0)
{
        global $ilance, $phrase, $ilconfig, $ilpage;
        
        $html = '<select name="charityid" style="font-family: verdana">';
                
        $sql = $ilance->db->query("
                SELECT charityid, title
                FROM " . DB_PREFIX . "charities
                WHERE visible = '1'
        ", 0, null, __FILE__, __LINE__);                
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql))
                {
                        if (isset($charityid) AND $charityid > 0 AND $charityid == $res['charityid'])
                        {
                                $html .= '<option value="' . $res['charityid'] . '" selected="selected">' . stripslashes($res['title']) . '</option>';
                        }
                        else
                        {
                                $html .= '<option value="' . $res['charityid'] . '">' . stripslashes($res['title']) . '</option>';
                        }
                }
        }
        
        $html .= '</select>';
        
        return $html;
}
/**
* Function to print the donation percentage pulldown menu
*
* @return      string       Returns HTML formatted presentation of a pulldown menu
*/
function print_donation_percentage($percentage = 0)
{
        $increments = array(5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100);
        
        $html = '<select name="donationpercentage" id="donationpercentage" style="font-family: verdana">';
        foreach ($increments AS $percent)
        {
                if (isset($percentage) AND $percentage > 0 AND $percentage == $percent)
                {
                        $html .= '<option value="' . $percent . '" selected="selected">' . $percent . '%</option>';
                }
                else
                {
                        $html .= '<option value="' . $percent . '">' . $percent . '%</option>';
                }
        }
        $html .= '</select>';
        
        return $html;
}
/**
* Function to fetch specific information about a charity based on a supplied charity id
*
* @return      array        returns an array of stats
*/
function fetch_charity_details($charityid = 0)
{
        global $ilance, $phrase, $ilconfig, $ilpage;
        
        $array = array();
                
        $sql = $ilance->db->query("
                SELECT title, description, url, visible
                FROM " . DB_PREFIX . "charities
                WHERE visible = '1'
                        AND charityid = '" . intval($charityid) . "'
        ", 0, null, __FILE__, __LINE__);                
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                $array['title'] = stripslashes($res['title']);
                $array['description'] = stripslashes($res['description']);
                $array['url'] = $res['url'];
        }
        else
        {
                $array['title'] = $phrase['_unknown'];
                $array['description'] = 'n/a';
                $array['url'] = $ilpage['nonprofits'];
        }
        
        return $array;
}
/**
* Function to parse various marketplace stats for the main home page
*
* @return      string       HTML formatted presentation of the stats
*/
function fetch_stats_overview()
{
        global $ilance;
        
	require_once(DIR_CORE . 'functions_search.php');
	
        $array = array();
        
	// #### reverse auctions posted in the marketplace #####################
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS jobcount
                FROM " . DB_PREFIX . "projects
                WHERE project_state = 'service'
                        AND status = 'open'
        ");
        $res = $ilance->db->fetch_array($sql);        
        $array['jobcount'] = $res['jobcount'];
	
	// #### total amount of revenue experts have earned ####################
	$sql = $ilance->db->query("
                SELECT SUM(bidamount) AS expertsrevenue
                FROM " . DB_PREFIX . "project_bids
                WHERE state = 'service'
                        AND bidstatus = 'awarded'
        ");
        $res = $ilance->db->fetch_array($sql);  
	$array['expertsrevenue'] = $res['expertsrevenue'];
        
	// #### active experts displaying profile in search ####################
	$sqlquery['userquery'] = build_expert_search_exclusion_sql('', 'searchresults');
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS expertcount
                FROM " . DB_PREFIX . "users
                WHERE status = 'active'
                        AND displayprofile = '1'
                        $sqlquery[userquery]
        ");
        $res = $ilance->db->fetch_array($sql);
        $array['expertcount'] = $res['expertcount'];
        
	// #### active, subscribed displaying profile in search ################
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS expertsearch
                FROM " . DB_PREFIX . "users users
                LEFT JOIN " . DB_PREFIX . "subscription_user user ON (users.user_id = user.user_id)
                        WHERE users.status = 'active'
                        AND users.displayprofile = '1'
                        AND user.active = 'yes'
        ");
        $res = $ilance->db->fetch_array($sql);
        $array['expertsearch'] = $res['expertsearch'];
        
	// #### total amount of items in marketplace ###########################
        $sql = $ilance->db->query("
                SELECT COUNT(*) AS itemcount
                FROM " . DB_PREFIX . "projects
                WHERE project_state = 'product'
                        AND status = 'open'
        ");
        $res = $ilance->db->fetch_array($sql);
        $array['itemcount'] = $res['itemcount'];
        
	// #### total amount of item worth in marketplace ######################
	$sql = $ilance->db->query("
                SELECT SUM(bidamount) AS itemsworth
                FROM " . DB_PREFIX . "project_bids
                WHERE state = 'product'
                        AND bidstatus = 'awarded'
        ");
        $res = $ilance->db->fetch_array($sql);
        $array['itemsworth'] = $res['itemsworth'];
	
	// #### total amount of scheduled auctions in marketplace ##############
	$sql = $ilance->db->query("
                SELECT COUNT(*) AS scheduledcount
                FROM " . DB_PREFIX . "projects
                WHERE project_state = 'product'
			AND project_details = 'realtime'
                        AND status = 'open'
			AND date_starts > '" . DATETIME24H . "'
        ");
        $res = $ilance->db->fetch_array($sql);
        $array['scheduledcount'] = $res['scheduledcount'];
        
        return $array;
}
/**
* Function to construct a country pulldown menu
*
* @param       integer      country id
* @param       string       country name
* @param       string       country fieldname
* @param       boolean      disable states pulldown (default false)
* @param       string       states field name
* @param       boolean      show worldwide as an option (default false)
* @param       boolean      show usa/canada at top of list (default false)
* @param       boolean      output option code as regions instead of countries (default false)
*
* @return      string       HTML formatted country pulldown menu
*/
function construct_country_pulldown($countryid = 0, $countryname = '', $fieldname = 'country', $disablestates = false, $statesfieldname = 'state', $showworldwide = false, $usacanadafirst = false, $regionsonly = false, $statesdivid = 'stateid', $onlyiso = false)
{
        global $ilance, $myapi, $ilconfig, $phrase;
        $html = '<select name="' . $fieldname . '" id="' . $fieldname . '"';
	//echo $countryname;
        $html .= ($disablestates == false)
		? ' onchange="return print_states(\'' . $statesfieldname . '\', \'' . $fieldname . '\', \'' . $statesdivid . '\');"'
		: '';
		
        $html .= ' style="font-family: verdana">';
        
	$extraquery = ($usacanadafirst) ? "WHERE locationid != '500' AND locationid != '330'" : '';
	$extraquery = ($regionsonly) ? "" : $extraquery;
        $sql = $ilance->db->query("
                SELECT locationid, location_" . $_SESSION['ilancedata']['user']['slng'] . " AS location, region, cc
                FROM " . DB_PREFIX . "locations
		$extraquery
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
		if ($regionsonly == false)
		{
			$html .= ($showworldwide)
				? '<option value=""></option><option value="' . $phrase['_worldwide'] . '">' . $phrase['_worldwide'] . '</option><option value="' . $phrase['_worldwide'] . '">-------------------------------</option>'
				: '';
				
			$html .= ($usacanadafirst)
				? '<option value="330">Canada</option><option value="500">USA</option><option value="" disabled="disabled">-------------------------------</option>'
				: '';
		}
		
                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
			if ($onlyiso == false)
			{
				$html .= ($regionsonly)
					? '<option value="' . mb_strtolower(str_replace(' ', '_', $res['region'])) . '.' . $res['locationid'] . '"'
					: '<option value="' . $res['location'] . '"';
				
				$html .= (mb_strtolower(str_replace(' ', '_', $res['region']) . '.' . $res['locationid']) == $countryname)
					? ' selected="selected"'
					: '';
					
				$html .= ($res['locationid'] == $countryid)
					? ' selected="selected"'
					: '';
			}
			else
			{
				$html .= '<option value="' . $res['cc'] . '"';
				$html .= ($res['locationid'] == $countryid)
					? ' selected="selected"'
					: '';
			}
			
                        $html .= '>' . handle_input_keywords($res['location']) . '</option>';
                }
        }
        
        $html .= '</select>';
        
        return $html;
}
        
/**
* Function to construct a state pulldown menu
*
* @return      string       HTML formatted state pulldown menu
*/
function construct_state_pulldown($locationid = '', $statename = '', $fieldname = 'state')
{
        global $ilance, $myapi, $ilconfig, $phrase;
        $html = '<select name="' . $fieldname . '" id="' . $fieldname . '" style="font-family: verdana; width:195px">';
        $defaultstate = '';
        
        if (!empty($locationid) AND !empty($statename))
        {
                $sql = $ilance->db->query("
                        SELECT locationid, state
                        FROM " . DB_PREFIX . "locations_states
                        WHERE locationid = " . intval($locationid) . "
                                AND state = '" . $ilance->db->escape_string($statename) . "'
			ORDER BY state ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $defaultstate = $res['state'];
                }
                unset($res);
        }
        else
        {
                if (defined('LOCATION') AND LOCATION == 'admin')
                {
                        $defaultstate = (isset($statename) AND !empty($statename)) ? $statename : $ilconfig['registrationdisplay_defaultstate'];
                }
                else
                {
                        $defaultstate = (!empty($_SESSION['ilancedata']['user']['state'])) ? $_SESSION['ilancedata']['user']['state'] : $ilconfig['registrationdisplay_defaultstate'];
                }
        }
        
        $sql = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "locations_states
                WHERE locationid = '" . intval($locationid) . "' ORDER BY state ASC
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        $html .= '<option value="' . $res['state'] . '"';
                        $html .= (isset($defaultstate) AND $res['state'] == $defaultstate) ? ' selected="selected"' : '';
                        $html .= '>' . stripslashes($res['state']) . '</option>';
                }
        }
        else
        {
                $html .= '<option value="' . $phrase['_unknown'] . '">' . $phrase['_no_states_defined'] . '</option>';
        }
        $html .= '</select>';
        return $html;
}
/**
* Function to print out a payment method icon
*
* @return      string       HTML formatted image (<img> tag)
*/
function print_paymethod_icon($paymethod = 'account')
{
        global $ilance, $phrase, $ilconfig;
        
       if($paymethod != '')
	  {
	  
        $html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $paymethod . '.gif" border="0" alt="' . ucwords($paymethod) . '" />';
		}
		else
		{
		
		$paym = 'card';
		     $html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $paym . '.gif" border="0" alt="' . ucwords($paym) . '" />'; 
		}
        
        return $html;
}
/**
* Function to convert javascript tags to entities
*
* @return      string       HTML formatted string
*/
function handle_input_keywords($text = '', $entities = false)
{
        $text = htmlspecialchars_uni($text, $entities);
        return $text;
}
/**
* Encodes HTML safely for UTF-8. Use instead of htmlentities.
*
* @param        string          $var
* @return       string          Returns a valid UTF-8 string
*/
function ilance_htmlentities($text = '')
{
        if (phpversion() < '4.0.3')
        {
                return htmlentities($text);
        }
        else if (phpversion() < '4.1.0')
        {
                return htmlentities($text, ENT_QUOTES);
        }
        else
        {
                return htmlentities($text, ENT_QUOTES, 'UTF-8');
        }
}
/**
* Function to update untranslated phrases to their master phrase so translators can have something to work from (instead of a blank phrase)
* This script is used when languages are being imported from AdminCP and when a old version of ILance is being upgraded to a newer version
* 
* @return      nothing
*/
function update_untranslated_phrases_to_master()
{
	global $ilance;
	
	// fetch installed languages within marketplace
	$langs = $ilance->db->query("
		SELECT languagecode
		FROM " . DB_PREFIX . "language
	");
	if ($ilance->db->num_rows($langs) > 0)
	{
		while ($reslangs = $ilance->db->fetch_array($langs))
		{
			$installedlanguages[] = $reslangs['languagecode'];
		}
		
		// number of installed languages
		$installedlanguagescount = count($installedlanguages);
		if ($installedlanguagescount > 1)
		{
			// we have more than 1 language installed...
			// let's check for blank phrases to be replaced with the master original phrase
			foreach ($installedlanguages AS $languagetitle)
			{
				$sqlchk = $ilance->db->query("
					SELECT phraseid, text_original, text_" . mb_substr($languagetitle, 0, 3) . "
					FROM " . DB_PREFIX . "language_phrases
					WHERE text_" . mb_substr($languagetitle, 0, 3) . " = ''
				");
				if ($ilance->db->num_rows($sqlchk) > 0)
				{
					while ($reschk = $ilance->db->fetch_array($sqlchk))
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "language_phrases
							SET text_" . mb_substr($languagetitle, 0, 3) . " = text_original
							WHERE phraseid = '" . $reschk['phraseid'] . "'
						");
					}
				}
			}
		}
	}
}
/**
* Function to parse and recreate a user-supplied youtube cut n' paste url
*
* @param        string      youtube video url
*
* @return	boolean     returns true or false
*/
function parse_youtube_video_url($url = '')
{
        $url = str_replace('/watch?v=', '/v/', $url);
        return $url;
}
function fetch_url_image_filename($img = '', $backupimg = '')
{
	if (!empty($img))
	{
		$ar = explode("/", $img);
		$filename = $ar[count($ar) - 1];
		return $filename;
	}
	return $backupimg;
}
/**
* Function to download and save an attachment from a remote url
*
* @param        string      image url (example: http://server.com/image.gif)
*
* @return	boolean     returns true or false
*/
function save_url_image($img = '', $fullpath = DIR_AUCTION_ATTACHMENTS)
{
	if (!empty($img))
	{
		$ch = curl_init($img);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		
		$rawdata = curl_exec($ch);
		curl_close($ch);
	
		$filehash = md5(uniqid(microtime()));
		
		$fp = @fopen($fullpath . $filehash . '.attach', 'x');
		fwrite($fp, $rawdata);
		fclose($fp);
		
		$filetype = '';
		
		// check if uploaded image is actually an image
		if ($data = @getimagesize($fullpath . $filehash . '.attach'))
		{
			$filetype = '';
			if (isset($data['mime']) AND !empty($data['mime']))
			{
				$filetype = $data['mime'];
			}
			
			return array(
				'fullpath' => $fullpath . $filehash . '.attach',
				//'filename' => $filehash . '.attach',
				'filename' => fetch_url_image_filename($img, $filehash . '.attach'),
				'filehash' => $filehash,
				'filetype' => $filetype
			);
		}	
	}
        
        return '';
}
/**
* Function to print the actual shipping partner based on a supplied shipping partner id
*
* @param        integer     shipping partner id
*
* @return	boolean     Returns HTML presentation
*/
function print_shipping_partner($partnerid = 0)
{
        global $ilance, $ilconfig, $phrase;
        
        $html = $phrase['_no_shipping_partner_assigned_to_this_listing'];
                
        $sql = $ilance->db->query("
                SELECT title
                FROM " . DB_PREFIX . "shippers
                WHERE shipperid = '" . intval($partnerid) . "'
                ORDER BY shipperid ASC
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                $html = stripslashes($res['title']);
        }
        
        return $html;
}
/**
* Function to handle all related invoice payments in ILance.
*
* @param        integer     invoice id
* @param        string      invoice type
* @param        integer     amount
* @param        integer     user id
* @param        string      payment method (default account)
* @param        string      payment gateway (default blank)
* @param        string      payment gateway return transaction id
* @param        boolean     silent mode (default false; when true it returns only true or false responses)
*
* @return	mixed       Returns true or false
*/
// murugan changes on Oct 12 for promo code auction here added one more variable that called $amountnew
function invoice_payment_handler($invoiceid = 0, $invoicetype = 'debit', $amount = 0, $amountnew = 0, $userid = 0, $paymethod = 'account', $gateway = '', $gatewaytxn = '', $silentmode = false)
{
	global $ilance, $ilconfig, $phrase, $ilpage, $area_title, $page_title, $show;
	
	$success = false;
	
	switch ($invoicetype)
	{
		// #### subscription payment 
		case 'subscription':
		{
			$ilance->subscription = construct_object('api.subscription');
			
			if ($paymethod == 'account' OR $paymethod == 'ipn')
			{				
				$success = $ilance->subscription->payment($userid, $invoiceid, $paymethod, $gateway, $gatewaytxn, $silentmode);
			}
			break;
		}
		// #### escrow funding payment
		case 'escrow':
		{
		// murugan changes on OCT12 For promo Code Auction 
			$ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_payment = construct_object('api.escrow_payment');
			
			if (($paymethod == 'ipn' OR $paymethod == 'account') AND $ilconfig['escrowsystem_enabled'])
			{
								
				$success = $ilance->escrow_payment->payment($userid, $invoiceid, $invoicetype, $amount, $paymethod, $gateway, $gatewaytxn, $silentmode);
			}
			break;
		}
		// #### debit or escrow fee commission payment
		case 'commission':
		case 'debit':
		{
			$ilance->accounting = construct_object('api.accounting');
			
			if ($paymethod == 'account' OR $paymethod == 'ipn')
			{
				$success = $ilance->accounting->process_debit_payment($userid, $invoiceid, $invoicetype, $amount, $paymethod, $gateway, $gatewaytxn, $silentmode);
			}
			break;
		}
		// #### provider generated invoice to buyer payment
		case 'p2b':
		{
			// #### PAYMENT VIA ONLINE ACCOUNT #####################			
			$ilance->accounting = construct_object('api.accounting');
			$ilance->accounting_p2b = construct_object('api.accounting_p2b');
			
			if ($paymethod == 'account' OR $paymethod == 'ipn')
			{
				$success = $ilance->accounting_p2b->payment($userid, $invoiceid, $invoicetype, $amount, $paymethod, $gateway, $gatewaytxn, $silentmode);
			}
			break;
		}
	}
	
	return $success;
}
function invoice_payment_handler_old($invoiceid = 0, $invoicetype = 'debit', $amount = 0, $userid = 0, $paymethod = 'account', $gateway = '', $gatewaytxn = '', $silentmode = false)
{
	global $ilance, $ilconfig, $phrase, $ilpage, $area_title, $page_title, $show;
	
	$success = false;
	
	switch ($invoicetype)
	{
		// #### subscription payment 
		case 'subscription':
		{
			$ilance->subscription = construct_object('api.subscription');
			
			if ($paymethod == 'account' OR $paymethod == 'ipn')
			{				
				$success = $ilance->subscription->payment($userid, $invoiceid, $paymethod, $gateway, $gatewaytxn, $silentmode);
			}
			break;
		}
		// #### escrow funding payment
		case 'escrow':
		{
			$ilance->escrow = construct_object('api.escrow');
			$ilance->escrow_payment = construct_object('api.escrow_payment');
			
			if (($paymethod == 'ipn' OR $paymethod == 'account') AND $ilconfig['escrowsystem_enabled'])
			{
				$success = $ilance->escrow_payment->payment($userid, $invoiceid, $invoicetype, $amount, $paymethod, $gateway, $gatewaytxn, $silentmode);
			}
			break;
		}
		// #### debit or escrow fee commission payment
		case 'commission':
		case 'debit':
		{
			$ilance->accounting = construct_object('api.accounting');
			
			if ($paymethod == 'account' OR $paymethod == 'ipn')
			{
				$success = $ilance->accounting->process_debit_payment($userid, $invoiceid, $invoicetype, $amount, $paymethod, $gateway, $gatewaytxn, $silentmode);
			}
			break;
		}
		// #### provider generated invoice to buyer payment
		case 'p2b':
		{
			// #### PAYMENT VIA ONLINE ACCOUNT #####################			
			$ilance->accounting = construct_object('api.accounting');
			$ilance->accounting_p2b = construct_object('api.accounting_p2b');
			
			if ($paymethod == 'account' OR $paymethod == 'ipn')
			{
				$success = $ilance->accounting_p2b->payment($userid, $invoiceid, $invoicetype, $amount, $paymethod, $gateway, $gatewaytxn, $silentmode);
			}
			break;
		}
	}
	
	return $success;
}
/**
* Function to fetch a particular role id for a user
*
* @param       string        (month, day)
* @param       integer        user id
*
* @return      integer       returns bids value
*/
function fetch_user_bidcount_per($filter, $userid)
{
        global $ilance, $myapi;
        
        if ($filter == 'day')
        {
        	$sql = $ilance->db->query("
                SELECT bidstoday
                FROM " . DB_PREFIX . "users
                WHERE user_id = '".intval($userid)."'
		        ");
		    if ($ilance->db->num_rows($sql) > 0)
		    {
		        $value = $ilance->db->fetch_array($sql);
		        return $value['bidstoday'];
		    }
		    return 0;
        }
        else if ($filter == 'month')
        {
        	$sql = $ilance->db->query("
                SELECT bidsthismonth
                FROM " . DB_PREFIX . "users
                WHERE user_id = '".intval($userid)."'
	        ");
	        if ($ilance->db->num_rows($sql) > 0)
	        {
	            $value = $ilance->db->fetch_array($sql);
		        return $value['bidsthismonth'];
	        }
	        return 0;
        }
        else 
        {
        	return 0;
        }
}
/**
* Function to format a zipcode for ILance by removing spaces and dashes
*
* @param       string         zip code to format
*
* @return      string         Returns HTML formatted string
*/
function format_zipcode($zipcode = '')
{
	$zipcode = str_replace(' ', '', $zipcode);
	$zipcode = str_replace('-', '', $zipcode);
	$zipcode = strtoupper($zipcode);
	
	return $zipcode;
}
/**
* Function to fetch and return an array with buyer facts such as jobs posted, awarded and the overall award ratio percentage
*
* @param      integer      user id
* @param      string       mode (service or product)
*
* @return     array        Mixed array of amounts requested
*/
function fetch_buyer_facts($user_id = 0, $mode = 'service')
{
	global $ilance, $ilconfig, $show, $phrase;
	
	$jobsposted = $jobsawarded = $awardratio = 0;
	
	$sql = $ilance->db->query("
		SELECT COUNT(*) AS jobsposted
		FROM " . DB_PREFIX . "projects
		WHERE user_id = '" . intval($user_id) . "'
			AND project_state = 'service'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$jobsposted = $res['jobsposted'];
	}
	
	$sql = $ilance->db->query("
		SELECT COUNT(*) AS jobsawarded
		FROM " . DB_PREFIX . "project_bids
		WHERE state = 'service'
			AND project_user_id = '" . intval($user_id) . "'
			AND bidstatus = 'awarded'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$jobsawarded = $res['jobsawarded'];
	}
	
	if ($jobsposted > 0 AND $jobsawarded > 0)
	{
		$awardratio = number_format(($jobsawarded / $jobsposted) * 100, 1);
	}
	
	return array(
		'jobsposted' => $jobsposted,
		'jobsawarded' => $jobsawarded,
		'awardratio' => $awardratio
	);
}
function fetch_savings_total($original_price = 0, $discount_price = 0)
{
	$savings = $original_price - $discount_price;
	$savingspercentage = number_format(($savings / $original_price) * 100, 1);
	
	return array('savings' => $savings, 'savingspercentage' => $savingspercentage);
}
function fetch_currency_symbols_js()
{
	global $ilance, $ilconfig, $headinclude;
	
	$headinclude .= '
<script type="text/javascript">
<!--
function currency_switcher()
{
	var currencyid = fetch_js_object(\'currencyoptions\').options[fetch_js_object(\'currencyoptions\').selectedIndex].value;
	fetch_js_object(\'ship_handlingfee_currency\').innerHTML = currencysymbols[currencyid];
	fetch_js_object(\'ship_handlingfee_currency_right\').innerHTML = currencysymbols2[currencyid];
';
	$headinclude .= $ilconfig['enableauctiontab']
		? '
	fetch_js_object(\'startprice_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'startprice_currency_right\').innerHTML = currencysymbols2[currencyid];
	fetch_js_object(\'buynowprice_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'buynowprice_currency_right\').innerHTML = currencysymbols2[currencyid];
	fetch_js_object(\'reserveprice_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'reserveprice_currency_right\').innerHTML = currencysymbols2[currencyid];
		'
		: '';
		
	$headinclude .= $ilconfig['enablefixedpricetab']
		? '
	fetch_js_object(\'buynowpricefixed_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'buynowpricefixed_currency_right\').innerHTML = currencysymbols2[currencyid];'
		: '';
		
	$headinclude .= $ilconfig['enable_uniquebidding']
		? '
	fetch_js_object(\'retailprice_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'retailprice_currency_right\').innerHTML = currencysymbols2[currencyid];'
		: '';
	
	for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
	{
		$headinclude .= ($ilconfig['globalserverlocale_currencyselector'])
			? 'fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'ship_service_' . $i . '_css_costsymbol_right\').innerHTML = currencysymbols2[currencyid]; '
			: '';
	}
	
$headinclude .= '
}
var currencysymbols = [];
var currencysymbols2 = [];';
	$sql = $ilance->db->query("
		SELECT currency_id, symbol_left, symbol_right
		FROM " . DB_PREFIX . "currency
	");
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$headinclude .= '
currencysymbols[' . $res['currency_id'] . '] = \'' . $res['symbol_left'] . '\'
currencysymbols2[' . $res['currency_id'] . '] = \'' . $res['symbol_right'] . '\'';
	}	
	$headinclude .= '
//-->
</script>' . "\n";
}
function print_currency_pulldown($currencyid = 0, $jsonchange = false, $disabled = false)
{
	global $ilance, $ilconfig;
	
	if ($jsonchange AND $ilconfig['globalserverlocale_currencyselector'])
	{
		fetch_currency_symbols_js();
	}
	
	$html = ($jsonchange AND $ilconfig['globalserverlocale_currencyselector'])
		? '<select name="currencyid" id="currencyoptions" style="font-family: verdana" onchange="currency_switcher()" ' . (($disabled) ? 'disabled="disabled"' : '') . '>'
		: '<select name="currencyid" id="currencyoptions" style="font-family: verdana" ' . (($disabled) ? 'disabled="disabled"' : '') . '>';
		
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "currency
	");
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$html .= '<option value="' . $res['currency_id'] . '"';
		if ($res['currency_id'] == $currencyid)
		{ 
			$html .= ' selected="selected"';
		}
		$html .= '>' . $res['currency_abbrev'] . ' - ' . $res['currency_name'] . '</option>';
	}
	$html .= '</select>';
	
	return $html;
}
function print_next_category($cid = 0, $box = '', $cidfield = 'cid', $showcontinue = 1, $showthumb = 1, $showcidbox = 1, $showyouselectedstring = 1, $readonly = 0, $showcheckmarkafterstring = 1, $categoryfinderjs = 0, $id = 0, $cmd = '', $rss = 0, $news = 0, $showaddanother = 0)
{
	global $ilance, $phrase, $ilconfig;
	
	list($j, $boxnum) = explode('_', $box);
	$boxnum++;
	$newcontent = $newcontentextra = '';
	
	$objResponse = new xajaxResponse();
	//$objResponse->addScript("window.top.document.forms['ilform'].cid.value = '$cid';"); // outside iframe
	$objResponse->addScript("window.top.fetch_js_object('$cidfield').value = '$cid';"); // outside iframe
	
	for ($i = ($boxnum); $i < 16; $i++)
	{
		$objResponse->addAssign('catbox_' . $i, 'innerHTML', '');
	}
	
	// #### show thumbnail with You've selected a category message #########
	if (is_last_category($cid))
	{
		if (is_postable_category($cid))
		{
			if ($showthumb)
			{
				$newcontent = '<div style="padding-top:55px; padding-left:15px; width:230px; font-family: Arial; font-size:13px"><span style="float:left; padding-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'thumbsup.gif" border="0" alt="" /></span>' . $phrase['_youve_selected_a_category_click_continue_button'] . '</div>';
				$objResponse->addAssign('catbox_' . ($boxnum + 1), 'innerHTML', $newcontent);
			}
			
			if ($showcheckmarkafterstring)
			{
				$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" />';
				$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
			}
		}
		else
		{
			if ($showcheckmarkafterstring)
			{
				$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" />';
				$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
			}	
		}
	}
	else
	{
		if (is_postable_category($cid))
		{
			if ($showcheckmarkafterstring)
			{
				$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" />';
				$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
			}
		}
		else
		{
			if ($showcheckmarkafterstring)
			{
				$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" />';
				$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
			}	
		}
	}
	// #### build our recursive category string for display ################
	if ($cid == '-1')
	{
		$recursivecats = $phrase['_assign_to_all_categories'];
	}
	else if ($cid == '0')
	{
		$recursivecats = $phrase['_no_parent_category'];
	}
	else
	{
		if ($showaddanother)
		{
			if ($cmd == 'product')
			{
				$recursivecats = '<div style="padding-top:3px" id="hiderow_' . $cid . '"><input type="hidden" id="subcategories2_' . $cid . '" name="subcategories2[]" value="' . intval($cid) . '" /><span class="blue">' . $ilance->categories->recursive($cid, $cmd, $_SESSION['ilancedata']['user']['slng'], 1, '', 0) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="smaller blue">(<a href="javascript:void(0)" onclick="fetch_js_object(\x27subcategories2_' . $cid . '\x27).disabled=true;toggle_hide(\x27hiderow_' . $cid . '\x27)" style="text-decoration:underline">' . $phrase['_remove'] . '</a>)</span></div>';
				
			}
			else if ($cmd == 'service')
			{
				$recursivecats = '<div style="padding-top:3px" id="hiderow_' . $cid . '"><input type="hidden" id="subcategories_' . $cid . '" name="subcategories[]" value="' . intval($cid) . '" /><span class="blue">' . $ilance->categories->recursive($cid, $cmd, $_SESSION['ilancedata']['user']['slng'], 1, '', 0) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="smaller blue">(<a href="javascript:void(0)" onclick="fetch_js_object(\x27subcategories_' . $cid . '\x27).disabled=true;toggle_hide(\x27hiderow_' . $cid . '\x27)" style="text-decoration:underline">' . $phrase['_remove'] . '</a>)</span></div>';
			}
		}
		else
		{
			$recursivecats = $ilance->categories->recursive($cid, $ilance->GPC['mode'], $_SESSION['ilancedata']['user']['slng'], 1, '', 0);
		}
	}
	
	// #### show you've selected the following category text message #######
	$divcontent = ($showyouselectedstring)
		? '<div style="padding-top:10px; padding-bottom:5px"><strong>' . $phrase['_you_have_selected_the_following_category'] . '</strong></div>' . $recursivecats
		: $recursivecats;
		
	$divcontent .= $newcontentextra;
	unset($recursivecats);
	
	//$divcontent .= '<div style="padding-top:10px"><table width="50%" border="0" cellpadding="0" cellspacing="0" class="smaller"><tr valign="top"><td nowrap="norwap"><div class="gray">Proxy bidding</div><div><strong>Deactivated</strong></div></td><td nowrap="norwap"><div class="gray">Reserve pricing</div><div><strong>Allowed</strong></div></td><td nowrap="norwap"><div class="gray">Anti-bid sniping</div><div><strong>Activated</strong></div></td><td></td></tr></table></div>';
	$objResponse->addScript("window.top.document.getElementById('selectedcategory').innerHTML = '$divcontent';");
	
	// #### show the Continue button #######################################
	if ($showcontinue)
	{
		$div2content = '<div style="padding-top:10px"><input type="submit" value="' . $phrase['_continue'] . '" class="buttons" style="font-size:15px" /></div>';
		$objResponse->addScript("window.top.document.getElementById('categorybutton').innerHTML = '$div2content';");
	}
	
	// #### use javascript to scroll our iframe to furthest right ##########
	$objResponse->addScript("window.scrollTo(2500,0);");
		
	// #### selecting the necessary information based on the category that is being sent to the function.
	$selectedindex = array();
	$index = 0;
	
	// #### determine if we're displaying rss feeds to hide cats that admin prefers not to include
	$rssquery = "";
        if ($rss)
        {
                $rssquery = "AND xml = '1' ";
        }
	
	// #### determine if we're displaying category notifications to hide cats that admin prefers not to include
	$newsquery = "";
        if ($news)
        {
                $newsquery = "AND newsletter = '1' ";
        }
	
	$getcats = $ilance->db->query("
		SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
		FROM " . DB_PREFIX . "categories
		WHERE parentid = '" . intval($cid) . "'
			AND cattype = '" . $ilance->db->escape_string($ilance->GPC['mode']) . "'
			AND visible = '1'
			$rssquery
			$newsquery
		ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($getcats) > 0 AND $cid > 0)
	{
		// #### if there are categories in here, create the selection box to be populated in the table field.
		$newcontent .= '<select disabled="disabled" id="catbox_' . $boxnum . '_list" name="catbox_' . $boxnum . '" onchange="xajax_print_next_category(this[this.selectedIndex].value, \'catbox_' . $boxnum . '\', \'' . $cidfield . '\', \'' . $showcontinue . '\', \'' . $showthumb . '\', \'' . $showcidbox . '\', \'' . $showyouselectedstring . '\', \'' . $readonly . '\', \'' . $showcheckmarkafterstring . '\', \'' . $categoryfinderjs . '\', \'' . $id . '\', \'' . $cmd . '\', \'' . $rss . '\', \'' . $news . '\', \'' . $showaddanother . '\')" size="8" style="position: relative; width:230px; height:212px; font-family: verdana">';
		while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
		{
			$selected = '';
			if ($cid == $res['cid'])
			{
				$selected = 'selected="selected"';
			}
			$newcontent .= '<option value="' . $res['cid'] . '" ' . $selected . '>' . $res['title'] . '' . (is_last_category($res['cid']) ? '' : ' &gt;') . '</option>';
			$selectedindex[$res['cid']] = $index;
			$index++;
		}
		$newcontent .= '</select>';
		$objResponse->addScript("window.fetch_js_object('" . $box . "_list').selectedIndex = fetch_js_object('" . $box . "_list').options[fetch_js_object('" . $box . "_list').value].selectedIndex;");
		$objResponse->addScript('window.setTimeout(function(){fetch_js_object(\'catbox_' . $boxnum . '_list\').disabled = false;},400);');
	}
	
	// #### assign the next selection box ##########################
	$objResponse->addAssign('catbox_' . $boxnum, 'innerHTML', $newcontent);
	
	// #### give time for the ajax call to kick in #########################
	for ($i = ($boxnum + 1); $i < 16; $i++)
	{
		$objResponse->addAssign('catbox_' . $i, 'innerHTML', '');
		if (is_postable_category($cid) == false)
		{
			$objResponse->addScript("window.top.document.getElementById('categorybutton').innerHTML = '';");
		}                        
	}
	
	if (is_postable_category($cid) AND is_last_category($cid) == false)
	{
		if ($showthumb)
		{
			$newcontent = '<div style="padding-top:55px; padding-left:15px; width:230px; font-family: Arial; font-size:13px"><span style="float:left; padding-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'thumbsup.gif" border="0" alt="" /></span>' . $phrase['_youve_selected_a_category_click_continue_button'] . '</div>';
			$objResponse->addAssign('catbox_' . ($boxnum + 1), 'innerHTML', $newcontent);
		}
		
		if ($showcheckmarkafterstring)
		{
			$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" />';
			$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
		}
	}
	
	$objResponse->addScript("fetch_js_object('" . $box . "_list').value = '$cid';");
	$objResponse->addScript("window.scrollTo(2500,0);");
	
	if ($categoryfinderjs)
	{
		$ilance->auction = construct_object('api.auction');
		$ilance->auction_questions = construct_object('api.auction_questions');
		if ($cmd == 'new-item')
		{
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $id, 'input', 'product', 4);
		}
		else if ($cmd == 'product-management' AND $id > 0)
		{
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $id, 'update', 'product', 4);
		}
		else if ($cmd == 'new-rfp')
		{
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $id, 'input', 'service', 4);
		}
		else if ($cmd == 'rfp-management' AND $id > 0)
		{
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $id, 'update', 'service', 4);
		}
		
		if ($project_questions == '')
		{
			$project_questions = $phrase['_no_category_specifics_exist_in_this_category'];	
		}
		
		$objResponse->addScript("window.top.document.getElementById('categoryfindertext').innerHTML = '$project_questions';");
	}
	
	if ($showaddanother)
	{
		$objResponse->addScript("window.top.document.getElementById('showaddanother').innerHTML = '<span class=\"black\"><strong>" . $phrase['_you_can'] . ":</strong></span> <span class=\"green\"><a href=\"javascript:void(0)\" onclick=\"move_from_merge_to(\'selectedcategory\', \'existing" . $cmd . "\')\" style=\"text-decoration:underline\">" . $phrase['_add_another_category_to_your_list'] ."</a></span>';");
	}
	
	return $objResponse->getXML();
}
function fetch_recursive_category_ids_js($cid = '', $cattype = '', $slng = 'eng', $cidfield = 'cid', $showcontinue = 1, $showthumb = 1, $showcidbox = 1, $showyouselectedstring = 1, $readonly = 0, $showcheckmarkafterstring = 1, $categoryfinderjs = false, $id = 0, $cmd = '', $rss = 1, $news = 0, $showaddanother = 0)
{
	global $ilance, $ilconfig, $phrase, $ilpage;
	
	$html = '';
	$delay1st = $delay = 800;
	$count = 1;
	
	// #### fetch our nested breadcrumb bit for this category ######
	$result = $ilance->db->query("
		SELECT parent.cid, parent.title_$slng AS title
		FROM " . DB_PREFIX . "categories AS child,
		" . DB_PREFIX . "categories AS parent
		WHERE child.lft BETWEEN parent.lft AND parent.rgt
			AND parent.cattype = '" . $ilance->db->escape_string($cattype) . "'
			AND child.cattype = '" . $ilance->db->escape_string($cattype) . "'
			AND child.cid = '" . intval($cid) . "'
		ORDER BY parent.lft ASC
	", 0, null, __FILE__, __LINE__);
	$resultscount = $ilance->db->num_rows($result);
	if ($resultscount > 0)
	{
		while ($results = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			if ($count == 1)
			{
				$html .= 'window.setTimeout(function(){xajax_print_next_category(\'' . $results['cid'] . '\',\'catbox_' . $count . '\',\'' . $cidfield . '\',\'' . $showcontinue . '\',\'' . $showthumb . '\',\'' . $showcidbox . '\',\'' . $showyouselectedstring . '\',\'' . $readonly . '\',\'' . $showcheckmarkafterstring . '\',\'' . $categoryfinderjs . '\',\'' . $id . '\',\'' . $cmd . '\',\'' . $rss . '\',\'' . $news . '\',\'' . $showaddanother . '\');},' . $delay1st . ');' . "\n";	
			}
			else
			{
				$delay1st = $delay * $count;
				$html .= 'window.setTimeout(function(){xajax_print_next_category(\'' . $results['cid'] . '\',\'catbox_' . $count . '\',\'' . $cidfield . '\',\'' . $showcontinue . '\',\'' . $showthumb . '\',\'' . $showcidbox . '\',\'' . $showyouselectedstring . '\',\'' . $readonly . '\',\'' . $showcheckmarkafterstring . '\',\'' . $categoryfinderjs . '\',\'' . $id . '\',\'' . $cmd . '\',\'' . $rss . '\',\'' . $news . '\',\'' . $showaddanother . '\');},' . $delay1st . ');' . "\n";	
			}
			
			$count++;
		}
		unset($results);
	}
	
	return $html;
}
function move_listing_category_from_to($pid = 0, $old_catid = 0, $cid = 0, $ctype = '', $old_status = '', $status = '')
{
	global $ilance, $ilconfig, $phrase;
	
	if ($ctype == 'service')
	{
		$table1 = 'project_questions';
		$table2 = 'project_answers';
	}
	else if ($ctype == 'product')
	{
		$table1 = 'product_questions';
		$table2 = 'product_answers';   
	}
	if ($old_catid != $cid AND $pid > 0)
	{
		// remove listing answers for this category
		$sql = $ilance->db->query("
			SELECT questionid
			FROM " . DB_PREFIX . $table1 . "
			WHERE cid = '" . $old_catid . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($questions = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . $table2 . "
					WHERE questionid = '" . $questions['questionid'] . "'
						AND project_id = '" . intval($pid) . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
		
		// update attachment table so everything is sync'ed
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "attachment
			SET category_id = '" . intval($cid) . "'
			WHERE project_id = '" . intval($pid) . "'
		", 0, null, __FILE__, __LINE__);
		
		// update items
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects
			SET cid = '" . intval($cid) . "'
			WHERE project_id = '" . intval($pid) . "'
		", 0, null, __FILE__, __LINE__);
		
		build_category_count($cid, 'add', "adding category count to #$cid");
		build_category_count($old_catid, 'subtract', "subtracting category count from $old_catid");
	}
	
	// is admin changing global status?
	if ($status != $old_status AND $pid > 0)
	{
		// #### admin is setting a new status on listing to open
		if ($status == 'open')
		{
			// add new auction count to the new selected category only
			build_category_count($cid, 'add', "adding category count to #$cid and setting status of listing to $status from $old_status");
		}
		
		// #### admin is setting status to closed, expired, delisted, finished or archived
		else if ($status == 'closed' OR $status == 'expired' OR $status == 'delisted' OR $status == 'frozen' OR $status == 'finished' OR $status == 'archived')
		{
			if ($old_status == 'open')
			{
				// remove old count from the old category
				build_category_count($old_catid, 'subtract', "setting status of listing to $status from $old_status: subtracting increment count from old category id $old_catid");
			}        
		}
	}
}
function fetch_bought_count($userid = 0, $what = 'service')
{
	global $ilance, $ilpage, $phrase, $ilconfig;
	
	$count = 0;
	if ($what == 'service' AND $userid > 0)
	{
		$count = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "serviceawards");
	}
	else if ($what == 'product' AND $userid > 0)
	{
		$count = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "productawards");
	}
	
	return $count;
}
function fetch_sold_count($userid = 0, $what = 'service')
{
	global $ilance, $ilpage, $phrase, $ilconfig;
	
	$count = 0;
	if ($what == 'service' AND $userid > 0)
	{
		$count = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "servicesold");
	}
	else if ($what == 'product' AND $userid > 0)
	{
		$count = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "productsold");
	}
	
	return $count;
}
function fetch_provider_facts($userid = 0)
{
	global $ilance, $phrase, $ilconfig, $ilpage;
	
	$ilance->feedback = construct_object('api.feedback');
	$ilance->subscription =  construct_object('api.subscription');
		
	$memberinfo = array();
	$memberinfo = $ilance->feedback->datastore(intval($userid));
	
	$pattern = str_replace('[fbscore]', $memberinfo['score'], $pattern);
	$pattern = str_replace('[fbpercent]', '<a href="' . print_username(intval($userid), 'url', 0, '', '') . '" title="' . $phrase['_total_positive_feedback_percentile'] . '">' . $memberinfo['pcnt'] . '%</a>', $pattern);
	$pattern = str_replace('[rating]', '<a href="' . print_username(intval($userid), 'url', 0, '', '') . '" title="' . $phrase['_total_feedback_rating_out_of_500'] . '">' . $memberinfo['rating'] . '</a>', $pattern);
	$pattern = str_replace('[stars]', $ilance->feedback->print_feedback_icon($memberinfo['score']), $pattern);
	$pattern = str_replace('[store]', '', $pattern);
	$pattern = str_replace('[verified]', '', $pattern);
	$pattern = str_replace('[subscription]', $ilance->subscription->print_subscription_icon(intval($userid)), $pattern);
	
	$facts = array();
	$facts['jobs'] = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "COUNT(*) AS jobs WHERE user_id = '" . intval($userid) . "' AND bidstatus = 'awarded'", "jobs");
	$facts['milestones'] = $ilance->db->fetch_field(DB_PREFIX . "milestones", "COUNT(*) AS milestones WHERE provider_id = '" . intval($userid) . "'", "milestones");
	$facts['hours'] = $ilance->db->fetch_field(DB_PREFIX . "milestones", "SUM(hours) AS hours WHERE provider_id = '" . intval($userid) . "'", "hours");
	$facts['rating'] = $memberinfo['rating'];
	$facts['stars'] = $ilance->feedback->print_feedback_icon($memberinfo['score']);
	$facts['reviews'] = fetch_service_reviews_reported($userid);
	$facts['scorepercent'] = $memberinfo['pcnt'] . '%';
	$facts['clients'] = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "COUNT(*) AS clients WHERE user_id = '" . intval($userid) . "' AND bidstatus = 'awarded' GROUP BY project_user_id", "clients");
	$facts['repeatclientspercent'] = '0%';
	$facts['earnings'] = print_income_reported($userid);
	$facts['earningsaverage'] = '';
	
	return $facts;
}
// murugan Codeing for Shipper selection  date Sep 29 2010 
		//#############   Murugan Function 	############################
		################################################################
		######  	Murugan Added Fuction					############
		######  Herakle Murugan Coding Sep 29 End Here 	 	############
		################################################################
/**
* Function to fetch any field from the auction table based on the main auction listing number identifier
*
* @param        string      field to fetch from the auction table
* @param        integer     shipper id
*
* @return	string      Returns field value from the shipper table
*/
function fetch_shipper($field = '', $shipperid = 0)
{
        global $ilance, $phrase, $myapi, $phrase;
        
        $sql = $ilance->db->query("
                SELECT $field
                FROM " . DB_PREFIX . "shippers
                WHERE shipperid = '" . intval($shipperid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                return stripslashes($res["$field"]);
        }
        
        return $phrase['_unknown'];
}
// murugan Codeing for Shipper selection  date Nov 01 2010 
function fetch_category($field = '', $cid = 0)
        {
                
				global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT $field
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);						
                        return $res["$field"];
                }
                else
                {
                        return 'default';
                }
        }
		
		function fetch_question_table($questionid = 0)
{
  global $ilance, $myapi;
  
  $sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "question_table
			WHERE questionid = '".$questionid."'	
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
			$res = $ilance->db->fetch_array($sql);
			$field = $res['field_name'];
			$table = $res['table_name'];
			$select = $ilance->db->query("
					  SELECT $field
			          FROM " . DB_PREFIX . "$table
					  ");
		    while($result = $ilance->db->fetch_array($select))
			{
			  $test[] = $result["$field"];
			}
			$choice = implode('|',$test);
			$update = $ilance->db->query("
			UPDATE ".DB_PREFIX."product_questions
			SET multiplechoice = '".$choice."'
			WHERE questionid = '".$questionid."'
			");			
			
			return $choice;
	}
	else
	{
			return 'default';
	}
}
// Murugan Changes On Dec 11 For Page Pagination
function print_pagnation_new($number = 0, $rowlimit = 0, $page = 0, $counter = 0, $scriptpage = '', $custompagename = 'page', $questionmarkfirst = false)
{
        global $ilance, $myapi, $phrase, $ilconfig;
        $html = '';
        
        if (empty($custompagename))
        {
                $custompagename = 'page';
        }
        
        $totalpages = ceil(($number / $rowlimit));
        if ($totalpages == 0)
        {
                $totalpages = 1;
        }
        
        //if ($number > $rowlimit)
        //{
                $html .= '<div style="margin-top:6px"><table cellpadding="4" cellspacing="0" border="0" width="100%" align="center" dir="' . $ilconfig['template_textdirection'] . '"><tr>';
                
                $startend = construct_start_end_array($page, $rowlimit, $number);
                $html .= '<td class="" style="padding:4px"><span style="float:left" class="gray">' . $ilance->language->construct_phrase($phrase['_showing_results_x_to_x_of_x'], array('<strong>' . $startend['first'] . '</strong>', '<strong>' . $startend['last'] . '</strong>', '<strong> ' . number_format($number) . '</strong>')) . '.</span> <span style="float:right"><strong>' . $phrase['_page'] . ' ' . number_format($page) . '</strong> ' . $phrase['_of'] . ' <strong>' . number_format($totalpages) . '</strong></span></td>';
                $html .= '<td class="" width="1" style="padding-left:12px"></td>';
                
                if ($page > 1)
                {
                        $html .= '<td class="" width="1" align="left" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=1&amp;pp=' . $rowlimit . '" title="' . $phrase['_goto_first_page'] . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_left_first.gif" border="0" alt="' . $phrase['_goto_first_page'] . '" /></a></td>';
                        $html .= '<td class="" width="1" align="left" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" title="' . $phrase['_prev_page'] . ': ' . ($page - 1) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_left.gif" border="0" alt="' . $phrase['_prev_page'] . ': ' . ($page - 1) . '" /></a></td>';
                        $html .= '<td class="" width="1" style="padding-right:12px"></td>';
                }
                
                if ($page > 10)
                {
                        $inc = floor(($page - 3) / 3);
                        for ($i = 1; $i < $page - 3; $i += $inc)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                else
                {
                        for ($i = 1; $i < $page - 3; $i++)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                
                for ($i = $page - 3; $i < $page; $i++)
                {
                        if ($i > 0)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                
                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><strong>' . $page . '</strong></td>';
                
                for ($i = $page + 1; $i <= $page + 3; $i++)
                {
                        if ($i > 0 AND $i <= $totalpages)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                
                if (($totalpages - $page) > 10)
                {
                        $temp = '';
                        
                        $inc = floor(($totalpages - ($page + 3)) / 3);
                        for ($i = $totalpages; $i > $page + 3; $i -= $inc)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $temp = '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                        
                        $html .= $temp;
                }
                else if ($totalpages - $page > 3)
                {
                        for ($i = $page + 4; $i <= $totalpages; $i++)
                        {
                                $startend = construct_start_end_array($i, $rowlimit, $number);
                                $html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase($phrase['_show_results_x_to_x_of_x'], array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
                        }
                }
                
                if ($page < $totalpages)
                {
                        $html .= '<td class="" align="right" width="1" style="padding-left:12px"></td>';
                        $html .= '<td class="" align="right" width="1" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="' . $phrase['_next_page'] . ': ' . number_format(($page + 1)) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_right.gif" border="0" alt="' . $phrase['_next_page'] . ': ' . number_format(($page + 1)) . '" /></a></td>';
                        $html .= '<td class="" align="right" width="1" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($totalpages) . '&amp;pp=' . $rowlimit . '" title="' . $phrase['_goto_last_page'] . ': ' . number_format($totalpages) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_right_last.gif" border="0" alt="' . $phrase['_goto_last_page'] . ': ' . number_format($totalpages) . '" /></a></td>';
                }
                
                $html .= '</tr></table></div>';
        //}
        
        return $html;
}
// Added By Murugan On jan 06 for Switch User
/**
* Function to build a valid user session after successful sign-in.  This function was created because we've implemented the new admin user switcher
* and it's pointless to handle 2 large pieces of code for session building- so this was created.  
*
* @param       array          $userinfo array of user from the database
*
* @return      nothing
*/
function build_user_session($userinfo = array())
{
        global $ilance, $ilconfig, $ilpage, $_SESSION;
        
	$_SESSION['ilancedata'] = array(
		'user' => array(
			'isadmin' => $userinfo['isadmin'],
			'isstaff' => $userinfo['isstaff'],
			'access_bb' => intval($userinfo['access_bb']),
			'status' => $userinfo['status'],
			'userid' => $userinfo['user_id'],
			'username' => $userinfo['username'],
			'password' => $userinfo['password'],
			'salt' => $userinfo['salt'],
			'email' => $userinfo['email'],
			'firstname' => stripslashes($userinfo['first_name']),
			'lastname' => stripslashes($userinfo['last_name']),
			'fullname' => $userinfo['first_name'] . ' ' . $userinfo['last_name'],
			'address' => ucwords(stripslashes($userinfo['address'])),
			'address2' => ucwords(stripslashes($userinfo['address2'])),
			'fulladdress' => ucwords(stripslashes($userinfo['address'])) . ' ' . ucwords(stripslashes($userinfo['address2'])),
			'city' => ucwords(stripslashes($userinfo['city'])),
			'state' => ucwords(stripslashes($userinfo['state'])),
			'postalzip' => mb_strtoupper(trim($userinfo['zip_code'])),
			'countryid' => intval($userinfo['country']),
			'country' => print_country_name($userinfo['country']),
			'countryshort' => print_country_name($userinfo['country'], mb_substr($userinfo['languagecode'], 0, 3), true),
			'lastseen' => $userinfo['lastseen'],
			'ipaddress' => $userinfo['ipaddress'],
			'iprestrict' => $userinfo['iprestrict'],
			'auctiondelists' => intval($userinfo['auctiondelists']),
			'bidretracts' => intval($userinfo['bidretracts']),
			'ridcode' => $userinfo['rid'],
			'dob' => $userinfo['dob'],
			'serviceawards' => intval($userinfo['serviceawards']),
			'productawards' => intval($userinfo['productawards']),
			'servicesold' => intval($userinfo['servicesold']),
			'productsold' => intval($userinfo['productsold']),
			'rating' => $userinfo['rating'],
			'languageid' => intval($userinfo['languageid']),
			'slng' => mb_substr($userinfo['languagecode'], 0, 3),
			'styleid' => intval($userinfo['styleid']),
			'timezoneid' => intval($userinfo['timezoneid']),
			'timezonedst' => $userinfo['timezone_dst'],
			'distance' => $userinfo['project_distance'],
			'emailnotify' => intval($userinfo['emailnotify']),
			'companyname' => stripslashes($userinfo['companyname']),
			'roleid' => intval($userinfo['roleid']),
			'subscriptionid' => intval($userinfo['subscriptionid']),
			'cost' => $userinfo['cost'],
			'active' => $userinfo['active'],
			'currencyid' => intval($userinfo['currencyid']),
			'currencyname' => stripslashes($userinfo['currency_name']),
			'enable_batch_bid' => isset($userinfo['enable_batch_bid']) ? $userinfo['enable_batch_bid'] : 0,
			'is_auto_lower_min_bid' => intval($userinfo['is_auto_lower_min_bid']) ? $userinfo['is_auto_lower_min_bid'] : 0,
			'auto_min_bid_lower_prec' => intval($userinfo['auto_min_bid_lower_prec']),
			'currencysymbol' => isset($userinfo['currencyid']) ? $ilance->currency->currencies[$userinfo['currencyid']]['symbol_left'] : '$',
			'currencyabbrev' => mb_strtoupper($userinfo['currency_abbrev']),
			'searchoptions'  => isset($userinfo['searchoptions']) ? $userinfo['searchoptions'] : '',
			'token' => TOKEN,
			'siteid' => SITE_ID,
		)
	);
}
// Murugan Changes On Jan 31 For Combine Invoice
/*
* Function to consolidate all unpaid invoices for a user and for 1 project
* A project of zero is for all misc charges not tied to any auction.
*
* @function    MP_cons
*
* @param       integer       UserID
*
* @param       integer       ProjectID
*
* @return      integer       InvoiceID of new or single invoice
*/
//".intval($projectid)."
Function MP_cons($UserID = 0, $projectid = 0)
{
 global $ilance, $myapi, $ilconfig;
 $sql = $ilance->db->query("
 SELECT *
 FROM ".DB_PREFIX."invoices
 WHERE user_id = '".intval($UserID)."'
 AND projectid != '0'
 AND status = 'unpaid'
 AND (invoicetype = 'buynow' OR invoicetype = 'escrow')
 ");
 $totalamount = 0;
 $invoiceid = 0;
 $CrLf = chr(13) . chr(10);
 $transactionid = "";
 if ($ilance->db->num_rows($sql) > 0)
 {
  // Do we need to consolidate?
  if ($ilance->db->num_rows($sql) > 1)
  {
   // Store standard items for new invoice
   $firstrecord = 0;
   while ($res = $ilance->db->fetch_array($sql))
   {
    if ($firstrecord < 1)
    {
     $subscriptionid = $res['subscriptionid'];
     $projectid = $res['projectid'];
     $buynowid = $res['buynowid'];
     $user_id = $res['user_id'];
     $p2b_user_id = $res['p2b_user_id'];
     $storeid = $res['storeid'];
     $orderid = $res['orderid'];
     $status = 'unpaid';
     $paymethod = $res['paymethod'];
     $ipaddress = $res['ipaddress'];
     $referer = "Invoice Consolidation";
     $createdate = DATETIME24H;
     $duedate = DATETIME24H;
     $paiddate = $res['paiddate'];
     $custommessage = $res['custommessage'];
     $archive = $res['archive'];
     $ispurchaseorder = $res['ispurchaseorder'];
     $isdeposit = $res['isdeposit'];
     $iswithdraw = $res['iswithdraw'];
     $invoicetype = $res['invoicetype'];
     $firstrecord = 1;
    }
	
    // Total up Items for consolidated Invoice
    $description .= $res['description']. ' <br> '. $CrLf;
    $amount += $res['amount'];
    $paid += $res['paid'];
    $taxamount += $res['taxamount'];
    $totalamount += $res['totalamount'];
   }
	 $ilance->tax = construct_object('api.tax');
	$taxinfo = '';
	$istaxable = 0;
		   if ($ilance->tax->is_taxable($user_id, $invoicetype) AND $amount > $ilconfig['staffsettings_max_tax_limit'])
			{
					// fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount($user_id, $amount, $invoicetype, 0);
					
					// fetch total amount to hold within the "totalamount" field
					$totalamount = ($amount + $taxamount);
					
					// fetch tax bit to display when outputing tax infos
					$taxinfo = $ilance->tax->fetch_amount($user_id, $amount, $invoicetype, 1);
					$istaxable = 1;
			}  
	 // Mark old invoices 'canceled'
   $ilance->db->query("
   UPDATE ".DB_PREFIX."invoices
   SET status = 'cancelled', custommessage = '*CONSOLIDATED* ',
   paiddate = '".$createdate."'
   WHERE user_id = '".intval($UserID)."'
   AND projectid != '0'
   AND status = 'unpaid'
   ");
   // Write new invoice
   $transactionid = construct_transaction_id();
   $ilance->db->query("
   INSERT INTO ".DB_PREFIX."invoices
   (invoiceid, subscriptionid, projectid, buynowid, user_id, p2b_user_id, storeid, orderid, description, invoicetype,
   amount, paid, totalamount, istaxable, taxamount, taxinfo, status, paymethod, ipaddress, referer, createdate, duedate, paiddate, custommessage,
   transactionid, archive, ispurchaseorder, isdeposit, iswithdraw)
   VALUES(
   NULL,
   '".intval($subscriptionid)."',
   '".intval($projectid)."',
   '".intval($buynowid)."',
   '".intval($user_id)."',
   '".intval($p2b_user_id)."',
   '".intval($storeid)."',
   '".intval($orderid)."',
   '".$description."',
   '".$ilance->db->escape_string($invoicetype)."',
   '".sprintf("%01.2f", $amount)."',
   '".sprintf("%01.2f", $paid)."',
   '".sprintf("%01.2f", $totalamount)."',
   '".intval($istaxable)."',
   '".sprintf("%01.2f", $taxamount)."',
   '".$ilance->db->escape_string($taxinfo)."',
   '".$ilance->db->escape_string($status)."',
   '".$ilance->db->escape_string($paymethod)."',
   '".$ilance->db->escape_string($ipaddress)."',
   '".$ilance->db->escape_string($referer)."',
   '".$ilance->db->escape_string($createdate)."',
   '".$ilance->db->escape_string($duedate)."',
   '".$ilance->db->escape_string($paiddate)."',
   '".$ilance->db->escape_string($custommessage)."',
   '".$ilance->db->escape_string($transactionid)."',
   '".$ilance->db->escape_string($archive)."',
   '".intval($ispurchaseorder)."',
   '".intval($isdeposit)."',
   '".intval($iswithdraw)."')
   ", 0, null, __FILE__, __LINE__);
   // fetch new last invoice id
   $invoiceid = $ilance->db->insert_id();
  }
  else
  {
   // Only 1 invoice so no need to consolidate
   $res = $ilance->db->fetch_array($sql);
   $totalamount = $res['totalamount'];
   $invoiceid = $res['invoiceid'];
   $transactionid = $res['transactionid'];
  }
 }
 return $invoiceid;
}
//##########    HERAKLE start feb 14 2011    ############//
function invoice_offline($method = '',$currencyid , $projectid = 0, $buynow_id = 0, $qty = 0, $amount = 0, $total = 0, $seller_id = 0, $buyer_id = 0, $shipping_address_required = 1, $shipping_address_id = 0, $accountid = 0, $buyerpaymethod = 'Unknown', $buyershipcost = 0, $buyershipperid = 0)
        {
		
		global $ilance;
		
					
										
		   $ilance->db->query("INSERT INTO " . DB_PREFIX . "invoices
                                                (invoiceid, currency_id, projectid, buynowid, user_id, p2b_user_id, description, amount, paid, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid)
                                                VALUES(
                                                NULL,
												'" . intval($currencyid) . "',
                                                '" . intval($projectid) . "',
												'" . intval($buynow_id) . "',
                                                '" . intval($buyer_id) . "',
                                                '" . intval($seller_id) . "',
                                                '" . $ilance->db->escape_string($phrase['_purchase_now']) . " " . $ilance->db->escape_string($phrase['_escrow_payment_forward']) . " - " . $ilance->db->escape_string(fetch_auction('project_title', $projectid)) . " #" . $projectid . "',
                                                '" .  $amount . "',
                                                '" .  $amount . "',
                                                '" . $total . "',
                                                'unpaid',
                                                'escrow',
                                                'account',
                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                '" . DATETIME24H . "',
                                                '" . DATETIME24H . "',
                                                '',
                                                '" . $ilance->db->escape_string($phrase['_funds_held_within_escrow_until_item_has_been_delivered']) . " - " . DATETIME24H . "',
                                                '" . $transactionid . "')
                                        ");
		                         return true;
		
}

function combine_invoice_payment($invoiceid,$accountid,$picked_up,$picked_up_date)
{
	global $ilance,$ilconfig;

	$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoice_projects WHERE final_invoice_id = '" . $invoiceid . "'");

	while($line = $ilance->db->fetch_array($sql))
	{
		$sql1 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices WHERE invoiceid = '" . $line['invoice_id'] . "' AND status != 'paid'");

		//mark each seller's account with new balance and mark this invoice as paid
		if($ilance->db->num_rows($sql1) > 0)
		{
			while($line1 = $ilance->db->fetch_array($sql1))
			{
				$shipment_date = DATETIME24H;

				if($line1['p2b_user_id'] != 0)
				{
					$accountdata = fetch_user_balance($line1['p2b_user_id']);
					$new_abalance = $accountdata['available_balance']+$line1['amount'];
					$new_tbalance = $accountdata['total_balance']+$line1['amount'];

					// update sellers account balances
					$ilance->db->query("UPDATE " . DB_PREFIX . "users
						SET total_balance = '".$new_tbalance."',
						available_balance = '".$new_abalance."'
						WHERE user_id = '" . $line1['p2b_user_id'] . "'
					", 0, null, __FILE__, __LINE__);

					// update invoice status
					$ilance->db->query("UPDATE " . DB_PREFIX . "invoice_projects
						SET status = 'paid'
						WHERE invoice_id = '" . $line1['invoiceid'] . "'
					", 0, null, __FILE__, __LINE__);

					// update combined invoices
					$ilance->db->query("UPDATE " . DB_PREFIX . "invoices
						SET status = 'paid',
						paymethod = '" . $accountid . "',
						paiddate = '" . DATETIME24H . "'
						WHERE invoiceid = '" . $line1['invoiceid'] . "'
					", 0, null, __FILE__, __LINE__);

					//buynow order paiddate kkk feb8		
					$ilance->db->query("UPDATE " . DB_PREFIX . "buynow_orders
						SET paiddate = '" . DATETIME24H . "',
						winnermarkedaspaid = '1',
						winnermarkedaspaiddate = '" . DATETIME24H . "'
						WHERE invoiceid = '" . $line1['invoiceid'] . "'
					", 0, null, __FILE__, __LINE__);

					// update shipment
					if ($picked_up) {

						$line['shipper_id'] = 36;
						$shipment_date = $picked_up_date;
					}
					
					$ilance->db->query("INSERT INTO "  . DB_PREFIX . "shippnig_details (item_id, coin_id, cust_id, buyer_id, shipper_id,  shipment_date, email,invoice_id,final_invoice_id) 
						value (
						'" . $line['project_id'] . "',
						'" . $line['coin_id'] . "',
						'" . $line['seller_id'] . "',
						'" . $line['buyer_id'] . "',
						'" . $line['shipper_id'] . "',
						'" . $shipment_date . "',
						'NO',
						'" . $line['invoice_id'] . "',
						'" . $invoiceid . "')
					");
				}
				else
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "invoice_projects
						SET status = 'paid'
						WHERE invoice_id = '" . $line1['invoiceid'] . "'
					", 0, null, __FILE__, __LINE__);

					$ilance->db->query("UPDATE " . DB_PREFIX . "invoices
						SET status = 'paid',
						paymethod = '" . $accountid . "',
						paiddate = '" . DATETIME24H . "'
						WHERE invoiceid = '" . $line1['invoiceid'] . "'
					", 0, null, __FILE__, __LINE__);

					// update combined invoices
					//place bid paiddate kkk feb8		
					$ilance->db->query("UPDATE " . DB_PREFIX . "project_bids
						SET winnermarkedaspaiddate = '" . DATETIME24H . "'
						WHERE bidstatus = 'awarded' 
						AND user_id = '" . $line1['user_id'] . "' 
						AND project_id = '" . $line1['projectid'] . "'
					", 0, null, __FILE__, __LINE__);

					// update shipment
					if ($picked_up) {

						$line['shipper_id'] = 36;
						$shipment_date = $picked_up_date;
					}

					$ilance->db->query("INSERT INTO "  . DB_PREFIX . "shippnig_details (item_id, coin_id, cust_id, buyer_id, shipper_id,  shipment_date, email, invoice_id, final_invoice_id) value (
						'" . $line['project_id'] . "',
						'" . $line['coin_id'] . "',
						'" . $line['seller_id'] . "',
						'" . $line['buyer_id'] . "',
						'" . $line['shipper_id'] . "',
						'" . $shipment_date . "',
						'NO',
						'" . $line['invoice_id'] . "',
						'" . $invoiceid . "')
					");
				}
			}
		}
		//$ilance->db->query("update ".DB_PREFIX."invoices set status='paid',paymethod='".$accountid."',paiddate='".DATETIME24H."' where invoiceid 	='".$invoiceid."'");

		$ilance->db->query("UPDATE " . DB_PREFIX . "invoices 
			SET status = 'paid',
			paymethod = '" . $accountid . "',
			paiddate = '" . DATETIME24H . "',
			amount = 0,
			paid = totalamount 
			WHERE invoiceid = '" . $invoiceid . "'
		");

		$ilance->db->query("UPDATE " . DB_PREFIX . "invoices 
			SET scheduled_date = paiddate 
			WHERE invoiceid = '" . $invoiceid . "' 
			AND scheduled_date = '0000-00-00 00:00:00'
		");
	}

	return false;
}
//suku
function construct_motd_list()
{
	global $ilance;
	$result=$ilance->db->query("SELECT subject,postsid FROM ".DB_PREFIX."kbposts WHERE catid=18 AND approved=1 ORDER BY postsid DESC limit 10");
	//$html='<div style="padding-top:10px; width:260px;color:#FFFFFF;" id="werq">';
	$html='';
	if($ilance->db->num_rows($result))
	{
	while($row=$ilance->db->fetch_array($result))
	{
	$html.='<li style="list-style-type:disc">';
	$ilance->bbcode = construct_object('api.bbcode');
    //venkat
	$new_str="";
	$str=$row['subject'];
	if(strlen($str)>80)
	{
	$new_str.=substr($str,'0','80');
	$new_str.="...";
	}
	else
	{
	$new_str=$str;
	}
   //karthik on may20 for SEO
   // $motd = '<a href='.HTTP_KB.construct_seo_url_name($row['subject']).'-t'.$row['postsid'].'-4.html>'.stripslashes($row['subject']).'</a>';
   $motd = '<a id="foowhite"  href='.HTTP_KB.construct_seo_url_name($row['subject']).'-t'.$row['postsid'].'-4.html>'.stripslashes($new_str).'</a>';
    $html.= $motd;
	$html.='</li>';
	}
	//$html.='</div>';
	}
	return $html;
}
	
	function fetch_cons_state($new_id,$date)
{
        global $ilance, $myapi, $ilconfig;
       // item_id	item_title	relist_count	start_date	end_date
         $sql = $ilance->db->query("
					SELECT sum(qty) as qty,sold_price,seller_fee FROM ". DB_PREFIX ."consign_statement
					WHERE item_id = '".$new_id."' AND date(end_date) = '".$date."'");
        if ($ilance->db->num_rows($sql) > 0)
        {
             $lang = $ilance->db->fetch_array($sql);
			$qty =  $lang['qty'];
			$sold_price = $lang['sold_price'] * $lang['qty'];
			$list_total = $lang['insertion_fee'] + $lang['featured_fee'] + $lang['highlite_fee'] + $lang['bold_fee'];
			$seller_fee = $lang['seller_fee'] * $lang['qty'];
			$net_cons = $sold_price - ($seller_fee + $list_total);
			$to = $lang['qty'].'|'.$sold_price.'|'.$seller_fee.'|'.$list_total.'|'.$net_cons;
        }
        return $to;
}
   function shipping_details($shipid,$cou)
	{
	global $ilance;
	  $sql1 = $ilance->db->query("SELECT *
                                FROM " . DB_PREFIX . "shippers 
                                 WHERE shipperid = '".$shipid."' 
                                 GROUP BY  shipperid		
								          ");
										  
			if($ilance->db->num_rows($sql1)>0)
					 {
					    while($totallist1=$ilance->db->fetch_array($sql1))
						 {	
						     $tot_cou = $cou-1;
							 $basefee = $totallist1['basefee'];
							 $added_fee = $totallist1['addedfee'];
							 
							$add_fee = $tot_cou*$added_fee;
							
							$ship_fee = $basefee+$add_fee;
					        
							return $ship_fee;
							
						 }	 
						 
						  
						
					 }	
					 else
					 {
					 	return false;
					 }
					 						  
										  
	
	}
		
		
// herakle end 	
//bug1736 starts 
function check_access($page_name="")
{
global $ilance,$ilpage,$ilconfig;
	if((isset($_SESSION['staff'][$page_name]) AND $_SESSION['staff'][$page_name] == '1')or($_SESSION['ilancedata']['user']['isadmin']==1))
	{
		return true;
	}
	else
	{
		
	   print_action_failed('Access denied',$_SERVER['HTTP_REFERER']);
	   exit();
	}
}
function check_tab_access($tab_name="")
{
global $ilance,$ilpage,$ilconfig;
	if((isset($_SESSION['staff'][$tab_name]) AND $_SESSION['staff'][$tab_name] == '1')or($_SESSION['ilancedata']['user']['isadmin']==1))
	{
		return true;
	}
	else
	{		
		
		return false;
	}
}
function show_tab_error()
{
	$show_error='<div style="border:1px solid;border-radius:5px;margin-top:5px;">				
				 <div style="background-color:#333333;color:#ffffff;height:12px;padding:12px;">
								Failed
                   </div>								
				   <div style="padding:9px;background-color:#DEDEDE;font-size:12px;">								
				      <div>One or more actions were not completed due to a problem that has occured</div>					
				   </div>								
				  <div style="padding:10px;">
						<blockquote>
							<strong>  Access denied </strong>
					    </blockquote>
				 </div>								
                </div>';
	return $show_error;				   
}
//bug1736 ends
// EDITED BY TAMIL FOR BUG 1989 ON 30/10/12 * START
function fetch_pop_up_search_title($search_id='')
{
	 global $ilance, $ilconfig, $phrase;
        
        $html = '';
        
        $sql = $ilance->db->query("
                SELECT title
                FROM " . DB_PREFIX . "search_favorites
                WHERE searchid='".intval($search_id)."'
        ", 0, null, __FILE__, __LINE__);
		 
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		
		$html=	$res['title'];
		
		return $html;
		
}
// EDITED BY TAMIL FOR BUG 1989 ON 30/10/12 * END
//EDITED BY TAMIL ON 21/11/12 FOR SITEMAP * START//ADDED 'wantlist' to the array by TAMIL for Bug 2503 
function print_accepted_array()
{
	$accepted=array('greatcollections-vs-ebay','wantlist','terms','contact','about','consign-now','consign-now1','consign-now2','why-greatcollections','news','promise','through','sell','selling-instructions','larryking','worth','grading','consignment','privacy','PCGS','NGC','ANACS','FUN','ANA','CAC','NAA','WINGS','ICTA','allitem','return','paymonth','404','members','cce','pcgs-sell','pcgs-raw');
	return $accepted;
}



//EDITED BY TAMIL ON 21/11/12 FOR SITEMAP * END
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
