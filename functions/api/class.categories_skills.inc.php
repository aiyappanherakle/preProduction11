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

if (!class_exists('categories'))
{
	exit;
}

/**
* Category Skills class to perform the majority of category skill related functions within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class categories_skills extends categories
{
        var $fetchskills = array();
        var $cidusercount = 0;
	var $cats = array();
        
        /**
        * Function to fetch the array of the skills category structure.
        *
        * @param       string       short language identifier (default eng)
        *
        * @return      array        Returns category array structure
        */
        function build_array_skills($slng = 'eng', $limit = -1, $counter = 0, $level = 0)
        {
                global $ilance;
                
                $query = $ilance->db->query("
                        SELECT cid, parentid, level, title_" . $slng . " AS title, description_" . $slng . " AS description, views, keywords, visible, sort
                        FROM " . DB_PREFIX . "skills
			" . (($level > 0) ? "level = '" . intval($level) . "'" : "") . "
                        ORDER BY sort ASC
			" . (($limit == -1) ? '' : "LIMIT $counter, $limit") . "
                ", 0, null, __FILE__, __LINE__);
                while ($categories = $ilance->db->fetch_array($query, DB_ASSOC))
                {
                        $this->fetchskills[$_SESSION['ilancedata']['user']['slng']]["$categories[cid]"] = array(
                                'cid' => $categories['cid'],
                                'parentid' => $categories['parentid'],
                                'level' => 0,
                                'title' => $categories['title'],
                                'description' => $categories['description'],
                                'views' => $categories['views'],
                                'keywords' => $categories['keywords'],
                                'visible' => $categories['visible'],
                                'sort' => $categories['sort'],
                                'auctioncount' => 0
                        );
                }
                unset($categories);
                
                $arr = array();
                foreach ($this->fetchskills["$slng"] AS $cid => $array)
                {
                        $arr[] = $array;
                }
		//print_r($arr); exit;
		
                //return $this->propersort($slng, $cattype, $categorymode, $counter);
                // sort results properly and makes $this->cats or $ilance->categores->cats using the cache without any further db overhead
                $this->get_cats($arr, 0, 1, $counter);

                return $this->cats;
        }
	
	/**
        * Function to process and fetch categories
        *
        * @param       array        category results array
        * @param       integer      parent id
        * @param       integer      category level
        *
        * @return      nothing
        */
        function get_cats($result, $parentid = 0, $level = 1, $counter = 0)
        {
                global $ilance;
                
                $this->cats = array();
                $this->get_cats_recursive($result, $parentid, $level, $counter);
        }
        
        /**
        * Function to process and fetch categories recusively
        *
        * @param       array        category results array
        * @param       integer      category parent id
        * @param       integer      category level
        * @param       integer      counter (default 0)
        *
        * @return      nothing
        */
        function get_cats_recursive($result, $parentid = 0, $level = 1, $counter = 0)
        {
                global $ilance;
                
                $ilance->GPC['pp'] = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : 10;
		$startfrom = (isset($ilance->GPC['page']) AND $ilance->GPC['page'] > 1) ? ($ilance->GPC['pp'] + $counter - $ilance->GPC['pp']) : 0;
		$endat = ($ilance->GPC['pp'] + $counter);

		for ($i = 0; $i < count($result); $i++)
                {
                        if ($result[$i]['parentid'] == $parentid)
                        {
                                $result[$i]['level'] = $level;
                                $this->cats[] = $result[$i];
                                $this->get_cats_recursive($result, $result[$i]['cid'], $level + 1, $i);
                        }
                }
        }
        
        /**
        * Function to determine if a user is skilled in a particular category based on his/her skills selection
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      skill category id
        *
        * @return      array        Returns true or false if user is skilled in a particular category
        */
        function is_user_skilled($userid = 0, $cid = 0)
        {
                global $ilance;
                
                $sql = $ilance->db->query("
                        SELECT aid
                        FROM " . DB_PREFIX . "skills_answers
                        WHERE user_id = '" . intval($userid) . "'
                                AND cid = '" . intval($cid) . "'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        return true;
                }
                
                return false;
        }
        
        /**
        *
        **/
        function print_skills_children($parentid = 0, $level, $showcount, $userid, $prepopulate, $slng = 'eng', $doajax = false)
        {
                global $ilance, $myapi, $ilconfig, $phrase; $headinclude;
		
                $html = '';
                $h = array();
                
                $result = $ilance->db->query("
                        SELECT parentid, cid, title_$slng
                        FROM " . DB_PREFIX . "skills
                        WHERE parentid = '" . intval($parentid) . "'
                            AND visible = '1'
                        GROUP BY cid
                        ORDER BY sort ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($result) > 0)
                {
                        $count = 0;
                        while ($row = $ilance->db->fetch_array($result))
                        {
                                if ($doajax)
                                {
                                        $js = 'onclick="add_skill(\'' . $row['cid'] . '\', \'' . addslashes(stripslashes($row['title_' . $slng])) . '\')"';
                                }
                                
                                $h[$count]['html'] = '';
                                $h[$count]['html'] .= '<div style="padding-top:4px; padding-left:4px"><label for="cid_' . $row['cid'] . '"><input type="checkbox" id="cid_' . $row['cid'] . '" name="sid[' . $row['cid'] . ']" value="true" ' . (($doajax) ? ($js) : '') . ' ' . ((!empty($_SESSION['ilancedata']['user']['userid']) AND $this->is_user_skilled($_SESSION['ilancedata']['user']['userid'], $row['cid']) AND $prepopulate) ? 'checked="checked"' : '') . ' /> ' . ((!empty($_SESSION['ilancedata']['user']['userid']) AND $this->is_user_skilled($_SESSION['ilancedata']['user']['userid'], $row['cid']) AND $prepopulate) ? '<strong>' . stripslashes($row['title_' . $slng]) . '</strong>' : stripslashes($row['title_' . $slng])) . '</label>' . ((isset($showcount) AND $showcount) ? ' <span class="smaller gray">(' . $this->fetch_skills_category_count($row['cid']) . ')</span>' : '') . '</div>';
                                $count++;
                        }
                }
                
                $bit['visible'] = $bit['hidden'] = '';
                
                $hidden = '<div style="padding-left:4px; padding-bottom:6px; padding-top:5px" class="blue"><a href="javascript:void(0)" onclick="toggle_more(\'showmoreskills_' . $parentid . '\', \'moretext_' . $parentid . '\', \'' . $phrase['_more'] . '\', \'' . $phrase['_less'] . '\', \'showmoreicon_' . $parentid . '\')"><span id="moretext_' . $parentid . '" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmoreskills_$parentid"]) ? $phrase['_less'] : $phrase['_more']) . '</span></a> <img id="showmoreicon_' . $parentid . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmoreskills_$parentid"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
                if (!empty($h) AND is_array($h))
                {
                        $c = 0;
                        foreach ($h as $key => $array)
                        {
                                $c++;
                                if ($c <= $ilconfig['globalauctionsettings_catcutoff'])
                                {
                                        $bit['visible'] .= $h[$key]['html'];
                                }
                                else
                                {
                                        $bit['hidden'] .= $h[$key]['html'];
                                }
                        }
                }
                
                if ($count <= $ilconfig['globalauctionsettings_catcutoff'])
                {
                        $hidden = '';
                }
                
                $html = "$bit[visible] <div id=\"showmoreskills_$parentid\" style=\"" . (!empty($ilcollapse["showmoreskills_$parentid"]) ? $ilcollapse["showmoreskills_$parentid"] : 'display: none;') . "\">$bit[hidden]</div>$hidden";
                
                return $html;
        }
        
        /**
        * Function to 
        *
        * @param       integer      parent category id
        * @param       string       short language identifier (default = eng)
        * @param       boolean      show the skills category count?
        * @param       integer      skill category level
        * @param       boolean      determine if we should pre-populate skill categories
        * @param       boolean      determine if we are using ajax for sending sid[]'s to the page outside the iframe
        */
	function fetch_skills_columns($parentid, $slng = 'eng', $showcount, $level, $prepopulate, $columns, $doajax = false)
        {
                global $ilance, $myapi, $ilconfig, $headinclude;
		
                $html = '';
                $cols = 0;
                
                $result = $ilance->db->query("
                        SELECT s.parentid, s.cid, s.title_$slng, a.user_id
                        FROM " . DB_PREFIX . "skills s
			LEFT JOIN " . DB_PREFIX . "skills_answers a ON a.cid = s.cid
                        WHERE parentid = '" . intval($parentid) . "'
                            AND visible = '1'
                        GROUP BY cid
                        ORDER BY sort ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($result) > 0)
                {
                        while ($row = $ilance->db->fetch_array($result))
                        {
                                if ($cols == 0)
                                {
                                        $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                }
                                
                                $html .= '<td width="25%" valign="top"><div style="padding-top:4px; padding-left:' . $this->fetch_level_padding($level) . 'px"><strong>' . stripslashes($row['title_' . $slng]) . '</strong>' . ((isset($showcount) AND $showcount) ? ' <span class="gray">(' . $this->fetch_skills_category_recursive_count($row['cid']) . ')</span>' : '') . '</div>' . $this->print_skills_children($row['cid'], $level + 1, $showcount, $row['user_id'], $prepopulate, $slng, $doajax) . '</td>';
                                
                                $cols++;
                                
                                if ($cols == $columns)
                                {
                                        $html .= '</tr>';
                                        $cols = 0;
                                }
                        }
                        
                        if ($cols != $columns && $cols != 0)
                        {
                                $neededtds = $columns - $cols;
                                for ($i = 0; $i < $neededtds; $i++)
                                {
                                        $html .= '<td></td>';
                                }
                                
                                $html .= '</tr>'; 
                        }
                }
                
                return $html;
        }
	
        /**
	* Function to print the main subcategory columns of a particular category being viewed or selected
	*
	* @param        string          short language code
	* @param        boolean         show category counts? (default yes)
	* @param        boolean         pre-populate skills (default true)
	* @param        integer         number of columns to display (default 4)
	* @param        boolean         do ajax logic for advanced searches? (default false)
	*/
        function print_skills_columns($slng = 'eng', $showcount = 1, $prepopulate = true, $columns = 4, $doajax = false)
        {
                global $ilance, $myapi, $phrase, $ilconfig, $ilpage, $show, $sqlquery, $categoryfinderhtml, $headinclude;
		
		$html = '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
                $html .= $this->fetch_skills_columns(0, $slng, $showcount, $level = 1, $prepopulate, $columns, $doajax);
                $html .= '</table>';
		
                return $html;
        }
        
        /**
        * Function to fetch the parent id of a skill category.
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      category id
        *
        * @return      integer      Returns parentid of a category or 0 otherwise
        */
        function parentid($slng = 'eng', $cid = 0)
        {
                global $ilance, $myapi;
                
                if (!empty($this->fetchskills["$slng"]["$cid"]))
                {
                        return $this->fetchskills["$slng"]["$cid"]['parentid'];
                }
                
                return 0;
        }
        
        /**
        * Function to fetch the meta tag keywords text of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      category id
        * @param       boolean      insert comma after? (default false)
        * @param       boolean      show input keywords (default false)
        *
        * @return      mixed        Returns category array structure (or All Categories) text otherwise
        */
        function keywords($slng = 'eng', $cid = 0, $commaafter = false, $showinputkeywords = false)
        {
                global $ilance;
                
                $keywordbit = $text = $bit = '';
                if (!empty($this->fetchskills["$slng"]["$cid"]) OR !empty($this->fetchskills["$slng"]["$cid"]) AND $this->fetchskills["$slng"]["$cid"] != '0')
                {
                        if (!empty($this->fetchskills["$slng"]["$cid"]['keywords']))
                        {
                                if ($commaafter)
                                {
                                        $bit = ', ';
                                }
                                $text = $this->fetchskills["$slng"]["$cid"]['keywords'] . $bit;
                        }
                }
                if ($showinputkeywords)
                {
                        if (!empty($ilance->GPC['q']))
                        {
                                $keywordbit = htmlspecialchars($ilance->GPC['q']) . ', ';
                        }
                }
                
                return $keywordbit . $text;
        }
        
        /**
        * Function to fetch all skill children category id numbers recursivly in comma separated values based on a parent category id number.
        * This function is useful because it reads from the cache and does not hit the database.
        *
        * @param       string         category id number (or all)
        *
        * @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
        */
        function fetch_skills_children_ids($cid = 'all')
        {
                global $ilance, $myapi;
        
                $ids = '';
                if ($cid == 'all')
                {
                        foreach ($this->fetchskills[$_SESSION['ilancedata']['user']['slng']] AS $cid2 => $categories)
                        {
                                $c = 0;
                                foreach ($categories AS $category)
                                {
                                        $ids .= $category['cid'] . ',';
                                        $c++;
                                }
                        }
                        if ($c > 1)
                        {
                                $ids = mb_substr($ids, 0, -1);
                        }                
                }
                else
                {
                        foreach ($this->fetchskills[$_SESSION['ilancedata']['user']['slng']] AS $cid2 => $categories)
                        {
                                if ($categories['parentid'] == $cid)
                                {
                                        if ($categories['cid'] != $cid)
                                        {
                                                $ids .= $categories['cid'] . ',' . $this->fetch_skills_children_ids($categories['cid']);
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
        *
        * @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
        */
        function fetch_skills_children($cid = 0)
        {
                global $ilance, $myapi;
                
                $ids = $this->fetch_skills_children_ids($cid);
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
        * Function to remove skill categories recursively
        *
        * @param       integer      category id
        */
        function remove_skills_category_recursive($cid = 0)
        {
                global $ilance, $myapi;
                
                if (empty($cid))
                {
                        return;
                }
                
                $cids = $this->fetch_skills_children(intval($cid));
                
                // #### REMOVE SKILL ANSWERS ###################################
                $sql = $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "skills_answers
                        WHERE cid IN (" . $cids . ")
                ", 0, null, __FILE__, __LINE__);
                
                // #### REMOVE CATEGORY ############################################
                // remove categories
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "skills
                        WHERE cid IN (" . $cids . ")
                ", 0, null, __FILE__, __LINE__);                
        }
        
        /**
        * Function to determine if a skill category can be removed from the datastore
        */
        function can_remove_skill_categories()
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "skills
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if ($res['count'] == 1)
                        {
                                return 0;
                        }
                }
                
                return 1;
        }
        
        /**
        * Function to update and set the proper category level for each category
        */
        function set_levels_skills()
        {
                global $ilance, $myapi, $ilpage, $show;
                
                $sql = $ilance->db->query("
                        SELECT cid, parentid, level
                        FROM " . DB_PREFIX . "skills
                ", 0, null, __FILE__, __LINE__);
                while ($cats = $ilance->db->fetch_array($sql))
                {
                        $level = 1;
                        if ($cats['parentid'] == 0)
                        {    
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "skills
                                        SET level = 1
                                        WHERE cid = '" . $cats['cid'] . "'
                                ", 0, null, __FILE__, __LINE__);                    
                        }
                        else
                        {
                                $this->set_levels_skills_update($cats['cid'], $cats['parentid'], $level);
                        }
                }
        }
        
        /**
        * Function to set skill level and update the datastore
        *
        * @param       integer      category id
        * @param       integer      parent category id
        * @param       integer      level
        * @param       integer      category id to update
        */
        function set_levels_skills_update($cid, $parentid, $level, $cid_save = '')
        {
                global $ilance, $myapi, $ilpage, $show;
                
                if (empty($cid_save))
                {
                        $cid_save = $cid;
                }
                
                $sql = $ilance->db->query("
                        SELECT cid, parentid, level
                        FROM " . DB_PREFIX . "skills
                        WHERE cid = '" . intval($parentid) . "'
                ", 0, null, __FILE__, __LINE__);
                                 
                $category = $ilance->db->fetch_array($sql);
            
                if ($category['parentid'] == 0)
                {
                        $level = $level + 1;
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "skills
                                SET level = '" . intval($level) . "'
                                WHERE cid = '" . intval($cid_save) . "'
                        ", 0, null, __FILE__, __LINE__);                    
                }
                else
                {
                        $level = $level + 1;
                        $this->set_levels_skills_update($category['cid'], $category['parentid'], $level, $cid_save);
                }                 
        }
        
        /**
        * Function to fetch the user count currently opted to a particular skill category id
        *
        * @param       integer      category id
        */
        function fetch_skills_category_count($cid = 0)
        {
                global $ilance, $ilconfig, $phrase;
                
                $count = 0;
                
                $sql = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "skills_answers
                        WHERE cid = '" . intval($cid) . "'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $count = $ilance->db->num_rows($sql);
                }
                
                return $count;
        }
        
        /**
        * Function to fetch the user count currently opted to a particular skill category id recursively.
        * This function is only called on the main parent skill categories.
        *
        * @param       integer      category id
        */
        function fetch_skills_category_recursive_count($cid = 0, $counter = 0)
        {
                global $ilance, $ilconfig, $phrase;
                
                $sql = $ilance->db->query("
                        SELECT cid
                        FROM " . DB_PREFIX . "skills
                        WHERE parentid = '" . intval($cid) . "'
                                AND visible = '1'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "skills_answers
                                        WHERE cid = '" . $res['cid'] . "'
                                ");
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        $c = 0;
                                        while ($r = $ilance->db->fetch_array($sql2))
                                        {
                                                $c++;
                                        }
                                        $counter+= $c;
                                }
                                $this->fetch_skills_category_recursive_count($res['cid'], $counter);
                        }
                }
                
                return $counter;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>