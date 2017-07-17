<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # iLance Knowledge Base1.0.8 Build 85
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

/**
* LanceKB class to perform the majority of LanceKB functions and operations
*
* @package      LanceKB
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class lancekb
{
	var $config = array();
        var $fetch = array();

	/*
        * Constructor
        */
	function lancekb()
	{
		global $ilance;
		
		$query = $ilance->db->query("
			SELECT configtable, version
			FROM " . DB_PREFIX . "modules_group
			WHERE modulegroup = 'lancekb'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($query) > 0)
		{
                        $table = $ilance->db->fetch_array($query);
                        if (!empty($table['configtable']))
                        {
                                $sql = $ilance->db->query("
					SELECT name, value
					FROM " . DB_PREFIX . $table['configtable'] . "
				", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
					while ($res = $ilance->db->fetch_array($sql))
					{
						$this->config[$res['name']] = $res['value'];
					}
					unset($res);
					
					// make sure we include the actual version in the config array
					$this->config['version'] = $table['version'];
                                }
                        }
		}
	}
	
        /*
        * Function to print out the LanceKB configuration window
        *
        * @return      string       HTML representation of the configuration options and settings
        */  
	function print_configuration()
        {
                global $ilance, $phrase, $ilpage;
		
                $sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "kb_configuration
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html = '';
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                switch ($res['inputtype'])
                                {
                                        case 'yesno':
                                        {
                                                if ($res['value'])
                                                {
                                                        $check1 = 'checked="checked"';
                                                        $check2 = '';
                                                }
                                                else
                                                {
                                                        $check2 = 'checked="checked"';
                                                        $check1 = '';
                                                }
                                                $html .= '<tr class="alt1"><td><div><strong>' . stripslashes($res['description']) . '</strong></div><div style="padding-top:3px" class="gray">' . stripslashes($res['comment']) . '</div><div style="padding-top:5px"><input type="radio" name="' . $res['name'] . '" id="' . $res['name'] . '1" value="1" ' . $check1 . ' /><label for="' . $res['name'] . '1">' . $phrase['_yes'] . '</label> <input name="' . $res['name'] . '" id="' . $res['name'] . '2" type="radio" value="0" ' . $check2 . ' /><label for="' . $res['name'] . '2">' . $phrase['_no'] . '</label></div></td></tr>';
                                                break;
                                        }				
                                        case 'int':
                                        {
                                                $html .= '<tr class="alt1"><td><div><strong>' . stripslashes($res['description']) . '</strong></div><div style="padding-top:3px" class="gray">' . stripslashes($res['comment']) . '</div><div style="padding-top:5px"><input type="text" name="' . $res['name'] . '" value="' . $res['value'] . '" style="width: 50px;"></div></td></tr>';
                                                break;
                                        }				
                                        case 'text':
                                        {
                                                $html .= '<tr class="alt1"><td><div><strong>'.stripslashes($res['description']).'</strong></div><div style="padding-top:3px" class="gray">' . stripslashes($res['comment']) . '</div><div style="padding-top:5px"><input type="text" name="' . $res['name'] . '" value="' . $res['value'] . '" style="width: 500px;" /></div></td></tr>';
                                                break;
                                        }				
                                        case 'textarea':
                                        {
                                                $html .= '';
                                                break;
                                        }				
                                        case 'password':
                                        {
                                                $html .= '<tr class="alt1"><td><div><strong>'.stripslashes($res['description']).'</strong></div><div style="padding-top:3px" class="gray">' . stripslashes($res['comment']) . '</div><div style="padding-top:5px"><input type="password" name="' . $res['name'] . '" value="' . $res['value'] . '" style="width: 500px;" /></div></td></tr>';
                                                break;
                                        }
                                }
                        }
			
                        $template = '
<form method="post" action="' . $ilpage['components'] . '" accept-charset="UTF-8" style="margin: 0px;">
<input type="hidden" name="cmd" value="components" />
<input type="hidden" name="subcmd" value="_update-settings" />
<input type="hidden" name="module" value="lancekb" />
<div class="block-wrapper">
	<div class="block">
	
			<div class="block-top">
					<div class="block-right">
							<div class="block-left"></div>
					</div>
			</div>
			
			<div class="block-header">' . $phrase['_settings'] . '</div>
			<div class="block-content-yellow" style="padding:9px"><div class="smaller">Configure this product using the settings below</div></div>
			<div class="block-content" style="padding:0px">
				<table cellpadding="12" cellspacing="0" width="100%">
				' . $html . '
				<tr class="alt2_top">
					<td><input type="submit" value="' . $phrase['_save'] . '" class="buttons" style="font-size:15px" /></td>
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

</form>';
                        
                        return $template;
                }
	}
    
	/**
        * Function to fetch and build the array of the category structure.  This will internally build our $ilance->categories->fetch[] array.
        * Additionally this function can sort the array using an internal sorting method if required.
        *
        * @param       string       short language identifier (default eng)
        * @param       bool         enable proper category/parent/child sorting on the fly? (default yes)
        *
        * @return      array        Returns category array structure
        */
        function build_array($slng = 'eng', $propersort = true)
        {
                global $ilance;

                $query = $ilance->db->query("
                        SELECT categoryid AS cid, catname AS title, parent AS parentid, adminaccess, description, sort
                        FROM " . DB_PREFIX . "kbcategory
                        ORDER BY sort ASC
                ", 0, null, __FILE__, __LINE__);
                while ($categories = $ilance->db->fetch_array($query, DB_ASSOC))
                {
                        $this->fetch["$slng"]["$categories[cid]"] = $categories;
                }                
                unset($categories);
                
                if ($propersort)
                {
                        return $this->propersort($slng);
                }
        }
        
        /**
        * Function to fetch the entire category cache and make it a proper formatted result set for various areas within ILance
        *
        * @param       string       short language identifier
        * @param       string       category type (service or product)
        * @param       integer      category mode (0 = all,  1 = portfolio, 2 = rss, 3 = newsletters)
        */
        function propersort($slng = 'eng')
        {
                $arr = array();
                
                if (!empty($this->fetch["$slng"]))
                {
                        foreach ($this->fetch["$slng"] AS $cid => $array)
                        {
                                $arr[] = $array;
                        }
                }
                
                return $arr;
        }
        
        /**
        * Function to generate the main header breadcrumb category trail.
        *
        * @param       integer      category id
        * @param       string       short language identifier (default eng)
        * @param       array        category cache (prevents hitting the db again)
        *
        * @return      array        Returns array $navcrumb breadcrumb trail
        */        
        function breadcrumb($cid = 0, $slng = 'eng', $categorycache = array())
        {
                global $ilance, $ilconfig, $navcrumb, $ilpage, $phrase;
                
                /*
                 Array
                (
                    [0] => Array
                        (
                            [cid] => 1
                            [parentid] => 0
                */
                
                $results = $categorycache;
                $resultscount = count($results);
                for ($i = 0; $i < $resultscount; $i++)
                {
                        if ($results[$i]['cid'] == $cid)
                        {
                                $this->breadcrumb($results[$i]['parentid'], $slng, $results);
                                
                                $title = $this->title($slng, $cid);
                                $parentid = $this->parentid($slng, $cid);
                                if ($ilconfig['globalauctionsettings_seourls'])
                                {
                                        $url = HTTP_KB . construct_seo_url_name(stripslashes($title)) . '-' . $cid . '-2.html';
                                        $navcrumb["$url"] = $title;
                                }
                                else
                                {
                                        $navcrumb[HTTPS_KB . '?cmd=2&amp;catid=' . intval($ilance->GPC['catid'])] = $title;   
                                }
                        }
                }
        }
        
        /**
        * Function to fetch the parentid of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      integer      Returns parentid of a category or 0 otherwise
        */
        function parentid($slng = 'eng', $cid = 0)
        {
                global $ilance;
                
                if (!empty($this->fetch["$slng"]["$cid"]))
                {
                        return $this->fetch["$slng"]["$cid"]['parentid'];
                }
                
                return 0;
        }
        
        /**
        * Function to fetch the title of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      category id
        *
        * @return      mixed        Returns category array structure (or All Categories) text otherwise
        */
        function title($slng = 'eng', $cid = 0)
        {
                global $phrase;
                
                if (!empty($this->fetch["$slng"]["$cid"]) OR !empty($this->fetch["$slng"]["$cid"]) AND $this->fetch["$slng"]["$cid"] != '0')
                {
                        return $this->fetch["$slng"]["$cid"]['title'];
                }
                
                return $phrase['_unknown'];
        }
        
        /*
        * Function to determine if a user is logged into their account or not
        *
        * @return      boolean      Returns true or false
        */
	function is_user_logged_in()
	{
		if (!empty($_SESSION['ilancedata']['user']['username']) AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/*
        * Function to construct and print out the LanceKB category pulldown menus
        *
        * @param       integer      category id
        * @param       integer      level id
        *
        * @return      string       HTML representation of the pulldown menu
        */
	function print_category_pulldown($catid = 0, $level)
	{
		global $ilance, $appendstr, $gbcatid;
		{
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "kbcategory
				WHERE categoryid = '" . intval($catid) . "'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($obj = $ilance->db->fetch_object($sql))
			{
				$sel = $spacer = '';
				if ($gbcatid == $catid)
				{
					$sel = ' selected="selected" ';
				}
				$appendstr = $appendstr . '<option ' . $sel . ' value="' . $catid . '">';
				
				for ($i = 0; $i < $level; $i++)
				{
					$spacer .= '&nbsp;&nbsp; ';
				}
				
				$appendstr = $appendstr . $spacer . $obj->catname . '</option>';
			}
		}
		$sql2 = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "kbcategory
			WHERE parent = '" . intval($catid) . "'
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		while ($obj2 = $ilance->db->fetch_object($sql2))
		{
			$catid = $obj2->categoryid;
			$this->print_category_pulldown($catid, ($level + 1));
		}
	}
	
	/*
        * Function to construct and print out the LanceKB category pulldown menus
        *
        * @param       integer      tblfolder_ref id (field used for attachtype = 'kb')
        *
        * @return      string       HTML representation of the attachment list
        */
	function fetch_attachment_list($id)
	{
		global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage, $SCRIPT_URL;
		
		$sql_attach = $ilance->db->query("
			SELECT attachid, filename, filesize
			FROM " . DB_PREFIX . "attachment
			WHERE tblfolder_ref = '" . intval($id) . "'
			AND attachtype = 'kb'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_attach) > 0)
		{
			while ($res_attach = $ilance->db->fetch_array($sql_attach))
			{
				$attach .= '<li><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" alt="' . $res_attach['filesize'] . ' ' . $phrase['_bytes'] . '"> ' . stripslashes($res_attach['filename']) . '&nbsp;&nbsp;<a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $res_attach['attachid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/arrowdown.gif" border="0" alt="" /></a>&nbsp;&nbsp;<a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_remove-article-attachment&amp;id=' . $res_attach['attachid'] . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')\"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a></li>';
			}
		}
		else
		{
			$attach = $phrase['_no_attachments'];
		}
		
		return $attach;
	}
	
	/*
        * Function to fetch variables and build a query string &x=y&y=z&etc=etc
        *
        * @return      string       HTML representation of the string based on variables being used
        */
	function fetch_variables()
	{
		global $_GET;
		
		$ret = '';
		    
		if (isset($_GET) AND is_array($_GET))
		{
			foreach ($_GET as $key => $value)
			{
				if ($ret != '')
				{
					$ret .= '&amp;';
				}
				
				if ($key != 'cmd')
				{
					$ret .= "$key=$value";
				}
			}
		}
		
		return $ret;
	}
	
	/*
        * Function to fetch row data from the datastore based on a provided SQL query string
        *
        * @param       string       sql code to execute
        * @param       string       search highlight
        *
        * @return      string       HTML representation of the row fetch
        */
	function fetch_rows($sql = '', $se = '')
	{
		global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
		
                $retstr = '';
                
                $count = 1;
                
		$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			$ilance->bbcode = construct_object('api.bbcode');
			
			while ($obj = $ilance->db->fetch_object($result))
			{
                                $retstr .= '<tr class="alt1"><td valign="top">';
				if ($obj->adminaccess == 'N')
				{
					$img = '<img border="0" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'article.gif" alt="" />';
				}
				else
				{
					$img = '<span title="' . $phrase['_subscribers_only'] . '"><img border="0" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/limited.gif" alt="' . $phrase['_subscribers_only'] . '" /></span>';
				}
				// article body
				$str = $obj->answer;
				$str = $ilance->bbcode->bbcode_to_html($str);
				$str = print_string_wrap($str, 70);
				
				if (mb_strlen($str) > 100)
				{
					// cut article body
					$str = mb_substr($str, 0, 200) . '...';
					$subj = stripslashes($obj->subject);
				}
				else
				{
                                        $subj = stripslashes($obj->subject);
				}
				
				$dose = 0;
				if (!empty($se))
				{
					$dose = 1;
					$subj = str_replace($se, '<span style="text-decoration:none; background-color:#ddeeff;">' . $se . '</span>', $subj);
					$subj = str_replace(ucfirst($se), '<span style="text-decoration:none; background-color:#ddeeff;">' . ucfirst($se) . '</span>', $subj);
					$subj = str_replace(mb_strtoupper($se), '<span style="text-decoration:none; background-color:#ddeeff;">' . mb_strtoupper($se) . '</span>', $subj);
					$subj = str_replace(mb_strtolower($se), '<span style="text-decoration:none; background-color:#ddeeff;">' . mb_strtoupper($se) . '</span>', $subj);
					$str = str_replace($se, '<span style="text-decoration:none; background-color:#ddeeff;">' . $se . '</span>', $str);
				}
				
				$str = str_replace("\n", "", $str);
                                $str = strip_tags($str);
		
				// author (if any)
				if (empty($obj->name))
				{
					$author = SITE_NAME;
				}
				else
				{
					$author = stripslashes($obj->name);
				}
				
				$comments = intval($ilance->db->num_rows($ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "kbcomments
					WHERE approved = '1'
						AND postsid = " . $obj->postsid . "
				")));
				$commentcount = '';
				if ($comments > 0)
				{
					$commentcount = '<span class="smaller black">(' . $comments . ' ' . mb_strtolower($phrase['_comments']) . ')</span>';
				}
		
                                $rank = '';
                                if (isset($ilance->GPC['pop']) AND $ilance->GPC['pop'] > 0)
                                {
                                        $rank = '<span style="font-size:15px" class="black"><strong>' . $this->rank_number($count) . '</strong></span>&nbsp;&nbsp;';
                                }
                
				if ($dose)
				{
					$retstr = ($this->config['enableseo'])
						? $retstr . $rank . '<span style="font-size:14px" class="blue"><a href="' . HTTP_KB . construct_seo_url_name(stripslashes($obj->subject)) . '-t' . $obj->postsid . '-4.html?se=' . $se . '">' . $subj . '</a></span> ' . $commentcount . ' <div class="gray" style="padding-top:5px">' . $str . '</div>'
						: $retstr . '<span style="font-size:14px" class="blue"><a href="' . HTTP_KB . '?cmd=4&amp;id=' . $obj->postsid . '&amp;se=' . $se . '">' . $subj . '</a></span> ' . $commentcount . ' <div class="gray" style="padding-top:5px">' . $str . '</div>';
				}
				else
				{
					$retstr = ($this->config['enableseo'])
						? $retstr . $rank . '<span style="font-size:14px" class="blue"><a href="' . HTTP_KB . construct_seo_url_name(stripslashes($obj->subject)) . '-t' . $obj->postsid . '-4.html">' . $subj . '</a></span> ' . $commentcount . ' <div class="gray" style="padding-top:5px">' . $str . '</div>'
						: $retstr . '<span style="font-size:14px" class="blue"><a href="' . HTTP_KB . '?cmd=4&amp;id=' . $obj->postsid . '">' . $subj . '</a></span> ' . $commentcount . ' <div class="gray" style="padding-top:5px">' . $str . '</div>';
				}
                                
				$retstr = $retstr . '<div style="padding-top:3px"></div>';
                                $retstr = $retstr . '</td></tr>';
                                
                                $count++;
			}
		}
		else
		{
			$retstr = '<tr class="alt1"><td colspan="2"><span class="gray">' . $phrase['_no_articles_found'] . '</span></td></tr>';
		}
		
		return $retstr;
	}
        
        function rank_number($n)
        {
                # Array holding the teen numbers. If the last 2 numbers of $n are in this array, then we'll add 'th' to the end of $n
                $teen_array = array(11, 12, 13, 14, 15, 16, 17, 18, 19);
                
                # Array holding all the single digit numbers. If the last number of $n, or if $n itself, is a key in this array, then we'll add that key's value to the end of $n
                $single_array = array(1 => 'st', 2 => 'nd', 3 => 'rd', 4 => 'th', 5 => 'th', 6 => 'th', 7 => 'th', 8 => 'th', 9 => 'th', 0 => 'th');
                
                # Store the last 2 digits of $n in order to check if it's a teen number.
                $if_teen = mb_substr($n, -2, 2);
                
                # Store the last digit of $n in order to check if it's a teen number. If $n is a single digit, $single will simply equal $n.
                $single = mb_substr($n, -1, 1);
                
                # If $if_teen is in array $teen_array, store $n with 'th' concantenated onto the end of it into $new_n
                if ( in_array($if_teen, $teen_array) )
                {
                    $new_n = $n . 'th';
                }
                # $n is not a teen, so concant the appropriate value of it's $single_array key onto the end of $n and save it into $new_n
                elseif ( $single_array[$single] )
                {
                    $new_n = $n . $single_array[$single];    
                }
                
                # Return new 
                return $new_n;
        }

	
	/*
        * Function to fetch related articles (based on keywords used in a specific article id being given as argument)
        *
        * @param       integer      article post id
        *
        * @return      string       HTML representation of the related articles
        */
	function fetch_related_articles($id = 0)
	{
		global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
		
		$keywords = '';
		
		$result = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "kbposts
			WHERE postsid = '" . intval($id) . "'
			AND approved = 1
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		while ($obj = $ilance->db->fetch_object($result))
		{
			$keywords = $obj->keywords;
		}
	
		$pieces = explode (',', $keywords);
		$query_limit = 3;
		$bool = " OR ";
		$selectquery = " SELECT * FROM " . DB_PREFIX . "kbposts ";
	
		// cycle through the search words-array if there are word(s) filled in
		if (isset($pieces))
		{
			$selectquery .= " WHERE postsid != '" . intval($id) . "' AND (";
			for ($i = 0; $i < count($pieces); $i++)
			{
				$selectquery .= " (answer LIKE '%" . $ilance->db->escape_string(trim($pieces[$i])) ."%' ) OR (keywords LIKE '%" . $ilance->db->escape_string(trim($pieces[$i])) ."%' )";
				if ($i < count($pieces) - 1)
				{
					$selectquery .= " $bool ";
				}
			}
			$selectquery .= " ) AND approved = 1 ORDER BY RAND() LIMIT " . $query_limit;
		}
		
		$cnt = 0;
		$result = $ilance->db->query($selectquery);
		while ($obj = $ilance->db->fetch_object($result))
		{
			if ($obj->adminaccess == 'N')
			{
				$img = '';
			}
			else
			{
				$img = '';
			}
			
			$str = $obj->answer;
			$str = str_replace("\n", "", $str);
			
			$im["$cnt"] = $img;
			$tex["$cnt"] = $str;
			
			$sub["$cnt"] = ($this->config['enableseo']) ? '<span class="blue"><a href="' . HTTP_KB . construct_seo_url_name(stripslashes($obj->subject)) . '-t' . $obj->postsid . '-4.html">' . stripslashes($obj->subject) . '</a></span>' : '<span class="blue"><a href="' . HTTP_KB . '?cmd=4&amp;id=' . $obj->postsid . '">' . mb_substr(stripslashes($obj->subject), 0, 50) . '</a></span>';
			
			$bg["$cnt"] = 'alt2';
			if ($cnt == 1)
			{
				$bg["$cnt"] = 'alt1';
			}
			$cnt++;
			if ($cnt == 3)
			{
				break;
			}
		}
		
		$ilance->bbcode = construct_object('api.bbcode');
			
		$retstr = '<tr align="left">';
		for ($i = 0; $i < $cnt; $i++)
		{
			$retstr .= '<td width="33.3%" valign="top"><div>' . $im[$i] . $sub[$i] . '</div>';
		}
		
		for ($i = $cnt; $i < 3; $i++)
		{
			$retstr .= '<td width="33.3%" valign="bottom">&nbsp;</td>';
		}
		
		$retstr .= '</tr>';
		
		if ($cnt == 0)
		{
			$retstr = '';
		}
		
		return $retstr;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_saved_list($savedart)
	{
		global $ilance, $phrase, $ilconfig, $page_title, $area_title, $ilconfig, $ilpage;
		
		$retstr = '';
		if (!empty($_COOKIE[COOKIE_PREFIX . 'savedarticles']))
		{
			$arr = explode('|', $_COOKIE[COOKIE_PREFIX . 'savedarticles']);
			$retstr .= '<ul>';
			
			for ($i = 0; $i < count($arr); $i++)
			{
				if ($this->config['enableseo'])
				{
					$retstr .= '<li><span class="blue"><a href="' . HTTP_KB . construct_seo_url_name(stripslashes($this->fetch_article_title_plain($arr[$i]))) . '-t' . $arr[$i] . '-4.html">' . stripslashes($this->fetch_article_title_plain($arr[$i])) . '</a></span></li>';
				}
				else 
				{
					$retstr .= '<li><span class="blue"><a href="' . HTTP_KB . '?cmd=4&amp;id=' . $arr[$i] . '">' . $this->fetch_article_title_plain($arr[$i]) . '</a></span></li>';	
				}
			}
			
			$retstr .= '</ul>';
			$retstr .= '<br /><br /><div><span class="blue"><a href="' . HTTP_KB . '?cmd=14&amp;clear=1">' . $phrase['_clear_my_article_list'] . '</a></span></div>';
		}
		else
		{
			$retstr = $phrase['_no_articles_saved_in_your_list'];
		}
		
		return $retstr;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_article_comments($sql = '', $id = 0)
	{
		global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
		
		$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		
		$ilance->bbcode = construct_object('api.bbcode');
		
		$count = 0;
		$retstr = '';
		while ($obj = $ilance->db->fetch_object($result))
		{
			$class = ($count % 2) ? 'alt2' : 'alt1';
			
			$retstr .= '
			<tr valign="top" class="">
				<td colspan="2" width="95%">
					<div><img border="0" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'quote.gif" alt="" /> <strong>' . stripslashes($ilance->common->xss_clean($obj->title)) . '</strong></div><div class="smaller gray">' . $phrase['_posted'] . ': ' . print_date($obj->insdate, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . ' ' . $phrase['_by'] . ': ' . $obj->name . '</div>
					<div style="padding-top:6px">' . $ilance->bbcode->bbcode_to_html($obj->content) . '</div>
				</td>
			</tr>';
			
			$count++;
		}
		
		if (empty($retstr))
		{
			return '<tr><td colspan="2"><span class="gray">' . $phrase['_no_comments'] . '</span></td></tr>';
		}
		
		return $retstr;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_kbaccess_level($id, $typekb = 1)
	{
		global $ilance;
		
		if ($typekb == 1)
		{
			$result = $ilance->db->query("
				SELECT adminaccess
				FROM " . DB_PREFIX . "kbcategory
				WHERE categoryid = '" . intval($id) . "'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
		}
		else
		{
			$result = $ilance->db->query("
				SELECT adminaccess
				FROM " . DB_PREFIX . "kbposts
				WHERE postsid = '" . intval($id) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($obj = $ilance->db->fetch_object($result))
		{
			return $obj->adminaccess;
		}
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_article_headings($sql)
	{
		global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
		
		$retstr = '';
		$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		
		while ($obj = $ilance->db->fetch_object($result))
		{
			if ($obj->adminaccess == 'N')
			{
				$img = '<img border="0" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'article.gif" />';
			}
			else
			{
				$img = '<img border="0" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/limited.gif" alt="' . $phrase['_subscribers_only'] . '" />';
			}
			
			$comments = intval($ilance->db->num_rows($ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "kbcomments
				WHERE approved = '1'
					AND postsid = " . $obj->postsid . "
			")));
			$commentcount = '';
			if ($comments > 0)
			{
				$commentcount = '<span class="smaller gray">(' . $comments . ' ' . mb_strtolower($phrase['_comments']) . ')</span>';
			}
			
			$retstr = ($this->config['enableseo']) ? $retstr . '<tr><td><a href="' . HTTP_KB . construct_seo_url_name(stripslashes($obj->subject)) . '-t' . $obj->postsid . '-4.html">' . stripslashes($obj->subject) . '</a> ' . $commentcount . '<div style="padding-bottom:4px"></div></td></tr>' : $retstr . '<tr><td><a href="' . HTTP_KB . '?cmd=4&amp;id=' . $obj->postsid . '"><strong>' . stripslashes($obj->subject) . '</strong></a> ' . $commentcount . '<div style="padding-bottom:4px"></div></td></tr>';
		}
		    
		if (empty($retstr))
		{
			$retstr = '<tr valign="top"><td height="22" colspan="2"><span class="gray">' . $phrase['_no_articles_found'] . '</span></td></tr>';
		}
		    
		return $retstr;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_kbcategory_name($id)
	{
		global $ilance;
		
		$retstr = '';
		
		$result = $ilance->db->query("
			SELECT catname
			FROM " . DB_PREFIX . "kbcategory
			WHERE categoryid = '" . intval($id) . "'
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		if ($obj = $ilance->db->fetch_object($result))
		{
			return $obj->catname;
		}
		
		return;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_article($id, $se = '')
	{
		global $ilance, $ilpage, $page_title, $area_title, $phrase;
		
		$ilance->bbcode = construct_object('api.bbcode');
		
		$retstr = '';
		
		$result = $ilance->db->query("
			SELECT subject, answer, html
			FROM " . DB_PREFIX . "kbposts
			WHERE postsid = '" . intval($id) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($obj = $ilance->db->fetch_object($result))
		{

                        if($id == 316)
            {
                  $post = '<div style="text-align: center; padding-top: 20px;"><img id="myImg" src="http://www.greatcollections.com/images/gc/Another-Old-Green-Holder-Collection-at-GreatCollections.jpg" title="Another Old Green Holder Collection at GreatCollections" width="400" height="496"></div>
                        <!-- The Modal -->
                        <div id="myModal" class="modal">
                          <span class="close">&times;</span>
                          <img class="modal-content" id="img01">
                          <div id="caption"></div>
                        </div>
                        <style>
                        #myImg {
                            border-radius: 5px;
                            cursor: pointer;
                            transition: 0.3s;
                        }

                        #myImg:hover {opacity: 0.7;}

                        /* The Modal (background) */
                        .modal {
                            display: none; /* Hidden by default */
                            position: fixed; /* Stay in place */
                            z-index: 1; /* Sit on top */
                            padding-top: 100px; /* Location of the box */
                            left: 0;
                            top: 0;
                            width: 100%; /* Full width */
                            height: 100%; /* Full height */
                            overflow: auto; /* Enable scroll if needed */
                            background-color: rgb(0,0,0); /* Fallback color */
                            background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
                        }

                        /* Modal Content (image) */
                        .modal-content {
                            margin: auto;
                            display: block;
                            width: 80%;
                            max-width: 700px;
                        }

                        /* Caption of Modal Image */
                        #caption {
                            margin: auto;
                            display: block;
                            width: 80%;
                            max-width: 700px;
                            text-align: center;
                            color: #ccc;
                            padding: 10px 0;
                            height: 150px;
                        }

                        /* Add Animation */
                        .modal-content, #caption {    
                            -webkit-animation-name: zoom;
                            -webkit-animation-duration: 0.6s;
                            animation-name: zoom;
                            animation-duration: 0.6s;
                        }

                        @-webkit-keyframes zoom {
                            from {-webkit-transform:scale(0)} 
                            to {-webkit-transform:scale(1)}
                        }

                        @keyframes zoom {
                            from {transform:scale(0)} 
                            to {transform:scale(1)}
                        }

                        /* The Close Button */
                        .close {
                            position: absolute;
                            top: 15px;
                            right: 35px;
                            color: #f1f1f1;
                            font-size: 40px;
                            font-weight: bold;
                            transition: 0.3s;
                        }

                        .close:hover,
                        .close:focus {
                            color: #bbb;
                            text-decoration: none;
                            cursor: pointer;
                        }

                        /* 100% Image Width on Smaller Screens */
                        @media only screen and (max-width: 700px){
                            .modal-content {
                                width: 100%;
                            }
                        }
                        </style>
                        <script>
                        // Get the modal
                        var modal = document.getElementById("myModal");

                        // Get the image and insert it inside the modal - use its "alt" text as a caption
                        var img = document.getElementById("myImg");
                        var modalImg = document.getElementById("img01");
                        var captionText = document.getElementById("caption");
                        img.onclick = function(){
                            modal.style.display = "block";
                            modalImg.src = this.src;
                            captionText.innerHTML = this.alt;
                        }

                        // Get the <span> element that closes the modal
                        var span = document.getElementsByClassName("close")[0];

                        // When the user clicks on <span> (x), close the modal
                        span.onclick = function() { 
                            modal.style.display = "none";
                        }
                        </script>';
            }
			$obj->subject = '<div style="padding-bottom:12px; padding-top:8px"><span style="font-size:19px; font-weight:bold">' . htmlspecialchars_uni(stripslashes($obj->subject)) . '</span><div class="smaller gray">' . $this->fetch_author(intval($id)) . '</div></div>';
			
			if ($obj->html)
			{
				// #### HTML ONLY ##############################
				$obj->answer = '<div>' . stripslashes($obj->answer) . '</div>';
			}
			else
			{
				// #### BBCODE / OTHER #########################
				$obj->answer = $ilance->bbcode->bbcode_to_html($obj->answer);
				$obj->answer = print_string_wrap($obj->answer.''.$post, 100);
			}
			
			if (!empty($se))
			{
				$obj->subject = str_replace($se, '<span style="text-decoration:none; background-color:#ddeeff;">' . $se . '</span>', $obj->subject);
				$obj->answer = str_replace($se, '<span style="text-decoration:none; background-color:#ddeeff;">' . $se . '</span>', $obj->answer);
			}
			
			$retstr = $obj->subject . $obj->answer  . $this->fetch_user_attachments($id);
		}
		else
		{
			$retstr = 'The article you are linking to no longer exists.';
		}
		
		return $retstr;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_author($id)
	{
		global $ilance, $ilpage, $ilconfig, $phrase;
		
		$name = '';
		
		$result = $ilance->db->query("
			SELECT name, email, moddate, numviews
			FROM " . DB_PREFIX . "kbposts
			WHERE postsid = '" . intval($id) . "'", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			if ($obj = $ilance->db->fetch_object($result))
			{
				if (!empty($obj->name))
				{
					$name = $obj->name . ' ' . $phrase['_on'] . ' ' . print_date($obj->moddate, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
				else
				{
					$name = SITE_NAME . ' ' . $phrase['_on'] . ' ' . print_date($obj->moddate, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
			}
		}
		
		return $name;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_article_title_plain($id)
	{
		global $ilance;
		
		$retstr = '';
		
		$result = $ilance->db->query("
			SELECT subject
			FROM " . DB_PREFIX . "kbposts
			WHERE postsid = '" . intval($id) . "'
		", 0, null, __FILE__, __LINE__);
		if ($obj = $ilance->db->fetch_object($result))
		{
			$retstr = stripslashes($obj->subject);
		}
		
		return $retstr;
	}
        
        /*
        * Function to fetch the description / body of an article
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_article_body_plain($id)
	{
		global $ilance;
		
                $ilance->bbcode = construct_object('api.bbcode');
                
		$html = '';
		
		$result = $ilance->db->query("
			SELECT answer, html
			FROM " . DB_PREFIX . "kbposts
			WHERE postsid = '" . intval($id) . "'
		", 0, null, __FILE__, __LINE__);
		if ($obj = $ilance->db->fetch_object($result))
		{
			if ($obj->html)
			{
				// #### HTML ONLY ##############################
				$obj->answer = stripslashes($obj->answer);
                                $html .= $obj->answer;
			}
			else
			{
				// #### BBCODE / OTHER #########################
				$obj->answer = $ilance->bbcode->bbcode_to_html($obj->answer);
				$obj->answer = print_string_wrap($obj->answer, 100);
                                
                                $html .= $obj->answer;
			}
		}
		
		return $html;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_article_title($id)
	{
		global $ilance;
		
		$retstr = '';
		
		$result = $ilance->db->query("
			SELECT subject
			FROM " . DB_PREFIX . "kbposts
			WHERE postsid = '" . intval($id) . "'
		", 0, null, __FILE__, __LINE__);
		if ($obj = $ilance->db->fetch_object($result))
		{
			$retstr = '<strong>' . stripslashes($obj->subject) . '</strong>';
		}
		
		return $retstr;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_kbcategories()
	{
		global $ilance;
		
		$retstr = '';
		$cnt = 0;
		
		$result = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "kbcategory
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		while ($obj = $ilance->db->fetch_object($result))
		{
			$retstr .= '<option value="' . $obj->categoryid . '">' . stripslashes($obj->catname) . '</option>';
		}
		
		return $retstr;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_kbsubcategories($catid = 0)
	{
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                $result = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "kbcategory
                        WHERE parent = '" . intval($catid) . "'
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($result) > 0)
                {
                        $retstr = '';
                        $cnt = $cols = 0;
                        $columns = 3;
                        
                        while ($obj = $ilance->db->fetch_object($result))
                        {
				$img = '';
                                if ($obj->adminaccess == 'N')
                                {
                                        $img = '';
                                }
                                else
                                {
					if (empty($_SESSION['ilancedata']['user']['userid']))
					{
						$img = '<img border="0" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/limited.gif" alt="' . $phrase['_subscribers_only'] . '" />';
					}
                                }
                                
                                $imarr["$cnt"] = $img;
                                /*$numquestions = intval($ilance->db->num_rows($ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "kbposts
                                        WHERE approved = '1'
                                                AND catid = " . $obj->categoryid . "
                                ")));*/
				$numquestions = $this->print_article_category_count($obj->categoryid);
                                
				$alink["$cnt"] = ($this->config['enableseo'])
					? '<span class="blue" style="font-size:14px"><a href="' . HTTP_KB . construct_seo_url_name(stripslashes($obj->catname)) . '-' . $obj->categoryid . '-2.html">' . stripslashes($obj->catname) . '</a></span>&nbsp;<span class="smaller gray">(' . $numquestions . ')</span> ' . $imarr["$cnt"]
					: '<span class="blue" style="font-size:15px"><a href="' . HTTP_KB . '?cmd=2&amp;catid=' . $obj->categoryid . '">' . stripslashes($obj->catname) . '</a></span>&nbsp;<span class="gray">(' . $numquestions . ')</span> ' . $imarr["$cnt"];
                                
				$small["$cnt"] = stripslashes($obj->description);
                                
                                if ($cols == 0)
                                {
                                       $retstr .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                }
                                
                                $retstr .= '<td width="30%" valign="top"><div>' . $alink["$cnt"] . '</div><div class="smaller gray">' . $small["$cnt"] . '</div></td>';
				
                                $cols++;
                                $cnt++;
                                
                                if ($cols == $columns)
                                {
                                        $retstr .= '</tr>';
                                        $cols = 0;
                                }
                        }
                        
                        if ($cols != $columns && $cols != 0)
                        {
                                $neededtds = $columns - $cols;
                                for ($i = 0; $i < $neededtds; $i++)
                                {
                                        $retstr .= '<td></td>';
                                }
                                
                                $retstr .= '</tr>'; 
                        }
                        
                        return $retstr;
                }
                
                return '';
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function fetch_ratings($id)
	{
		global $ilance, $phrase, $iltemplate, $ilconfig, $ilpage;
		
		$result1 = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "kbratings
			WHERE postsid = '" . intval($id) . "'
		");
		if (!$ilance->db->num_rows($result1))
		{
			$n = 5;
			$rating = '';
			for ($i=0; $i<$n; $i++)
			{
				$rating .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/starn.gif" border="0" alt="" />';
			}
		}
		else
		{
			$result1 = $ilance->db->query("
				SELECT AVG(rating) AS average
				FROM " . DB_PREFIX . "kbratings
				WHERE postsid = '" . intval($id) . "'
			");
			if ($ilance->db->num_rows($result1) > 0)
			{
				$res = $ilance->db->fetch_array($result1);
			}
			$rating = "";
	    
			for ($i=0; $i<floor($res['average']); $i++)
				$rating .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/starp.gif" border="0" alt="" />';
				$numleft = 5-$i;
				if (($res['average']-$i)>=0.5)
				{
					    $numleft = $numleft-1;
					    $rating .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/starm.gif" border="0" alt="" />';
				}
				
				for ($i=0; $i<$numleft; $i++)
					    $rating .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/starn.gif" border="0" alt="" />';
		}
		return $rating;
	}
	
	/*
        * Function to display the knowledge base category structure within the AdminCP
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_categories($catid, $level, $mode = '')
	{
		global $ilance, $phrase, $v3template, $iltemplate, $page_title, $area_title, $ilconfig, $ilpage, $show, $headerinclude;
		
		if (isset($mode) AND $mode == 'header')
		{
			$html = '
<form method="post" action="' . $ilpage['components'] . '" accept-charset="UTF-8" style="margin:0px">
<input type="hidden" name="cmd" value="components" />
<input type="hidden" name="subcmd" value="updatesort" />
<input type="hidden" name="module" value="lancekb" />
<div class="block-wrapper">
        <div class="block">
        
                        <div class="block-top">
                                        <div class="block-right">
                                                        <div class="block-left"></div>
                                        </div>
                        </div>
                        
                        <div class="block-header">' . $phrase['_categories'] . '</div>
                        <!--<div class="block-content-yellow" style="padding:9px"><div class="smaller"></div></div>-->
                        <div class="block-content" style="padding:0px">
                                
			<table width="100%" cellpadding="9" cellspacing="0">
			<tr class="alt2">
				<td width="57%" align="left" nowrap="nowrap">' . $phrase['_category_title'] . '</td>
				<td width="10%" align="center">' . $phrase['_sort'] . '</td>
				<td width="10%" align="center" nowrap="nowrap">' . $phrase['_unapproved_articles'] . '</td>
				<td width="7%" align="center" nowrap="nowrap">' . $phrase['_unapproved_comments'] . '</td>
                                <td width="16%" align="center" nowrap="nowrap">' . $phrase['_members_only'] . '</td>
				<td width="10%" align="center">' . $phrase['_action'] . '</td>
			</tr>';
			
			return $html;
		}
		else if (isset($mode) AND $mode == 'footer')
		{
			$html = '
			<tr>
			    <td colspan="6"><input type="submit" name="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" /></td>
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
</form>';
			return $html;
		}
		else
		{
			global $appendstr;
			{
				$catid = intval($catid);
				
				$result = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "kbcategory
					WHERE categoryid = '" . intval($catid) . "'
					ORDER BY sort ASC
				");
				if ($obj = $ilance->db->fetch_object($result))
				{
					$bg = 'alt2_top';
					$bg2 = $actions = '';
					$strong1 = '<strong>';
					$strong2 = '</strong>';
					
					if ($level != 1)
					{
						$bg = 'alt1';
						$bg2 = $strong1 = $strong2 = $actions = '';
					}
					
					$spacer = '';
					for ($i = 0; $i < $level - 1; $i++)
					{
						if (empty($spacer))
						{
							$spacer = $spacer . '--&nbsp;';
						}
						else
						{
							$spacer = $spacer . '--&nbsp;';
						}
					}
					
					$appendstr = $appendstr . '<tr class="' . $bg . '" valign="top">';
					$appendstr = $appendstr . '<td><div>&nbsp;' . $bg2 . $strong1 . $spacer . '<span class="blue"><a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_edit-category&amp;catid=' . $obj->categoryid . '&amp;parentid=' . $obj->parent . '&amp;external=1">' . $obj->catname . '</a></span>' . $strong2 . '&nbsp;<span class="smaller gray">&nbsp;' . $phrase['_articles'] . ': <span class="smaller">' . $this->print_article_category_count($catid) . '</span>, ' . $phrase['_comments'] . ': <span class="smaller">' . $this->print_article_comments_count($catid) . '</span></div></td>';
					$appendstr .= '<td align="center"><input type="text" name="sort[' . $obj->categoryid . ']" value="' . $obj->sort . '" style="width:30px; text-align:center; font-family: verdana" /></td>';
					$appendstr .= '<td align="center"><div class="smaller">' . $this->print_pending_article_category_count($catid) . '</div></td>';
					$appendstr .= '<td align="center"><div class="smaller">' . $this->print_pending_article_comments_count($catid) . '</div></td>';
					$appendstr .= '<td align="center">';
					if ($obj->adminaccess == 'Y')
					{
						$appendstr .= '<a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_membersonly-disable&amp;id='.$obj->categoryid.'"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to disable members-only access" border="0" /></a>';
					}
					else
					{
						$appendstr .= '<a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_membersonly-enable&amp;id='.$obj->categoryid.'"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to activate members-only access" border="0" /></a>';
					}
					$appendstr .= '</td>';
					$appendstr .= '<td align="center" nowrap="nowrap"><a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_edit-category&amp;catid=' . $obj->categoryid . '&amp;parentid=' . $obj->parent . '&amp;external=1"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_remove-category&amp;catid=' . $obj->categoryid . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a></td>';
					$appendstr .= '</tr>';
				}
			}
			
			$result2 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "kbcategory
				WHERE parent = '" . $catid . "'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			while ($obj2 = $ilance->db->fetch_object($result2))
			{
				$this->display_categories($obj2->categoryid, ($level + 1));
			}
		}
	}
	
	/*
        * Function to display the category edit menu
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_edit_category()
	{
		global $ilance, $phrase, $ilpage, $appendstr, $ilconfig;
		
		$catid = (isset($ilance->GPC['catid'])) ? intval($ilance->GPC['catid']) : 0;
		
		$result = $ilance->db->query("
			SELECT catname, parent, description
			FROM " . DB_PREFIX . "kbcategory
			WHERE categoryid = '" . intval($catid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($object = $ilance->db->fetch_object($result))
		{
			$catname = $object->catname;
			$parentid = $object->parent;
			$desc = $object->description;
		}
		
		$appendstr  = '';
		$html = '
<form method="post" action="' . $ilpage['components'] . '" name="updatecategory" accept-charset="UTF-8" style="margin: 0px;">
<input type="hidden" name="cmd" value="components" />
<input type="hidden" name="module" value="lancekb" />
<input type="hidden" name="subcmd" value="_update-category" />
<input type="hidden" name="catid" value="' . intval($catid) . '" />
<div class="block-wrapper">
<div class="block">
        <div class="block-top">
		<div class="block-right">
			<div class="block-left"></div>
		</div>
        </div>
        
        <div class="block-header">' . $phrase['_update'] . ' ' . $phrase['_category'] . ': ' . handle_input_keywords($catname) . '</div>
        <!--<div class="block-content-yellow" style="padding:9px"><div class="smaller"></div></div>-->
        <div class="block-content" style="padding:0px">
                <table width="100%" border="0" cellspacing="1" cellpadding="9">
		<tr class="alt1">
			<td><span class="gray">' . $phrase['_parent_category'] . '</span></td>
			<td><select name="parent" id="select" style="font-family: verdana"><option value="0">' . $phrase['_none'] . '</option>' . $this->display_category_update_pulldown(0, '') . $appendstr . '</select></td>
		</tr>
		<tr class="alt1">
			<td width="27%" align="left"><span class="gray">' . $phrase['_category_name'] . '</span></td>
			<td width="73%"><input type="text" name="catname" class="input" style="width:500px; font-family:Verdana" value="' . handle_input_keywords($catname) . '"></td>
		</tr>
		<tr class="alt1">
			<td valign="top"><span class="gray">' . $phrase['_description'] . '</span></td>
			<td align="left"><textarea name="desc" style="width: 500px; height: 54px; font-family:Verdana" wrap="physical">' . handle_input_keywords($desc) . '</textarea></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="' . $phrase['_save'] . '" style="font-size:15px" class="buttons" /> &nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['components'] . '?module=lancekb">' . $phrase['_cancel'] . '</a></span></td>
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
</form>';
		
		return $html;
	}
	
	/*
        * Function to display the add new category menu
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_add_category()
	{
		global $ilance, $appendstr, $phrase, $ilconfig, $ilpage;
		
		$appendstr = '';
                
		$html = '
<form method="post" action="' . $ilpage['components'] . '" name="addcategory" accept-charset="UTF-8" style="margin: 0px;">
<input type="hidden" name="cmd" value="components" />
<input type="hidden" name="module" value="lancekb" />
<input type="hidden" name="subcmd" value="_add-category" />
<div class="block-wrapper">
	<div class="block">
	
			<div class="block-top">
					<div class="block-right">
							<div class="block-left"></div>
					</div>
			</div>
			
			<div class="block-header">' . $phrase['_create_a_new_category'] . '</div>
			<!--<div class="block-content-yellow" style="padding:9px"><div class="smaller"></div></div>-->
			<div class="block-content" style="padding:0px">
				
                                <table width="100%" border="0" cellspacing="1" cellpadding="9">
                                <tr class="alt1">
                                        <td><span class="gray">' . $phrase['_parent_category'] . '</span></td>
                                        <td><select name="parent" id="select" style="font-family: verdana"><option value="0">' . $phrase['_none'] . '</option>' . $this->display_category_pulldown(0, '', 0) . $appendstr . '</select></td>
                                </tr>
                                <tr class="alt1">
                                        <td width="27%" align="left"><span class="gray">' . $phrase['_category_name'] . '</span></td>
                                        <td width="73%"><input type="text" name="catname" style="width:500px" /></td>
                                </tr>
                                <tr class="alt1">
                                        <td valign="top"><span class="gray">' . $phrase['_description'] . '</span></td>
                                        <td align="left"><textarea name="desc" style="width: 500px; height: 54px; font-family: verdana" wrap="physical"></textarea></td>
                                </tr>
                                <tr>
                                        <td colspan="2"><input type="submit" value=" ' . $phrase['_save'] . ' " style="font-size:15px" class="buttons" /></td>
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
</form>';
		
		return $html;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_add_article()
	{
		global $ilance, $appendstr, $phrase, $ilconfig, $ilpage;
		
		$appendstr = '';
		$html = '
<script language="JavaScript">
<!--
function validate_message()
{
        fetch_bbeditor_data();
        return(true);
}
function validate_all()
{	
        return validate_message(); 
}
// -->
</script>
		
<form name="ilform" method="post" action="'.$ilpage['components'] . '" enctype="multipart/form-data" accept-charset="UTF-8" onsubmit="return validate_all();" style="margin:0px;">
<input type="hidden" name="cmd" value="components" />
<input type="hidden" name="module" value="lancekb" />
<input type="hidden" name="subcmd" value="_add-article" />
<div class="block-wrapper">
        <div class="block">
        
                        <div class="block-top">
                                        <div class="block-right">
                                                        <div class="block-left"></div>
                                        </div>
                        </div>
                        
                        <div class="block-header">' . $phrase['_new'] . ' ' . $phrase['_article'] . '</div>
                        <div class="block-content-yellow" style="padding:9px"><div class="smaller">' . $phrase['_create_a_new_article_in_the_knowledge_base'] . '</div></div>
                        <div class="block-content" style="padding:0px">
                           
                        <table border="0" cellspacing="1" cellpadding="9">
                        <tr class="alt1">
                                <td><span class="gray">' . $phrase['_category'] . '</span></td>
                                <td><select name="catid" style="font-family: verdana">'.$this->display_category_update_pulldown(0, '') . $appendstr .'</select></td>
                        </tr>
                        <tr class="alt1">
                                <td valign="top"><span class="gray">' . $phrase['_subject'] . '</span></td>
                                <td ><input type="text" name="subject" style="width: 98%; font-family:Verdana" id="subject" /></td>
                        </tr>
                        <tr class="alt1">
                                <td valign="top"><span class="gray">' . $phrase['_message'] . '</span></td>
                                <td>' . print_wysiwyg_editor('message', '', 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']) . '</td>
                        </tr>
                        <tr class="alt1">
                                <td><span class="gray">' . $phrase['_keywords'] . '</span></td>
                                <td><input type="text" name="keywords" style="width: 98%; font-family: Verdana" /><br /><span class="gray">Keywords Seperate by commas; also used for related search results</span></td>
                        </tr>
                        <tr class="alt1">
                                <td width="27%" align="left"><span class="gray">' . $phrase['_author'] . '</span></td>
                                <td width="73%"><input type="text" name="name" style="width:98%; font-family: Verdana" /></td>
                        </tr>
                        <tr class="alt1">
                                <td><span class="gray">' . $phrase['_author_email'] . '</span></td>
                                <td><input type="text" name="email" style="width:98%; font-family: Verdana" /></td>
                        </tr>
                        <tr class="alt1">
                                <td><span class="gray">' . $phrase['_approved'] . '</span></td>
                                <td><input type="checkbox" name="approved" value="1" checked="checked" /> Click this checkbox if you would like to approve this article.</td>
                        </tr>
                        <!--
                        <tr class="alt1">
                                <td><span class="gray">' . $phrase['_attachments'] . '</span></td>
                                <td>'.$this->fetch_attachment_list('').'</td>
                        </tr>
                        //-->
                        <tr class="alt1">
                                <td><span class="gray">' . $phrase['_members'] . '</span></td>
                                <td><input type="radio" name="adminaccess" value="Y" />' . $phrase['_yes'] . ' <input name="adminaccess" type="radio" value="N" checked="checked" /> ' . $phrase['_no'] . '</td>
                        </tr>
                        <tr>
                                <td colspan="2"><input type="submit" value=" '. $phrase['_save'] . ' " class="buttons" style="font-size:15px" /> &nbsp;&nbsp;&nbsp;<span class="blue"><a href="'.$ilpage['components'] . '?module=lancekb">' . $phrase['_cancel'] . '</a></span></td>
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
</form>';
		return $html;
	}
	
	/*
        * Function to fetch the articles and print out the display for the AdminCP
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_articles()
	{
		global $ilance, $appendstr, $phrase, $ilpage, $show, $headinclude, $ilconfig;
		
		$keywords = isset($ilance->GPC['q']) ? $ilance->GPC['q'] : '';
		
		$page = 1;
		if (isset($ilance->GPC['page']) AND $ilance->GPC['page'] > 0)
		{
			$page = intval($ilance->GPC['page']);
		}
		
		$catid = 0;
		if (isset($ilance->GPC['catid']) AND $ilance->GPC['catid'] > 0)
		{
			$catid = intval($ilance->GPC['catid']);
		}
		$gbcatid = $catid;

		// inline auction ajax controls
		$headinclude .= "
<script type=\"text/javascript\">
<!--
var searchid = 0;
var value = '';
var imgtag = '';
var favoriteicon = '';
var status = '';
function reset_image()
{
	imgtag.src = favoriteicon;
}
function fetch_response()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
	{
		// format response
		response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
		phpstatus = xmldata.fetch_data(response);
		
		searchiconsrc = fetch_js_object('inline_article_' + xmldata.searchid + '').src;
		status = searchiconsrc.match(/\/unchecked.gif/gi);
		if (status == '/unchecked.gif')
		{
		       status = 'unchecked';
		}
		else
		{
		       status = 'checked';
		}                                
		if (status == 'unchecked')
		{
			if (phpstatus == 'on' || phpstatus == 'off')
			{
				favoriteiconsrc = fetch_js_object('inline_article_' + xmldata.searchid + '').src;
				imgtag = fetch_js_object('inline_article_' + xmldata.searchid + '');
				
				favoriteicon2 = favoriteiconsrc.replace(/unchecked.gif/gi, 'working.gif');
				imgtag.src = favoriteicon2;
				
				favoriteicon = favoriteiconsrc.replace(/unchecked.gif/gi, 'checked.gif');
				var t = window.setTimeout('reset_image()', 700);
			}
			else
			{
				alert(phpstatus);
			}
		}
		else if (status == 'checked')
		{
			if (phpstatus == 'on' || phpstatus == 'off')
			{
				favoriteiconsrc = fetch_js_object('inline_article_' + xmldata.searchid + '').src;
				imgtag = fetch_js_object('inline_article_' + xmldata.searchid + '');
				
				favoriteicon2 = favoriteiconsrc.replace(/checked.gif/gi, 'working.gif');
				imgtag.src = favoriteicon2;
	
				favoriteicon = favoriteiconsrc.replace(/checked.gif/gi, 'unchecked.gif');
				var t = window.setTimeout('reset_image()', 700);
			}
			else
			{
				alert(phpstatus); 
			}
		}
		xmldata.handler.abort();
	}
}
function update_article(searchid)
{                        
	// set ajax handler
	xmldata = new AJAX_Handler(true);
	
	// url encode the vars
	searchid = urlencode(searchid);
	xmldata.searchid = searchid;
	
	searchiconsrc = fetch_js_object('inline_article_' + searchid + '').src;
	status = searchiconsrc.match(/\/unchecked.gif/gi);
	if (status == '/unchecked.gif')
	{
	       value = 'on';
	}
	else
	{
	       value = 'off';
	}
	xmldata.onreadystatechange(fetch_response);
	
	// send data to php
	xmldata.send('../ajax.php', 'do=kbajaxarticles&value=' + value + '&id=' + searchid + '&s=' + ILSESSION + '&token=' + ILTOKEN);                        
}
//-->
</script>
";
		$appendstr = '';
		$html = '
<div style="padding-bottom:6px; padding-top:6px">
<form method="get" action="'.$ilpage['components'] . '" accept-charset="UTF-8" style="margin: 0px;">
<input type="hidden" name="cmd" value="components" />
<input type="hidden" name="module" value="lancekb" />
<table width="100" style="width:160px">
<tr>
<td width="1%" nowrap="nowrap"><strong>' . $phrase['_category_filter'] . '</strong>&nbsp;</td><td width="1%" nowrap="nowrap"><select name="catid" id="catid" style="font-family: verdana"><option value="">' . $phrase['_all_categories'] . '</option>' . $this->display_category_pulldown(0, '', $catid) . $appendstr . '</select>&nbsp;&nbsp;</td><td width="1%" nowrap="nowrap"><strong>' . $phrase['_keywords'] . '</strong>&nbsp;</td><td width="1%" nowrap="nowrap"><input type="text" name="q" value="' . handle_input_keywords($keywords) . '" /></td><td width="1%" nowrap="nowrap">&nbsp;<input type="submit" value="' . $phrase['_filter'] . '" class="buttons" /></td>
</tr>
</table>
</form>
</div>
                
<div class="block-wrapper">
<div class="block">
                <div class="block-top">
			<div class="block-right">
				<div class="block-left"></div>
			</div>
                </div>
                <div class="block-header">' . $phrase['_articles'] . '</div>
                <div class="block-content" style="padding:0px">
                        <table width="100%" cellpadding="9" cellspacing="1">
                        <tr class="alt2">
                                <td width="50%" align="left">' . $phrase['_article_topic'] . '</td>
                                <td width="20%" align="left">' . $phrase['_category'] . '</td>
				<td width="1%" align="left">' . $phrase['_views'] . '</td>
                                <td width="10%">' . $phrase['_approved'] . '</td>
                                <td width="10%" nowrap="nowrap" align="center">' . $phrase['_rated'] . '</td>
                                <td width="10%" align="center">' . $phrase['_action'] . '</td>
                        </tr>
                        ' . $this->display_article_listings(intval($catid), intval($page), $keywords) . '
                </div>
                <div class="block-footer">
                                <div class="block-right">
                                                <div class="block-left"></div>
                                </div>
                </div>
</div>
</div>';
		
		return $html;
	}
	
	/*
        * Function to display the article results row by row from the AdminCP
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_article_listings($id, $page, $keywords = '')
	{
		global $ilance, $phrase, $iltemplate, $v3template, $page_title, $area_title, $ilconfig, $ilpage, $show;
		
		if (!isset($page))
		{
			$page = 1;
		}
		$limit = ' ORDER BY postsid DESC LIMIT ' . (($page - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		
		$extra1 = $extra2 = '';
		if ($id > 0)
		{
			$extra1 = "AND catid = '" . intval($id) . "'";
		}
		if (!empty($keywords))
		{
			$extra2 = "AND (subject LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR answer LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR keywords LIKE '%" . $ilance->db->escape_string($keywords) . "%')";
		}
		
		$sqlmsg  = "
			SELECT *
			FROM " . DB_PREFIX . "kbposts
			WHERE postsid > 0
			$extra1
			$extra2
			$limit
		";
		$sqlmsg2 = "
			SELECT *
			FROM " . DB_PREFIX . "kbposts
			WHERE postsid > 0
			$extra1
			$extra2
		";
		
		$numberrows = $ilance->db->query($sqlmsg2);
		$number = $ilance->db->num_rows($numberrows);
		
		$counter = ($page - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	
		$result = $ilance->db->query($sqlmsg);
		$return = '';
		if ($ilance->db->num_rows($result) > 0)
		{
			$rows = 0;
			while ($resmsg = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				$class = ($rows % 2) ? 'alt1' : 'alt1';
				$return .= '<tr class="' . $class . '" valign="top">';
				$return .= '<td align="left"><span class="blue"><a href="' . HTTP_KB . '?cmd=4&amp;id='.$resmsg['postsid'] . '" target="_blank">' . handle_input_keywords($resmsg['subject']) . '</a></span><br /><span class="smaller">' . $phrase['_added'] . ': <strong>' . print_date($resmsg['insdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . '</strong></span></td>';
				$return .= '<td>' . handle_input_keywords($ilance->db->fetch_field(DB_PREFIX."kbcategory", "categoryid = '" . $resmsg['catid'] . "'", "catname")) . '</td>';
				$return .= '<td><div class="smaller">' . number_format($resmsg['numviews']) . '</div></td>';
				$return .= '<td>';
				if ($resmsg['approved'])
				{
					$return .= '<span title="Click to Unapprove"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="Click to Unapprove" border="0" id="inline_article_' . $resmsg['postsid'] . '" onclick="update_article(\'' . $resmsg['postsid'] . '\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span>';
				}
				else
				{
					$return .= '<span title="Click to Approve"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="Click to Approve" border="0" id="inline_article_' . $resmsg['postsid'] . '" onclick="update_article(\'' . $resmsg['postsid'] . '\');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span>';
				}
				$return .= '</td>';
				$return .= '<td align="center" nowrap="nowrap">' . $this->print_article_star_ratings($resmsg['postsid']) . '</td>';
				$return .= '<td align="center"><a href="' . HTTPS_SERVER_ADMIN . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_update-article&amp;id=' . $resmsg['postsid'] . '&amp;parentid='.$resmsg['catid'] . '&amp;external=1" title="' . $phrase['_update'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="' . $phrase['_update'] . '"></a>&nbsp;&nbsp;<a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_remove-article&amp;id=' . $resmsg['postsid'] . '" onClick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')" title="' . $phrase['_remove'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="' . $phrase['_remove'] . '" /></a></td>';
				$return .= '</tr>';
				$rows++;
			}
		}
		
		$return .= '</table><div style="padding-top:6px">' . print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $page, $counter, $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;catid=' . intval($id)) . '</div>';
		
		return $return;
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_category_update_pulldown($catid, $level)
	{
		global $ilance;
		
                $parentid = 0;
		if (isset($ilance->GPC['parentid']))
		{
			$parentid = intval($ilance->GPC['parentid']);
		}
		
		global $ilance, $appendstr, $origid;
		{
			$result = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "kbcategory
				WHERE categoryid = '" . intval($catid) . "'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($obj = $ilance->db->fetch_object($result))
			{
			    $sel = "";
	    
			    if ($parentid == $obj->categoryid) $sel = ' selected="selected" ';
			    $appendstr = $appendstr . '<option value="' . $catid . '" ' . $sel . '>';
			    
			    $spacer = "";
			    
			    for ($i=0; $i<$level; $i++)
			    $spacer = $spacer . "&nbsp;&nbsp;&nbsp;&nbsp;";
			    $appendstr = $appendstr . $spacer . stripslashes($obj->catname) . "</option>\n";
			}
		}
		
		$result = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "kbcategory
			WHERE parent = '" . intval($catid) . "'
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		while ($obj = $ilance->db->fetch_object($result))
		{
			$catid = $obj->categoryid;
			$this->display_category_update_pulldown(intval($catid), ($level + 1));
		}
	}
	
	/*
        * Function to
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_category_pulldown($catid = 0, $level, $selected = 0)
	{
		global $ilance, $appendstr;
		{
			$result = $ilance->db->query("
				SELECT catname
				FROM " . DB_PREFIX . "kbcategory
				WHERE categoryid = '" . intval($catid) . "'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($obj = $ilance->db->fetch_object($result))
			{
				if (isset($selected) AND $selected == $catid)
				{
					$appendstr = $appendstr . '<option value="' . intval($catid) . '" selected="selected">';	
				}
				else
				{
					$appendstr = $appendstr . '<option value="' . intval($catid) . '">';
				}
				
				
					
				$spacer = '';
				
				for ($i=0; $i<$level; $i++)
				$spacer = $spacer . "&nbsp;&nbsp;&nbsp;";
				$appendstr = $appendstr . $spacer . $obj->catname . "</option>\n";
			}
		}
	
		$result = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "kbcategory
			WHERE parent = '" . intval($catid) . "'
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		while ($obj = $ilance->db->fetch_object($result))
		{
			$catid = $obj->categoryid;
			$this->display_category_pulldown(intval($catid), ($level + 1), $selected);
		}
	}
	
	/*
        * Function to fetch
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_comments()
	{
		global $ilance, $phrase, $iltemplate, $v3template, $page_title, $area_title, $ilconfig, $ilpage, $show;
		
		$mode = isset($ilance->GPC['mode']) ? $ilance->GPC['mode'] : '';
		$page = (isset($ilance->GPC['page']) AND $ilance->GPC['page'] > 0) ? intval($ilance->GPC['page']) : 1;
		$keywords = isset($ilance->GPC['q']) ? $ilance->GPC['q'] : '';
		
		if (!isset($page))
		{
			$page = 1;
		}
		
		$modeselect = '';
		if ($mode == 'approved')
		{
			$modeselect = '<option value="">All comments</option><option value="approved" selected="selected">Approved comments</option><option value="unapproved">Unapproved comments</option>';
		}
		else if ($mode == 'unapproved')
		{
			$modeselect = '<option value="">All comments</option><option value="approved">Approved comments</option><option value="unapproved" selected="selected">Unapproved comments</option>';
		}
		else
		{
			$modeselect = '<option value="" selected="selected">All comments</option><option value="approved">Approved comments</option><option value="unapproved">Unapproved comments</option>';
		}
		
		$html = '
		<div style="padding-bottom:6px; padding-top:6px">
		<form method="get" action="'.$ilpage['components'] . '" accept-charset="UTF-8" style="margin: 0px;">
		<input type="hidden" name="cmd" value="components" />
		<input type="hidden" name="module" value="lancekb" />
		<table width="100" style="width:160px">
		<tr>
		<td width="1%" nowrap="nowrap"><strong>' . $phrase['_type'] . '</strong>&nbsp;</td><td width="1%" nowrap="nowrap"><select name="mode" id="mode" style="font-family: verdana">' . $modeselect . '</select>&nbsp;&nbsp;</td><td width="1%" nowrap="nowrap"><strong>' . $phrase['_keywords'] . '</strong>&nbsp;</td><td width="1%" nowrap="nowrap"><input type="text" name="q" value="' . handle_input_keywords($keywords) . '" /></td><td width="1%" nowrap="nowrap">&nbsp;<input type="submit" value="' . $phrase['_filter'] . '" class="buttons" /></td>
		</tr>
		</table>
		</form>
		</div>
                <div class="block-wrapper">
                        <div class="block">
                                        <div class="block-top">
						<div class="block-right">
							<div class="block-left"></div>
						</div>
                                        </div>
                                        <div class="block-header">' . $phrase['_comments'] . '</div>
                                        <!--<div class="block3-content-gray" style="padding:9px"><div class="smaller"></div></div>-->
                                        <div class="block-content" style="padding:0px">
                                                
                                        <table width="100%" cellpadding="9" cellspacing="1">
                                        <tr class="alt2">
                                                <td width="34%">' . $phrase['_subject'] . '</td>
                                                <td width="36%">' . $phrase['_article'] . '</td>
                                                <td width="8%">' . $phrase['_added'] . '</td>
						<td width="8%">' . $phrase['_approved'] . '</td>
                                                <td width="10%" nowrap align="center">' . $phrase['_view'] . '</td>
                                                <td width="6%" align="center">' . $phrase['_action'] . '</td>
                                        </tr>
                                        ' . $this->display_comment_listings($mode, $page, $keywords) . '
                                        </table>
                                                
                                        </div>
                                        <div class="block-footer">
                                                        <div class="block-right">
                                                                        <div class="block-left"></div>
                                                        </div>
                                        </div>
                        </div>
                </div>';
		
		return $html;
	}
	
	/*
        * Function to fetch and display the comments for an article
        *
        * @param       integer      
        *
        * @return      string       
        */
	function display_comment_listings($mode = '', $page, $keywords = '')
	{
		global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage, $show;
		
		if (!isset($page))
		{
			$page = 1;
		}
		
		$limit = ' ORDER BY commentsid DESC LIMIT ' . (($page - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		
		// #### APPROVED COMMENTS ##################################
		$extra1 = $extra2 = '';
		if ($mode == 'approved')
		{
			$extra1 = "AND approved = '1'";
		}
		else if ($mode == 'unapproved')
		{
			$extra1 = "AND approved = '0'";
		}
		
		if (!empty($keywords))
		{
			$extra2 = "AND (name LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR title LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR content LIKE '%" . $ilance->db->escape_string($keywords) . "%')";
		}
		
		$sqlmsg  = "
			SELECT *
			FROM " . DB_PREFIX . "kbcomments
			WHERE postsid > 0
			$extra1
			$extra2
			$limit
		";
		
		$sqlmsg2 = "
			SELECT *
			FROM " . DB_PREFIX . "kbcomments
			WHERE postsid > 0
			$extra1
			$extra2
			ORDER BY commentsid DESC
		";
		
		$numberrows = $ilance->db->query($sqlmsg2);
		$number = $ilance->db->num_rows($numberrows);
		
		$counter = ($page - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	
		$sqlmsg = $ilance->db->query($sqlmsg);
		if ($ilance->db->num_rows($sqlmsg) > 0)
		{
			$rows = 0;
			$return = '';
			while ($resmsg = $ilance->db->fetch_array($sqlmsg))
			{
				$parentarticle = $ilance->db->fetch_field(DB_PREFIX."kbposts", "postsid = '".$resmsg['postsid']."'", "subject");
				if (empty($parentarticle))
				{
					$parentarticle = '<span class="gray">' . $phrase['_none'] . '</span>';
				}
				$class = ($rows % 2) ? 'alt1' : 'alt1';
				$return .= '<tr class="' . $class . '" valign="top">';
				$return .= '<td><div title="' . handle_input_keywords($resmsg['content']) . '"><div class="blue"><a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_view-comment&amp;id='.$resmsg['commentsid'] . '&amp;external=1">' . handle_input_keywords($resmsg['title']) . '</a></div><div style="padding-top:2px" class="smaller gray">' . handle_input_keywords(shorten($resmsg['content'], 35)) . '</div><div class="smaller" style="padding-top:3px">' . $phrase['_by'] . ': <strong>' . handle_input_keywords($resmsg['name']) . '</strong> <!--(<span class="blue"><a href="mailto:'.$resmsg['email'] . '">'.$resmsg['email'] . '</a></span>)--></div></div></td>';
				$return .= '<td><div class="black">' . handle_input_keywords($parentarticle) . '</div></td>';
				$return .= '<td nowrap="nowrap">' . print_date($resmsg['insdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . '</td>';
				
				if ($resmsg['approved'] == '1')
				{
					$return .= '<td nowrap="nowrap" align="center"><span title="Approved - Click to Unapprove"><a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_unapprove-comment&amp;id='.$resmsg['commentsid'] . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" /></a></span></td>';
				}
				else
				{
					$return .= '<td nowrap="nowrap" align="center"><span title="Unapproved - Click to Approve"><a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_approve-comment&amp;id='.$resmsg['commentsid'] . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" /></a></span></td>';
				}
				
				$return .= '<td align="center"><a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_view-comment&amp;id='.$resmsg['commentsid'] . '&amp;external=1"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a></td>';
				$return .= '<td align="center" nowrap="nowrap"><a href="' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_remove-comment&amp;id='.$resmsg['commentsid'] . '" onclick="return confirm(\'' . $phrase['_please_take_a_moment_to_confirm_your_action'] . '\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt=""></a></td>';
				$return .= '</tr>';
				$rows++;
			}
			$return .= '<tr><td colspan="6">' . print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $page, $counter, $ilpage['components'] . '?cmd=components&amp;module=lancekb') . '</td></tr>';
		}
		else
		{
			if (isset($return))
			{
				$return .= '<tr><td colspan="6" align="center">' . $phrase['_no_results_found'] . '</td></tr>';
			}
			else
			{
				$return = '<tr><td colspan="6" align="center">' . $phrase['_no_results_found'] . '</td></tr>';
			}
		}
		
		return $return;
	}
	
	/*
        * Function to fetch the article count within a particular category for display within the AdminCP
        *
        * @param       integer      
        *
        * @return      string       
        */
	function print_article_category_count($catid = 0)
	{
		global $ilance;
		
		$cats = $this->fetch_children($catid);
		
		$sql = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "kbposts
			WHERE (FIND_IN_SET(catid, '" . $cats . ",'))
				AND approved = '1'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['count'] > 0)
			{
				return '<span class="blue">' . number_format($res['count']) . '</span>';
			}
			else
			{
				return '<span class="gray">0</span>';
			}
		}
		
		return '<span class="gray">0</span>';
	}
    
	/*
        * Function to fetch the pending articles within a particular category for display within the AdminCP
        *
        * @param       integer      
        *
        * @return      string       
        */
	function print_pending_article_category_count($catid = 0)
	{
		global $ilance;
		
		$sql = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "kbposts
			WHERE catid = '" . intval($catid) . "'
			AND approved = '0'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['count'] > 0)
			{
				return '<span class="blue">' . number_format($res['count']) . '</span>';
			}
			else
			{
				return '<span class="gray">0</span>';
			}
		}	
		else
		{
			return '<span class="gray">0</span>';
		}
	}
	
	/*
        * Function to fetch the pending comments within a particular category for display within the AdminCP
        *
        * @param       integer      
        *
        * @return      string       
        */
	function print_pending_article_comments_count($catid = 0)
	{
		global $ilance;
		
		$sql = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "kbcomments c
			LEFT JOIN " . DB_PREFIX . "kbposts p ON (c.postsid = p.postsid)
			WHERE p.catid = '" . intval($catid) . "'
			AND c.approved = '0'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['count'] > 0)
			{
				return '<span class="blue">' . number_format($res['count']) . '</span>';
			}
			else
			{
				return '<span class="gray">0</span>';
			}
		}
		else
		{
			return '<span class="gray">0</span>';
		}
	}
	
	/*
        * Function to fetch the pending comments within a particular category for display within the AdminCP
        *
        * @param       integer      
        *
        * @return      string       
        */
	function print_article_comments_count($catid = 0)
	{
		global $ilance;
		
		$cats = $this->fetch_children($catid);
		
		$sql = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "kbcomments k
			LEFT JOIN " . DB_PREFIX . "kbposts p ON (p.postsid = k.postsid)
			WHERE (FIND_IN_SET(p.catid, '" . $cats . ",'))
				AND k.approved = '1'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['count'] > 0)
			{
				return '<span class="blue">' . number_format($res['count']) . '</span>';
			}
			else
			{
				return '<span class="gray">0</span>';
			}
		}
		
		return '<span class="gray">0</span>';
	}
	
	/*
        * Function to fetch the ratings for an article
        *
        * @param       integer      article id
        *
        * @return      string       
        */
	function print_article_star_ratings($id)
	{
		global $ilance, $phrase, $iltemplate, $page_title, $area_title, $ilconfig, $ilpage;
		
		$result1 = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "kbratings
			WHERE postsid = '" . intval($id) . "'
		");
		if (!$ilance->db->num_rows($result1))
		{
			$n = 5;
			$rating = '';
			for ($i = 0; $i < $n; $i++)
			{
				$rating .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/starn.gif" border="0" alt="" />';
			}
		}
		else
		{
			$result1 = $ilance->db->query("
				SELECT AVG(rating) AS average
				FROM " . DB_PREFIX . "kbratings
				WHERE postsid = '" . intval($id) . "'
			");
			if ($ilance->db->num_rows($result1) > 0)
			{
				$res = $ilance->db->fetch_array($result1);
			}
			$rating = "";
			for ($i = 0; $i < floor($res['average']); $i++)
				$rating .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/starp.gif" border="0" alt="" />';
				$numleft = 5 - $i;
				if (($res['average']-$i) >= 0.5)
				{
					$numleft = $numleft - 1;
					$rating .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/starm.gif" border="0" alt="" />';
				}
				
				for ($i = 0; $i < $numleft; $i++)
				{
					$rating .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/starn.gif" border="0" alt="" />';
				}
		}
		
		return $rating;
	}
	
	/*
        * Function to display the update article menu within the AdminCP
        *
        * @param       integer      article id
        * @param       integer      parent id
        *
        * @return      string       
        */
	function display_update_article()
	{       
		global $ilconfig, $ilance, $appendstr, $parentid, $phrase, $ilpage, $show;
		
		$postid = 0;
		$parentid = 0;
		if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
		    $postid = intval($ilance->GPC['id']);
		}
		if (isset($ilance->GPC['parentid']) AND $ilance->GPC['parentid'] > 0)
		{
		    $parentid = intval($ilance->GPC['parentid']);
		}

		$html = '';
		
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "kbposts
			WHERE postsid = '".intval($postid)."'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			
			$approved = 0;
			$cb_approved = '';
			if ($res['approved'])
			{
				$approved = 1;
				$cb_approved = 'checked="checked"';
			}
			$appendstr = '';
			
			$res['answer'] = htmlspecialchars_uni($res['answer']);
			
			$html = '
<script language="JavaScript">
<!--
function validate_message()
{
        fetch_bbeditor_data();
        return(true);
}
function validate_all()
{	
        return validate_message(); 
}
// -->
</script>

<form name="ilform" method="post" action="' . $ilpage['components'] . '" accept-charset="UTF-8" onsubmit="return validate_all();" style="margin:0px">
<input type="hidden" name="cmd" value="components" />
<input type="hidden" name="module" value="lancekb" />
<input type="hidden" name="subcmd" value="_update-article" />
<input type="hidden" name="id" value="' . intval($postid) . '" />
<input type="hidden" name="return" value="' . $ilpage['components'] . '?cmd=components&module=lancekb&subcmd=_update-article&id=' . intval($postid) . '&parentid=' . intval($parentid) . '&external=1" />

<div class="block-wrapper">
        <div class="block">
        
                        <div class="block-top">
                                        <div class="block-right">
                                                        <div class="block-left"></div>
                                        </div>
                        </div>
                        
                        <div class="block-header">' . $phrase['_update'] . ' &quot;' . handle_input_keywords($res['subject']) . '&quot;</span></div>
                        <!--<div class="block-content-yellow" style="padding:9px"><div class="smaller"></div></div>-->
                        <div class="block-content" style="padding:0px">
                                
                                <table border="0" cellspacing="1" cellpadding="9">
                                <tr class="alt1">
                                        <td><span class="gray">' . $phrase['_category'] . '</span></td>
                                        <td><select name="catid" style="font-family: verdana"><option value="0">' . $phrase['_orphan_article_no_category'] . '</option>' . $this->display_category_update_pulldown(0, '') . $appendstr .'</select></td>
                                </tr>
                                <tr class="alt1">
                                        <td valign="top"><span class="gray">' . $phrase['_subject'] . '</span></td>
                                        <td ><input type="text" name="subject" style="width: 500px; font-family:Verdana" id="subject" value="' . handle_input_keywords($res['subject']) . '" /></td>
                                </tr>
                                <tr class="alt1">
                                        <td valign="top"><span class="gray">' . $phrase['_message'] . '</span></td>
                                        <td>' . print_wysiwyg_editor('message', $res['answer'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg']) . '</td>
                                </tr>
                                <tr class="alt1">
                                        <td><span class="gray">' . $phrase['_keywords'] . '</span></td>
                                        <td><input type="text" name="keywords" style="width: 500px; font-family: Verdana" value="' . handle_input_keywords($res['keywords']) . '"><br />Keywords Seperate by commas; also used for related search results</td>
                                </tr>
                                <tr class="alt1">
                                        <td width="27%" align="left"><span class="gray">' . $phrase['_author'] . '</span></td>
                                        <td width="73%"><input type="text" name="name" value="' . handle_input_keywords($res['name']) . '" style="width:500px; font-family: Verdana" /></td>
                                </tr>
                                <tr class="alt1">
                                        <td><span class="gray">' . $phrase['_author_email'] . '</span></td>
                                        <td><input type="text" name="email" value="' . handle_input_keywords($res['email']) . '" style="width:500px; font-family: Verdana" /></td>
                                </tr>
                                <tr class="alt1">
                                        <td><span class="gray">' . $phrase['_approved'] . '</span></td>
                                        <td><input type="checkbox" name="approved" value="' . $approved . '" ' . $cb_approved . ' /> Click this checkbox if you would like to approve this article.</td>
                                </tr>
                                <!--
                                <tr class="alt1">
                                        <td><span class="gray">' . $phrase['_attachments'] . '</span></td>
                                        <td>'.$this->fetch_attachment_list(intval($postid)).'</td>
                                </tr>
                                //-->
                                <tr>
                                    <td colspan="2"><input type="submit" value=" '. $phrase['_save'] . ' " class="buttons" style="font-size:15px" /> &nbsp;&nbsp;&nbsp;<span class="blue"><a href="'.$ilpage['components'] . '?module=lancekb">' . $phrase['_cancel'] . '</a></span></td>
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
</form>';	
		}
		
		return $html;
	}
	
	/*
        * Function to fetch users article attachments
        *
        * @param       integer      article id
        *
        * @return      string       
        */
	function fetch_user_attachments($id)
	{
		global $ilance, $phrase, $v3template, $iltemplate, $page_title, $area_title, $ilconfig, $ilpage;
		
		$sql_attach = $ilance->db->query("
			SELECT attachid, filename, filesize
			FROM " . DB_PREFIX . "attachment
			WHERE tblfolder_ref = '" . intval($id) . "'
			AND attachtype = 'kb'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_attach) > 0)
		{
			$attach = "<span class='smaller'><br /><strong>".$phrase['_attachments']."</strong>:<br />";
			while ($res_attach = $ilance->db->fetch_array($sql_attach))
			{
				$attach .= "<li><img src='".$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder']."paperclip.gif' alt='".$res_attach['filesize']." bytes'> ".stripslashes($res_attach['filename'])."&nbsp;&nbsp;<a href='".HTTP_SERVER.$ilpage['attachment']."?id=".$res_attach['attachid']."'><img src='".$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder']."icons/arrowdown.gif' border='0' alt=''></a></li>";
			}
			$attach .= '</span><br />';
		}
		else
		{
			$attach = "<br />";
		}
		
		return $attach;
	}
	
	/*
        * Function to display the article comment
        *
        * @param       integer      article id
        *
        * @return      string       
        */
	function display_article_comment()
	{
		global $ilance, $phrase, $ilpage, $ilconfig, $show;
		
		$id = 0;
		if (isset($ilance->GPC['id']))
		{
			$id = intval($ilance->GPC['id']);
		}
		
		$result = $ilance->db->query("
			SELECT comments.title, comments.ipaddr, comments.name, comments.email, comments.content, comments.commentsid, comments.insdate, comments.approved, comments.postsid, posts.subject
			FROM " . DB_PREFIX . "kbcomments as comments,
			" . DB_PREFIX . "kbposts as posts
			WHERE commentsid = '" . intval($id) . "'
				AND comments.postsid = posts.postsid
		", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($result);
		if ($res['approved'] == 0)
		{
			$res['status'] = "Not Approved";
		}
		else
		{
			$res['status'] = "Approved";
		}
		
		$html = '
                <div class="block-wrapper">
                        <div class="block">
				<div class="block-top">
					<div class="block-right">
							<div class="block-left"></div>
					</div>
				</div>
				<div class="block-header">' . $phrase['_review_existing_comments_below'] . '</div>
				<div class="block-content-yellow" style="padding:9px"><div class="smaller">You can approve or remove this comment from within this area</div></div>
				<div class="block-content" style="padding:0px">
				<table border="0" width="100%" cellpadding="9" cellspacing="1">
				<tr class="alt1">
					<td width="32%" height="19"><span class="gray">' . $phrase['_guest_or_member_name'] . '</span></td>
					<td width="68%" height="19">'.stripslashes($res['name']).'</td>
				</tr>
				<tr class="alt1">
					<td height="19"><span class="gray">' . $phrase['_ip_address'] . '</span></td>
					<td height="19">'.$res['ipaddr'] . '</td>
				</tr>
				<tr class="alt1">
					<td height="19"><span class="gray">' . $phrase['_email'] . '</span></td>
					<td height="19"><span class="blue"><a href="mailto:'.$res["email"].'">'.$res['email'] . '</a></span></td>
				</tr>
				<tr class="alt1">
					<td height="19"><span class="gray">' . $phrase['_subject'] . '</span></td>
					<td height="19">'.stripslashes($res['title']).'</td>
				</tr>
				<tr class="alt1">
					<td height="19"><span class="gray">' . $phrase['_date'] . '</span></td>
					<td height="19">'.date('Y-n-d', strtotime($res['insdate'])).'</td>
				</tr>
				<tr class="alt1">
					<td height="19"><span class="gray">' . $phrase['_parent_article'] . '</span></td>
					<td height="19">'.stripslashes($res['subject']).'</td>
				</tr>
				<tr class="alt1">
					<td height="19"><span class="gray">' . $phrase['_status'] . '</span></td>
					<td height="19"><strong>'.$res['status'] . '</strong></td>
				</tr>
				<tr class="alt1">
					<td height="17" valign="top"><span class="gray">' . $phrase['_message'] . '</span></td>
					<td height="17">'.stripslashes($res['content']).'</td>
				</tr>
				<tr>
					<td colspan="2">';
					if ($res['approved'] == 0)
					{
						$html .= '<input type="button" style="font-size:15px" name="approve" value="' . $phrase['_approve_comment'] . '" onclick="location.href=\'' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_approve-comment&amp;id=' . $id . '\'" class="buttons" />';
					}
			    
					$html .= '&nbsp;&nbsp;<input type="button" name="remove" value="' . $phrase['_remove'] . '" style="font-size:15px" onclick="location.href=\'' . $ilpage['components'] . '?cmd=components&amp;module=lancekb&amp;subcmd=_remove-comment&amp;id='.$id.'\'" class="buttons" /> &nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['components'] . '?module=lancekb">' . $phrase['_cancel'] . '</a></span>';
					$html .= '</td>
				</tr>
				</table>
				</div>
				<div class="block-footer">
					<div class="block-right">
						<div class="block-left"></div>
					</div>
				</div>
                        </div>
                </div>';
                
		return $html;
	}
	
	/**
        * Function to fetch all children category id numbers recursivly in comma separated values based on a parent category id number.
        * This function is useful because it reads from the cache and does not hit the database.
        *
        * @param       string         category id number (or all)
        * @param       string         category type (service or product)
        *
        * @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
        */
        function fetch_children_ids($cid = 0)
        {
                global $ilance, $myapi, $show;
                
                $ids = '';
                
		$sql = $ilance->db->query("
			SELECT categoryid AS cid, parent AS parentid
			FROM " . DB_PREFIX . "kbcategory
			WHERE parent = '" . intval($cid) . "'
			ORDER BY sort
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($category = $ilance->db->fetch_array($sql))
			{
				if ($category['parentid'] == $cid)
				{
					if ($category['cid'] != $cid)
					{
						$ids .= $category['cid'] . ',' . $this->fetch_children_ids($category['cid']);
					}    
				}
			}
		}
		
                return $ids;
        }
        
        /**
        * Function to fetch all children category id numbers returns in comma separated values.
        *
        * @param       integer        category id number (or all)
        * @param       string         category type (service/product)
        *
        * @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
        */
        function fetch_children($cid = 0)
        {
                global $ilance, $myapi;
                
                $ids = $this->fetch_children_ids($cid);
                if (empty($ids))
                {
                        $ids = $cid;
                }
                else 
                {
                        $ids = $cid . ',' . mb_substr($ids, 0, -1);
                }
                
                return $ids;
        }
	
	/**
        * Function to print and display the about tab for this app within the AdminCP
        *
        * @return      string        HTML formatted display
        */
        function print_about_admincp()
        {
                global $ilance, $ilconfig, $show, $phrase, $ilpage;
                
                $html = '
<div class="block-wrapper">
<div class="block">
        <div class="block-top">
                <div class="block-right">
                        <div class="block-left"></div>
                </div>
        </div>
        <div class="block-header">About Knowledge Base</div>
        <div class="block-content" style="padding:0px">
                
                <table width="100%" border="0" cellspacing="0" cellpadding="12">
                <tr> 
                        <td><span class="header"><strong>LanceKB</strong></span>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                        <td>Copyright</td>
                                        <td>2010 by <span class="blue"><a href="http://www.ilance.com/" target="_blank">ILance</a></span>, all rights reserved.</td>
                                </tr>
                                <tr> 
                                        <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr>
                                        <td colspan="2">
                                        Knowledge base provides article management and integrates as a fully featured help system with comments, print, email to friend and ask a question functionality plus much more.
                                        </td>
                                </tr>
                                </table></td>
                </tr>
                </table>

        </div>
        <div class="block-footer">
                        <div class="block-right">
                                        <div class="block-left"></div>
                        </div>
        </div>
</div>
</div>';
                return $html;
        }
	
	function print_categories_admincp()
	{
		global $ilance, $phrase, $appendstr, $ilconfig, $ilpage, $show;
		
		$html = $this->display_categories(0, 0, 'header') . $this->display_categories(0, 0, '') . $appendstr . $this->display_categories(0, 0, 'footer');
		
		return $html;		
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Tue, Jan 11th, 2011
|| ####################################################################
\*======================================================================*/
?>