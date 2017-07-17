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
* Category class to perform the majority of category functions in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class categories
{
        var $cats = array();
        var $fetch = array();
        var $custom = array();
        var $subs = null;
        var $cat_map = array();
        var $buildarray = false;
        
        /**
        * Constructor
        *
        * @param       bool         build the array on load? default = false
        * @param       string       category type (service, product, experts, portfolio, serviceprovider)
        * @param       string       short language identifier? default = user session lang.
        * @param       integer      category mode (0 = all,  1 = portfolio, 2 = rss, 3 = newsletters)
        * @param       boolean      enable proper category sorting (builds $this->cat & $this->fetch internally) (default true)
        */
        function categories($buildarray = false, $cattype = 'service', $slng = 'eng', $categorymode = 0, $propersort = true)
        {
                if ($buildarray)
                {
                        $this->build_array($cattype, $slng, $categorymode, $propersort);
                }
        }
        
        /**
        * Function to fetch and build the array of the category structure.  This will internally build our $ilance->categories->fetch[] array.
        * Additionally this function can sort the array using an internal sorting method if required.
        *
        * @param       string       category type (service/product)
        * @param       string       short language identifier (default eng)
        * @param       string       category mode (0 = all,  1 = portfolio, 2 = rss, 3 = newsletters)
        * @param       bool         enable proper category/parent/child sorting on the fly? (default yes)
        * @param       string       extra 1
        * @param       string       extra 2
        * @param       integer      page counter (default 0)
        * @param       integer      per page limit (default 10)
        * @param       integer      category id level depth selector (default 10)
        * @param       integer      category id selector (extra logic)
        * @param       string       category title we're searching for (extra logic)
        * @param       integer      category visibility (admincp usage mainly) (default 1)
        *
        * @return      array        Returns category array structure
        */
        function build_array($cattype = 'service', $slng = 'eng', $categorymode = 0, $propersort = true, $extra = '', $extra2 = '', $counter = 0, $limit = 10, $level = 10, $cid = 0, $title = '', $visible = 1)
        {
                global $ilance;

                $ilance->timer->start();
                
                // #### let other scripts know we've built the array cache already
                $this->buildarray = true;

                if ($cattype == 'stores')
                {
                        $query = $ilance->db->query("
                                SELECT cid, storeid, category_name AS title, parentid, type, canpost, views, itemcount AS auctioncount, visible, sort, level
                                FROM " . DB_PREFIX . "stores_category
                                WHERE (storeid = '" . intval($extra) . "' OR cid = '" . intval($extra2) . "')        
                                ORDER BY cid, sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        while ($categories = $ilance->db->fetch_array($query, DB_ASSOC))
                        {
                                $this->fetch["$slng"]["$cattype"][] = $categories;
                        }
                        unset($categories);
                }
                else if ($cattype == 'storesmain')
                {
                        $query = $ilance->db->query("
                                SELECT cid, storeid, category_name AS title, parentid, type, canpost, views, itemcount AS auctioncount, visible, sort, level
                                FROM " . DB_PREFIX . "stores_category
                                WHERE storeid = '-1'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        while ($categories = $ilance->db->fetch_array($query, DB_ASSOC))
                        {
                                $this->fetch["$slng"]["stores"][] = $categories;
                        }
                        unset($categories);
                }
                else
                {
                        // #### handle some logic ##############################
                        $cattype = ($cattype == 'service' OR $cattype == 'servicecatmap' OR $cattype == 'experts' OR $cattype == 'portfolio' OR $cattype == 'serviceprovider') ? 'service' : 'product';
                        $cidsql2 = ($cid == 0) ? "" : "AND node.cid = '" . intval($cid) . "'";
                        
                        // #### find the root nodes ############################
                        if ($cid == 0 OR empty($cid))
                        {
                                $result = $ilance->db->query("
                                        SELECT cid, parentid, level, title_$slng AS title, description_$slng AS description, canpost, views, xml, portfolio, newsletter, auctioncount, budgetgroup, insertiongroup, finalvaluegroup, incrementgroup, cattype, bidamounttypes, usefixedfees, fixedfeeamount, nondisclosefeeamount, multipleaward, bidgrouping, bidgroupdisplay, useproxybid, usereserveprice, useantisnipe, bidfields, catimage, keywords, visible, sort, lft, rgt
                                        FROM " . DB_PREFIX . "categories
                                        WHERE cattype = '" . $ilance->db->escape_string($cattype) . "'
                                                " . ((isset($level) AND $level <= 0) ? '' : "AND level <= '" . intval($level) . "'") . "
                                                " . ((isset($title) AND !empty($title)) ? "AND title_$slng LIKE '%" . $ilance->db->escape_string($title) . "%'" : '') . "
                                                " . (($cid == 0) ? '' : "AND cid = '" . intval($cid) . "'") . "
                                        ORDER BY lft ASC
                                        " . (($limit == -1) ? '' : "LIMIT $counter, $limit") . "
                                ", 0, null, __FILE__, __LINE__);
                        }
                        
                        // #### find the immediate subordinates of a node ######
                        else
                        {
                                $result = $ilance->db->query("
                                        SELECT node.cid, node.parentid, node.level, node.title_$slng AS title, node.description_$slng AS description, node.canpost, node.views, node.xml, node.portfolio, node.newsletter, node.auctioncount, node.budgetgroup, node.insertiongroup, node.finalvaluegroup, node.incrementgroup, node.cattype, node.bidamounttypes, node.usefixedfees, node.fixedfeeamount, node.nondisclosefeeamount, node.multipleaward, node.bidgrouping, node.bidgroupdisplay, node.useproxybid, node.usereserveprice, node.useantisnipe, node.bidfields, node.catimage, node.keywords, node.visible, node.sort, node.lft, node.rgt
                                        FROM " . DB_PREFIX . "categories hp
                                        JOIN " . DB_PREFIX . "categories node ON node.lft BETWEEN hp.lft AND hp.rgt
                                        JOIN " . DB_PREFIX . "categories hr ON MBRWithin(Point(0, node.lft), hr.sets)
                                        WHERE hp.cid = '" . intval($cid) . "'
                                                AND hp.cattype = '" . $ilance->db->escape_string($cattype) . "'
                                                AND hr.cattype = '" . $ilance->db->escape_string($cattype) . "'
                                                AND node.cattype = '" . $ilance->db->escape_string($cattype) . "'
                                        GROUP BY node.cid
                                        HAVING  COUNT(*) <=
                                        (
                                                SELECT  COUNT(*)
                                                FROM    " . DB_PREFIX . "categories hp
                                                JOIN    " . DB_PREFIX . "categories hrp
                                                ON      MBRWithin(Point(0, hp.lft), hrp.sets)
                                                WHERE   hp.cid = '" . intval($cid) . "'
                                                AND     hp.cattype = '" . $ilance->db->escape_string($cattype) . "'
                                                AND     hrp.cattype = '" . $ilance->db->escape_string($cattype) . "'
                                        ) + 2
                                        ORDER BY node.lft
                                ", 0, null, __FILE__, __LINE__);
                        }
                        while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                        {
                                // #### add next category to our fetch array
                                $this->fetch["$slng"]["$cattype"]["$row[cid]"] = $row;
                        }
                }
                
                if ($propersort)
                {
                        $ilance->timer->stop();
                        DEBUG("build_array(\$slng = $slng, \$cattype = $cattype, \$categorymode = $categorymode) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                        
                        return $this->propersort($slng, $cattype, $categorymode, $counter);
                }
                
                $ilance->timer->stop();
                DEBUG("build_array(\$slng = $slng, \$cattype = $cattype, \$categorymode = $categorymode) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
        
                return $this->fetch;
        }
        
        /**
        * Function to fetch the entire category cache and make it a proper formatted result set for various areas within ILance
        *
        * @param       string       short language identifier
        * @param       string       category type (service or product)
        * @param       integer      category mode (0 = all,  1 = portfolio, 2 = rss, 3 = newsletters)
        */
        function propersort($slng = 'eng', $cattype = 'service', $categorymode = 0, $counter = 0)
        {
                global $ilance;
                
                $ilance->timer->start();
                
                $result = array();

                if (!empty($this->fetch["$slng"]["$cattype"]))
                {
                        foreach ($this->fetch["$slng"]["$cattype"] AS $cid => $array)
                        {
                                if (isset($categorymode))
                                {
                                        // portfolio
                                        if ($categorymode == '1')
                                        {
                                                if ($array['portfolio'])
                                                {
                                                        $result[] = $array;                
                                                }
                                        }
                                        // rss feeds
                                        else if ($categorymode == '2')
                                        {
                                                if ($array['xml'])
                                                {
                                                        $result[] = $array;                
                                                }       
                                        }
                                        // newsletters
                                        else if ($categorymode == '3')
                                        {
                                                if ($array['newsletter'])
                                                {
                                                        $result[] = $array;                
                                                }         
                                        }
                                        else
                                        {
                                                $result[] = $array;
                                        }
                                }
                                else
                                {
                                        $result[] = $array;
                                }
                        }
                        
                        // sort results properly and creates $this->cats or $ilance->categores->cats
                        //$this->get_cats($result, $parentid = 0, $level = 1, $counter);
                        $this->cats = $result;
                        unset($result);
                }

                $ilance->timer->stop();
                DEBUG("propersort(\$slng = $slng, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');

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
                
                $ilance->timer->start();

                $this->cats = $this->tmp_cats = array();
                $this->get_cats_recursive($result, $parentid, $level, $counter);
                
                $ilance->timer->stop();
                DEBUG("get_cats(\$parentid = $parentid, \$level = $level) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
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

                for ($i = $counter; $i < ($ilance->GPC['pp'] + $counter); $i++)
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
        * Function to fetch the title of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      mixed        Returns category array structure (or All Categories) text otherwise
        */
        function title($slng = 'eng', $cattype = 'service', $cid = 0)
        {
                global $ilance, $phrase;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "title_$slng");
                if (!empty($html))
                {
                        return $html;
                }
                
                return $phrase['_unknown'];
        }
        
        /**
        * Function to fetch the title of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      mixed        Returns category array structure (or All Categories) text otherwise
        */
        function parent_title($slng = 'eng', $cattype = 'service', $cid = 0)
        {
                global $ilance, $phrase;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "title_$slng");
                if (!empty($html))
                {
                        return $html;
                }
                
                return $phrase['_unknown'];
        }
        
        /**
        * Function to fetch the visibility of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      mixed        Returns true or false based on visibility
        */
        function visible($slng = 'eng', $cattype = 'service', $cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "visible");
                if ($html == 0 OR $html == 1)
                {
                        return $html;
                }
                
                return 1;
        }
        
        /**
        * Function to fetch the true level of a category within the category structure.
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      mixed        Returns category level for the selected category
        */
        function level($slng = 'eng', $cattype = '', $cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "level");
                if ($html > 0)
                {
                        return $html;
                }
                
                return 0;
        }
        
        /**
        * Function to determine if a specific product category has proxy bidding enabled.
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      mixed        Returns category array structure (or All Categories) text otherwise
        */
        function useproxybid($slng = 'eng', $cattype = '', $cid = 0)
        {
                global $ilance;
                
                if ($cattype == 'product')
                {
                        $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "useproxybid");
                        if ($html == 0 OR $html == 1)
                        {
                                return $html;
                        }
                }
                
                return 0;
        }
        
        /**
        * Function to fetch the description text of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      mixed        Returns category array structure (or All Categories) text otherwise
        */
        function description($slng = 'eng', $cattype = '', $cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "description_$slng");
                if (!empty($html))
                {
                        return $html;
                }
                
                return '';
        }
        
        /**
        * Function to fetch the meta tag keywords text of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        * @param       boolean      add comma after? (default false)
        * @param       boolean      show input keywords? (default false)
        *
        * @return      mixed        Returns category array structure (or All Categories) text otherwise
        */
        function keywords($slng = 'eng', $cattype = '', $cid = 0, $commaafter = false, $showinputkeywords = false)
        {
                global $ilance;
                
                $keywordbit = $text = $bit = '';
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "keywords");
                if (!empty($html))
                {
                        if ($commaafter)
                        {
                                $bit = ', ';
                        }
                        
                        $text = $html . $bit;
                }
                
                if ($showinputkeywords)
                {
                        if (!empty($ilance->GPC['q']))
                        {
                                $keywordbit = htmlspecialchars($ilance->GPC['q']) . ', ';
                        }
                }
                
                if (!empty($text) AND $commaafter)
                {
                        $text = mb_substr($text, 0, -2);
                }
                
                return $keywordbit . $text;
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
        function parentid($slng = 'eng', $cattype = '', $cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "' AND cattype = '" . $ilance->db->escape_string($cattype) . "'", "parentid");
                if ($html > 0)
                {
                        return $html;
                }
                
                return 0;
        }
        
        /**
        * Function to fetch the parentid of a category.
        *
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      integer      Returns parentid of a category or 0 otherwise
        */
        function auctioncount($cattype = '', $cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "' AND cattype = '" . $ilance->db->escape_string($cattype) . "'", "auctioncount");
                if ($html > 0)
                {
                        return $html;
                }
                
                return 0;
        }
        
        /**
        * Function to fetch the category type of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      category id
        *
        * @return      string        Returns the category type (service/product)
        */
        function cattype($slng = 'eng', $cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "cattype");
                
                return $html;
        }
        
        /**
        * Function to fetch the category increment group for a category.
        *
        * @param       integer      category id
        *
        * @return      string       Returns the category increment group name
        */
        function incrementgroup($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "incrementgroup");
                
                return $html;
        }
        
        /**
        * Function to determine if a category bid grouping logic is enabled or disabled
        *
        * @param       integer      category id
        *
        * @return      string       Returns true or false
        */
        function bidgrouping($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "bidgrouping");
                
                return (($html == 1) ? true : false);
        }
        
        /**
        * Function to fetch the category bid group display logic for a category
        *
        * @param       integer      category id
        *
        * @return      string       Returns the category bid group display (lowest or highest)
        */
        function bidgroupdisplay($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "bidgroupdisplay");
                
                return $html;
        }
        
        /**
        * Function to determine if a category uses fixed fees
        *
        * @param       integer      category id
        *
        * @return      string       Returns true or false
        */
        function usefixedfees($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "usefixedfees");
                
                return (($html == 1) ? true : false);
        }
        
        /**
        * Function to fetch the fixed fee amount of a particular category
        *
        * @param       integer      category id
        *
        * @return      string       Returns fee amount
        */
        function fixedfeeamount($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "fixedfeeamount");
                
                return $html;
        }
        
        /**
        * Function to fetch the bid amount types for a category
        *
        * @param       integer      category id
        *
        * @return      string       Returns a serialized string holding array with information on bid types enabled for the category.
        */
        function bidamounttypes($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "bidamounttypes");
                
                return $html;
        }
        
        function nondisclosefeeamount($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "nondisclosefeeamount");
                
                return $html;        
        }
        
        function insertiongroup($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "insertiongroup");
                
                return $html;        
        }
        
        function budgetgroup($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "budgetgroup");
                
                return $html;        
        }
        
        function finalvaluegroup($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "finalvaluegroup");
                
                return $html;        
        }
        
        function usereserveprice($cid = 0)
        {
                global $ilance;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "usereserveprice");
                
                return (($html == 1) ? true : false);     
        }
        
        /**
        * Function to determine if a category is a postable auction category.
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      string       Returns true or false response
        */
        function can_post($slng = 'eng', $cattype = '', $cid = 0)
        {
                global $ilance;
                
                ($apihook = $ilance->api('categories_can_post_start')) ? eval($apihook) : false;
                
                $html = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "canpost");
                
                return $html;
        }
        
        /**
        * Function to determine if a category is proxy bid ready (if proxy bidding is enabled)
        *
        * @param       string       short language identifier (default eng)
        * @param       string       category type (service/product)
        * @param       integer      category id
        *
        * @return      string       Returns true or false response
        */
        function proxy_bid_ready($slng = 'eng', $cattype = '', $cid = 0)
        {
                global $ilance, $myapi;
                
                $useproxybid = false;
                $incrementgroup = '';
                
                $useproxybid = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "useproxybid");
                if ($useproxybid)
                {
                        $incrementgroup = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "incrementgroup");
                        if ($incrementgroup != '0' AND !empty($incrementgroup) AND $incrementgroup != '')
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
        * Function to fetch a category count based on a set of search options per specific category.
        * For example, say Web Design normally by itself has 300 auctions.  If a user enters a search for keyword
        * "template" and 35 results were found, "35" would be that category's counter: Web Design (35) even though
        * the current category selected is "Programming".  This can be considered a mini-internal search engine for
        * category listing counts.
        *
        * @param       string       category id
        * @param       string       category type (service/product/serviceprovider)
        * @param       integer      search sql query (array)
        *
        * @return      integer      Returns category counter number
        */
        function bestmatch_auction_count($cid = 0, $cattype = 'service', $sqlquery = array())
        {
                global $ilance;
                
                $ilance->timer->start();
                
                if ($cattype == 'wantads')
                {
                        $sqlquery['cidfield'] = 'cid';
                }
                else
                {
                        $sqlquery['cidfield'] = 'p.cid';        
                }
                
                
                $count = 0;
                if (is_array($sqlquery))
                {
                        if ($cattype == 'serviceprovider')
                        {
                                $cattype = 'service';
                        }
                        
                        if ($cattype == 'wantads')
                        {
                                $cids = $ilance->wantads->fetch_children_ids($cid);
                        }
                        else
                        {
                                $cids = $this->fetch_children_ids($cid, $cattype);
                        }
                        
                        if (!empty($cids))
                        {
                                $subcategorylist = $cid . ',' . $cids;
                                $sqlquery['categories'] = "AND (FIND_IN_SET($sqlquery[cidfield], '" . $subcategorylist . "'))";
                        }
                        else
                        {
                                $sqlquery['categories'] = "AND (FIND_IN_SET($sqlquery[cidfield], '" . $cid . "'))";        
                        }                        
                        
                        if ($cattype == 'serviceprovider')
                        {
                                $sql = "$sqlquery[select] $sqlquery[keywords] $sqlquery[categories] $sqlquery[options] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[pricerange] $sqlquery[genrequery] $sqlquery[profileanswersquery] $sqlquery[skillsquery] $sqlquery[groupby] $sqlquery[orderby]";        
                        }
                        else if ($cattype == 'wantads')
                        {
                                $sql = "$sqlquery[select] $sqlquery[keywords] $sqlquery[categories] $sqlquery[options] $sqlquery[groupby] $sqlquery[orderby]";
                        }
                        else
                        {
                                $sql = "$sqlquery[select] $sqlquery[timestamp] $sqlquery[projectstatus] $sqlquery[keywords] $sqlquery[categories] $sqlquery[projectdetails] $sqlquery[projectstate] $sqlquery[options] $sqlquery[pricerange] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[genrequery] $sqlquery[groupby] $sqlquery[orderby]";
                        }
                        
                        $rows = $ilance->db->query($sql);
                        $count = $ilance->db->num_rows($rows);
                }
                
                $ilance->timer->stop();
                DEBUG("bestmatch_auction_count(\$cid = $cid, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                
                return $count;
        }
        
        /**
        * Function to print a recursive category breadcrumb trail.
        *
        * @param       integer      category id
        * @param       string       category type (service/product)
        * @param       string       short language identifier (default eng)
        * @param       boolean      no urls flag (default off)
        * @param       string       custom url (optional)
        * @param       boolean      enable seo urls (default off)
        *
        * @return      string       Returns HTML formatted version of the breadcrumb trail
        */
        function recursive($cid = 0, $cattype = 'service', $slng = 'eng', $nourls = 0, $customurl = '', $seourls = 0)
        {
                global $ilance, $myapi, $ilconfig, $navcrumb, $ilpage, $phrase, $htmlx, $show, $storeid;
                
                $ilance->timer->start();
                
                $sid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
                
                if ((empty($cattype) OR $cattype == '') OR ($cattype == 'service' OR $cattype == 'product') OR ($cattype == 'servicecatmap' OR $cattype == 'productcatmap'))
                {
                        $dbtable = DB_PREFIX . "categories";
                        $cidname = 'cid';
                        $titlefield = "title_$slng";    
                }
                
                ($apihook = $ilance->api('categories_recursive_start')) ? eval($apihook) : false;
                
                $htmlx = '';
                $sql = $ilance->db->query("
                        SELECT $cidname AS cid, $titlefield AS title, parentid
                        FROM $dbtable
                        WHERE $cidname = '" . intval($cid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $this->recursive($res['parentid'], $cattype, $slng, $nourls, $customurl, $seourls);
                        
                        if ($cattype == 'service' OR $cattype == 'servicecatmap')
                        {
                                if ($nourls)
                                {
                                        $htmlx .= stripslashes($res['title']). ' > ';
                                }
                                else 
                                {
                                        // we using custom url?
                                        if (!empty($customurl))
                                        {
                                                if ($sid == $cid)
                                                {
                                                        $htmlx .= stripslashes($res['title']) . ' > ';  					
                                                }
                                                else 
                                                {
                                                        $htmlx .= '<a href="' . $customurl . '&amp;cid=' . $res['cid'] . '">' . stripslashes($res['title']) . '</a> > ';  										
                                                }
                                        }
                                        else 
                                        {
                                                if ($seourls)
                                                {
                                                        if ($sid == $cid)
                                                        {
                                                                $htmlx .= stripslashes($res['title']) . ' > ';
                                                        }
                                                        else 
                                                        {
                                                                if ($cattype == 'service')
                                                                {
                                                                        $seotype = 'servicecat';
                                                                }
                                                                else if ($cattype == 'servicecatmap')
                                                                {
                                                                        $seotype = 'servicecatmap';    
                                                                }
                                                                
                                                                $show['nourlbit'] = true;
                                                                $htmlx .= construct_seo_url($seotype, $res['cid'], $auctionid = 0, stripslashes($res['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . ' > ';
                                                        }
                                                }
                                                else
                                                {                                        
                                                        if ($sid == $cid)
                                                        {
                                                                $htmlx .= stripslashes($res['title']) . ' > ';
                                                        }
                                                        else 
                                                        {
                                                                $htmlx .= '<a href="' . $ilpage['rfp'] . '?cid=' . $res['cid'] . '">' . stripslashes($res['title']) . '</a> > ';
                                                        }
                                                }
                                        }
                                }
                        }
                        
                        if ($cattype == 'product' OR $cattype == 'productcatmap')
                        {
                                if ($nourls)
                                {
                                        $htmlx .= stripslashes($res['title']) . ' > ';
                                }
                                else 
                                {
                                        if (!empty($customurl))
                                        {
                                                if ($sid == $cid)
                                                {
                                                        $htmlx .= stripslashes($res['title']) . ' > ';  					
                                                }
                                                else 
                                                {
                                                        $htmlx .= '<a href="' . $customurl . '&amp;cid=' . $res['cid'] . '">' . stripslashes($res['title']) . '</a> > ';  					
                                                }
                                        }
                                        else 
                                        {
                                                if ($seourls)
                                                {
                                                        if ($sid == $cid)
                                                        {
                                                                $htmlx .= stripslashes($res['title']) . ' > ';
                                                        }
                                                        else 
                                                        {
                                                                if ($cattype == 'product')
                                                                {
                                                                        $seotype = 'productcat';
                                                                }
                                                                else if ($cattype == 'productcatmap')
                                                                {
                                                                        $seotype = 'productcatmap';    
                                                                }
                                                                
                                                                $show['nourlbit'] = true;
                                                                $htmlx .= construct_seo_url($seotype, $res['cid'], $auctionid = 0, stripslashes($res['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . ' > ';
                                                        }
                                                }
                                                else
                                                {
                                                        if ($sid == $cid)
                                                        {
                                                                $htmlx .= stripslashes($res['title']) . ' > ';
                                                        }
                                                        else 
                                                        {
                                                                $htmlx .= '<a href="' . $ilpage['merch'] . '?cid=' . $res['cid'] . '">' . stripslashes($res['title']) . '</a> > ';
                                                        }   
                                                }
                                        }
                                }
                        }
                        
                        ($apihook = $ilance->api('categories_recursive_conditions')) ? eval($apihook) : false;                        
                }
                
                $html = mb_substr($htmlx, 0, -3);
                $html .= (isset($show['submit']) AND $show['submit'])
                        ? ' <img vspace="-2" hspace="3" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" width="16" height="16" />'
                        : '';
                
                ($apihook = $ilance->api('categories_recursive_end')) ? eval($apihook) : false;
                
                $ilance->timer->stop();
                DEBUG("recursive(\$cid = $cid, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                
                return $html;
        }
                
        /**
        * Function to generate the main header breadcrumb category trail.
        *
        * @param       integer      category id
        * @param       string       category type (service/product)
        * @param       string       short language identifier (default eng)
        *
        * @return      array        Returns array $navcrumb breadcrumb trail
        */        
        function breadcrumb($cid = 0, $cattype = 'service', $slng = 'eng')
        {
                global $ilance, $ilconfig, $ilpage, $phrase, $navcrumb;
                
                $ilance->timer->start();
                
                // #### handle category type for database ######################
                switch ($cattype)
                {
                        case 'service':
                        case 'servicecatmap':
                        case 'experts':
                        case 'portfolio':
                        {
                                $ctype = 'service';
                                break;                                
                        }
                        case 'product':
                        case 'productcatmap':
                        {
                                $ctype = 'product';
                                break;                                
                        }
                        default:
                        {
                                $ctype = $cattype;
                        }
                }
                
                ($apihook = $ilance->api('breadcrumb_start')) ? eval($apihook) : false;
                
                // #### fetch our nested breadcrumb bit for this category ######
                $result = $ilance->db->query("
                        SELECT parent.*, parent.title_$slng AS title, parent.description_$slng AS description
                        FROM " . DB_PREFIX . "categories AS child,
                        " . DB_PREFIX . "categories AS parent
                        WHERE child.lft BETWEEN parent.lft AND parent.rgt
                                AND parent.cattype = '" . $ilance->db->escape_string($ctype) . "'
                                AND child.cattype = '" . $ilance->db->escape_string($ctype) . "'
                                AND child.cid = '" . intval($cid) . "'
                        ORDER BY parent.lft
                ");
                $resultscount = $ilance->db->num_rows($result);
                if ($resultscount > 0)
                {
                        while ($results = $ilance->db->fetch_array($result, DB_ASSOC))
                        {
                                if ($cid == 0 AND defined('LOCATION') AND LOCATION == 'search')
                                {
                                        if (!empty($ilance->GPC['q']))
                                        {
                                                $navcrumb["$ilpage[search]?q=" . htmlspecialchars($ilance->GPC['q'])] = $phrase['_keywords'] . ': ' . htmlspecialchars($ilance->GPC['q']);
                                        }
                                        
                                        return $navcrumb;
                                }
                                
                                if ($cattype == 'service')
                                {
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('servicecatplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
                                                $navcrumb["$url"] = $results['title'];
                                                unset($url);
                                        }
                                        else
                                        {
                                                $navcrumb["$ilpage[rfp]?cid=" . $results['cid']] = $results['title'];   
                                        }
                                }
                                else if ($cattype == 'servicecatmap')
                                {
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('servicecatmapplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
                                                $catmap = print_seo_url($ilconfig['servicecatmapidentifier']);
                                                $catmap2 = print_seo_url($ilconfig['categoryidentifier']);
                                                
                                                $navcrumb[HTTP_SERVER . $catmap2] = $phrase['_categories'];
                                                $navcrumb["$catmap"] = $phrase['_services'];
                                                $navcrumb["$url"] = $results['title'];
                                                unset($catmap, $catmap2, $url);
                                        }
                                        else
                                        {
                                                $navcrumb["$ilpage[main]?cmd=categories"] = $phrase['_categories'];
                                                $navcrumb["$ilpage[rfp]?cmd=listings"] = $phrase['_browse'];
                                                $navcrumb["$ilpage[rfp]?cmd=listings&amp;cid=" . $results['cid']] = $results['title'];  
                                        }
                                }
                                else if ($cattype == 'product')
                                {
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productcatplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
                                                $navcrumb["$url"] = $results['title'];
                                                unset($url);
                                        }
                                        else
                                        {
                                                $navcrumb["$ilpage[merch]?cid=" . $results['cid']] = $results['title'];
                                        }
                                }
                                else if ($cattype == 'productcatmap')
                                {
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productcatmapplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
                                                $catmap = print_seo_url($ilconfig['productcatmapidentifier']);
                                                $catmap2 = print_seo_url($ilconfig['categoryidentifier']);
                                                
                                                $navcrumb[HTTP_SERVER . $catmap2] = $phrase['_categories'];
                                                $navcrumb["$catmap"] = $phrase['_buy'];
                                                $navcrumb["$url"] = $results['title'];
                                                unset($catmap, $catmap2, $url);
                                        }
                                        else
                                        {
                                                $navcrumb["$ilpage[main]?cmd=categories"] = $phrase['_categories'];
                                                $navcrumb["$ilpage[merch]?cmd=listings"] = $phrase['_buy'];
                                                $navcrumb["$ilpage[merch]?cmd=listings&amp;cid=" . $results['cid']] = $results['title'];
                                        }
                                }
                                else if ($cattype == 'experts')
                                {
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('serviceprovidercatplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
                                                $navcrumb["$ilpage[search]?mode=experts"] = $phrase['_browse'] . ' ' . $phrase['_experts'];
                                                $navcrumb["$url"] = $results['title'];
                                                unset($url);
                                        }
                                        else
                                        {
                                                $navcrumb["$ilpage[search]?mode=experts"] = $phrase['_browse'] . ' ' . $phrase['_experts'];
                                                $navcrumb["$ilpage[search]?mode=experts&cid=" . $results['cid']] = $results['title'];   
                                        }
                                }
                                else if ($cattype == 'portfolio')
                                {
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('portfoliocatplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
                                                $navcrumb["$url"] = $results['title'];
                                                unset($url);
                                        }
                                        else
                                        {
                                                $navcrumb["$ilpage[portfolio]?cid=" . $results['cid']] = $results['title'];
                                        }
                                }
                        }        
                }
                
                ($apihook = $ilance->api('breadcrumb_end')) ? eval($apihook) : false;
                
                $ilance->timer->stop();
                DEBUG("breadcrumb(\$cid = $cid, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
        }        
        
        /**
        * Function to fetch the profile verification count for a user within a particular category.
        *
        * @param       integer      category id
        * @param       integer      user id
        *
        * @return      integer      Returns xxx
        */
        function fetch_profile_verification_count($cid = 0, $userid = 0)
        {
                global $ilance, $myapi;
                
                $count = 0;
                $groups = $ilance->db->query("
                        SELECT groupid
                        FROM " . DB_PREFIX . "profile_groups
                        WHERE cid = '".intval($cid)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($groups) > 0)
                {
                        // for every group, fetch questionid's
                        while ($group = $ilance->db->fetch_array($groups))
                        {
                                $questions = $ilance->db->query("
                                        SELECT questionid
                                        FROM " . DB_PREFIX . "profile_questions
                                        WHERE groupid = '".$group['groupid']."'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($questions) > 0)
                                {
                                        // for every questionid in this group, count answers
                                        while ($res = $ilance->db->fetch_array($questions))
                                        {
                                                $verified = $ilance->db->query("
                                                        SELECT answerid
                                                        FROM " . DB_PREFIX . "profile_answers
                                                        WHERE questionid = '".$res['questionid']."'
                                                            AND user_id = '".intval($userid)."'
                                                            AND isverified = '1'
                                                            AND visible = '1'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($verified) > 0)
                                                {
                                                        $count += 1;
                                                }
                                        }
                                }
                        }
                }
                return $count;
        }
        
        /**
        * Function to fetch the entire profile count within any category.
        *
        * @param       integer      category id
        *
        * @return      integer      Returns xxx
        */
        function fetch_profile_question_count($cid = 0)
        {
                global $ilance, $myapi;
                
                $count = 0;
                $groups = $ilance->db->query("
                        SELECT groupid, cid
                        FROM " . DB_PREFIX . "profile_groups
                        WHERE cid = '".intval($cid)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($groups) > 0)
                {
                        while ($group = $ilance->db->fetch_array($groups))
                        {
                                // count questions in this profile group
                                $questions = $ilance->db->query("
                                        SELECT COUNT(*) AS count
                                        FROM " . DB_PREFIX . "profile_questions
                                        WHERE groupid = '".$group['groupid']."'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($questions) > 0)
                                {
                                        $res = $ilance->db->fetch_array($questions);
                                        $count += $res['count'];
                                }
                                
                        }
                }
                
                return $count;
        }
        
        /**
        * Function to fetch the number of portfolio items within a particular category
        *
        * @param       integer      category id
        *
        * @return      integer      Returns xxx
        */
        function fetch_portfolio_count($cid = 0)
        {
                global $ilance, $myapi;
                
                $count = 0;
                $items = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "attachment
                        WHERE category_id = '".intval($cid)."'
                            AND attachtype = 'portfolio'
                            AND visible = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($items) > 0)
                {
                        $item = $ilance->db->fetch_array($items);
                        $count = intval($item['count']);
                }
                
                return $count;
        }
        
        /**
        * Function to fetch the answer count for the profile question within a category for a particular user.
        *
        * @param       integer      category id
        * @param       integer      user id
        *
        * @return      integer      Returns xxx
        */
        function fetch_profile_answer_count($cid = 0, $userid = 0)
        {
                global $ilance, $myapi;
                
                $count = 0;
                
                // select groupid's this category id belongs to
                $groups = $ilance->db->query("
                        SELECT groupid
                        FROM " . DB_PREFIX . "profile_groups
                        WHERE cid = '".intval($cid)."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($groups) > 0)
                {
                        // for every group, fetch questionid's
                        while ($group = $ilance->db->fetch_array($groups))
                        {
                                $questions = $ilance->db->query("
                                        SELECT questionid
                                        FROM " . DB_PREFIX . "profile_questions
                                        WHERE groupid = '".$group['groupid']."'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($questions) > 0)
                                {
                                        // for every questionid in this group, count answers
                                        while ($res = $ilance->db->fetch_array($questions))
                                        {
                                                $answers = $ilance->db->query("
                                                        SELECT answerid
                                                        FROM " . DB_PREFIX . "profile_answers
                                                        WHERE questionid = '".$res['questionid']."'
                                                            AND user_id = '".intval($userid)."'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($answers) > 0)
                                                {
                                                        $count += 1;
                                                }
                                        }
                                }
                        }
                }
                
                return $count;
        }
        
        /**
        * Function to print the searchable category questions auction count sum.
        * For example, if a seller posted an item under DVD and answered "Horror" as the
        * question, Horror (1) would be shown.  Additionally, this counter would
        * be affected if and when the searcher enters existing search patterns such as
        * selecting another category question at the same time or keywords being used.
        *
        * @param       integer      question id
        * @param       string       choice
        * @param       string       category type (service/product)
        * @param       array        current search sql info (optional)
        *
        * @return      integer      Returns item count
        */
        function searchable_question_count($qid = 0, $choice = '', $cattype = '', $sqlquery = array())
        {
                global $ilance, $ilconfig, $myapi;
                
                $ilance->timer->start();
                
                // #### require search backend #################################
                require_once(DIR_CORE . 'functions_search.php');
                
                $supported = array('service','product');
                
                if (isset($cattype) AND !in_array($cattype, $supported) OR !isset($cattype) OR empty($cattype))
                {
                        return 0;
                }
                
                $table = ($cattype == 'service') ? 'project_answers' : 'product_answers';
                $sqlcount = count($sqlquery);
                
                $sqlquery['genrequery'] = '';
                if (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid']))
                {
                        // #### question groups selected : &qid=9.1,8.1,etc
                        $qids = explode(',', $ilance->GPC['qid']);
                        
                        $tempgenrequery = '';
                        $sqlquery['genrequery'] .= "AND (p.project_id IN(";
                        
                        foreach ($qids AS $keyquestionid => $keyanswerid)
                        {
                                $aids = explode('.', $keyanswerid);
                                
                                if (isset($aids[1]) AND !empty($aids[1]) AND isset($aids[0]) AND !empty($aids[0]))
                                {
                                        $tempgenrequery .= fetch_searchable_sql_condition($aids[0], $aids[1], $cattype);
                                }
                        }
                        
                        if (!empty($tempgenrequery))
                        {
                                $tempgenrequery = (strrchr($tempgenrequery, ',')) ? substr($tempgenrequery, 0, -1) : $tempgenrequery;
                                $sqlquery['genrequery'] .= $tempgenrequery;
                                $sqlquery['genrequery'] .= ")) ";
                        }
                        else
                        {
                                $sqlquery['genrequery'] = '';
                        }
                        
                        unset($tempgenrequery, $qids);
                }
                
                $count = 0;
                
                $extraquery = "";
                if ($sqlcount > 0)
                {
                        $extraquery = "
                                $sqlquery[hidequery]
                                $sqlquery[userquery]
                                $sqlquery[projectstate]
                                $sqlquery[projectdetails]
                                $sqlquery[keywords]
                                $sqlquery[categories]
                                $sqlquery[options]
                                $sqlquery[pricerange]
                                $sqlquery[radius]
                                $sqlquery[location]
                                $sqlquery[timestamp]
                                $sqlquery[projectstatus]
                        ";
                }
                
                // #### for category maps..
                if (empty($sqlquery['leftjoin']))
                {
                        $sqlquery['leftjoin'] = "LEFT JOIN " . DB_PREFIX . "users u ON p.user_id = u.user_id ";
                }
                
                $sql = $ilance->db->query("
                        SELECT answers.questionid, answers.answer, answers.project_id, p.status
                        FROM " . DB_PREFIX . $table . " answers
                        LEFT JOIN " . DB_PREFIX . "projects p ON (answers.project_id = p.project_id)
                        $sqlquery[leftjoin]                        
                        WHERE questionid = '" . intval($qid) . "'
                            AND answer != ''
                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "AND p.visible = '1'") . "
                            $sqlquery[genrequery]
                            $extraquery
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $choice = ilance_htmlentities($choice);
                        while ($answers = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $decrypted = (is_serialized($answers['answer'])) ? unserialize($answers['answer']) : $answers['answer'];                                
                                if (isset($decrypted) AND is_array($decrypted))
                                {
                                        foreach ($decrypted AS $answervalue)
                                        {
                                                //$answervalue = htmlentities($answervalue, ENT_QUOTES);
                                                $answervalue = ilance_htmlentities($answervalue);
                                                if (mb_strtolower($answervalue) == mb_strtolower($choice))
                                                {
                                                        if ($answers['status'] == 'open')
                                                        {
                                                                $count++;
                                                        }
                                                }
                                        }
                                }
                        }
                }    
                
                $ilance->timer->stop();
                DEBUG("searchable_question_count(\$qid = $qid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                
                return $count;
        }
        
        /**
        * Function to print an html formatted representation of the searchable question category genres under a specific category
        * when viewing the search results
        *
        * @param       integer      category id
        * @param       bool         show category counters
        * @param       integer      current level
        * @param       integer      current show view level
        * @param       boolean      force showing of links for category maps (default false)
        * @param       string       category type (optional if forcing links)
        * @param       string       category title (optional if forcing links)
        *
        * @return      string       Returns HTML formatted searchable question output
        */
        function print_searchable_questions($cid = 0, $showcount = 1, $level = 0, $viewlevel = -1, $forcelinks = false, $cattype = '', $title = '')
        {
                global $ilance, $myapi, $ilconfig, $ilpage, $phrase, $ilcollapse, $sqlquery, $show, $block, $blockcolor, $scriptpage, $page_url;
                
                $ilance->timer->start();
                
                if ($cid == 0)
                {
                        return false;
                }
                
                $paddingtopfirst = 0;
                $paddingtop = 5;
                
                if ($viewlevel == -1)
                {
                        $level++;
                }
                else
                {
                        $level = $viewlevel;
                }
                
                $res = array();
                if ($forcelinks)
                {
                        // forcing links via category map
                        $res['cattype'] = $cattype;
                        $res['title'] = $title;
                }
                else
                {
                        $res['cattype'] = $this->cattype($_SESSION['ilancedata']['user']['slng'], $cid);
                        $res['title'] = $this->title($_SESSION['ilancedata']['user']['slng'], $res['cattype'], $cid);
                }
                
                $table = ($res['cattype'] == 'service') ? 'project_questions' : 'product_questions';
                $seotype = ($res['cattype'] == 'service') ? 'servicesearchquestion' : 'productsearchquestion';
                $pagetype = ($res['cattype'] == 'service') ? HTTP_SERVER . $ilpage['rfp'] : HTTP_SERVER . $ilpage['merch'];
                $urlbase = ((defined('LOCATION') AND (LOCATION == 'rfp' OR LOCATION == 'search' OR LOCATION == 'merch')) ? $ilpage['search'] : $pagetype);
                $formattedhtml = $catcounter = '';
                $questioncount = 0;
                
                $pid = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "parentid");
                $extracids = "AND (cid = '" . intval($cid) . "' OR cid = '-1')";
                $var = $this->fetch_parent_ids($cid);
                $explode = explode(',', $var);
                if (in_array($pid, $explode))
                {
                        $extracids = "AND (FIND_IN_SET(cid, '$var') AND recursive = '1' OR cid = '-1')";
                }
                unset($explode, $var);
                
                $questions = $ilance->db->query("
                        SELECT questionid, inputtype, multiplechoice, question_" . $_SESSION['ilancedata']['user']['slng'] . " AS question, recursive
                        FROM " . DB_PREFIX . $table . "
                        WHERE cansearch = '1'
                                $extracids
                                AND visible = '1'
                        ORDER BY sort ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($questions) > 0)
                {
                        $count = 0;
                        while ($question = $ilance->db->fetch_array($questions, DB_ASSOC))
                        {
                                if (!empty($question['multiplechoice']))
                                {
                                        $questioncount++;
                                        $choices = explode('|', $question['multiplechoice']);
                                        
                                        $html[$count]['htmlhead'] = '';
                                        $html[$count]['html'] = '';
                                        
                                        // #### start the content box for this category question
                                        $html[$count]['htmlblockstart'] = ((defined('LOCATION') AND LOCATION == 'search')
                                                ? '<div class="block' . $block . '-content alt1" id="collapseobj_leftnav_specifics_' . $question['questionid'] . '" style="{collapse[collapseobj_leftnav_specifics_' . $question['questionid'] . ']} padding:6px"><div style="padding-top:6px"></div>'
                                                : '');
                                        
                                        // #### end the content box for this category question
                                        $html[$count]['htmlblockend'] = ((defined('LOCATION') AND LOCATION == 'search')
                                                ? '</div>'
                                                : '');
                                        
                                        $i = 0;
                                        foreach ($choices AS $choice)
                                        {
                                                if (!empty($choice))
                                                {
                                                        $i++;
                                                        
                                                        $itemcount = 0;
                                                        if ($ilconfig['globalfilters_enablecategorycount'] AND $showcount)
                                                        {
                                                                $itemcount = $this->searchable_question_count($question['questionid'], $choice, $res['cattype'], $sqlquery);
                                                                $catcounter = (($itemcount >= 0)
                                                                               ? '&nbsp;<span class="smaller gray">(' . $itemcount . ')</span>'
                                                                               : '');
                                                        }
                                                        
                                                        // #### currently selected genre question handler
                                                        $x = 0;
                                                        if (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid']))
                                                        {
                                                                // #### question groups selected : &qid=9.1,8.1,etc
                                                                if (strrchr($ilance->GPC['qid'], ',') == true)
                                                                {
                                                                        $temp = explode(',', $ilance->GPC['qid']);
                                                                        $aids = array();
                                                                        foreach ($temp AS $key => $value)
                                                                        {
                                                                                $tmp = explode('.', $value);
                                                                                $aids[$tmp[0]][] = $tmp[1];
                                                                                $x++;
                                                                        }
                                                                }
                                                                else if (strrchr($ilance->GPC['qid'], ',') == false)
                                                                {
                                                                        $tmp = explode('.', $ilance->GPC['qid']);
                                                                        $aids = array();
                                                                        $aids[$tmp[0]][] = $tmp[1];
                                                                        $x++;
                                                                }
                                                        }
                                                        
                                                        $removeurl = urldecode(PAGEURL); //&qid=9.1
                                                        $removeurl = rewrite_url($removeurl, '' . $question['questionid'] . '.' . $i . ',');
                                                        $removeurl = rewrite_url($removeurl, ',' . $question['questionid'] . '.' . $i);
                                                        $removeurl = rewrite_url($removeurl, '' . $question['questionid'] . '.' . $i);
                                                        
                                                        // #### currently selected genre questions
                                                        if (!empty($aids[$question['questionid']]) AND in_array($i, $aids[$question['questionid']]))
                                                        {
                                                                $removeurl = ($x == 1) ? rewrite_url($removeurl, 'qid=') : $removeurl;
                                                                
                                                                /*$html[$count]['html'] .= (defined('LOCATION') AND LOCATION == 'search')
                                                                        ? '<div style="padding-left:2px"><div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_' . $question['questionid'] . '_' . $i . '" /></span><span class="black"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_' . $question['questionid'] . '_' . $i . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_' . $question['questionid'] . '_' . $i . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . ilance_htmlentities($choice) . '</strong></a></span>' . $catcounter . '</div></div>'
                                                                        : '<div style="padding-top:3px"><span class="black"><strong>' . ilance_htmlentities($choice) . '</strong></span>'  . $catcounter . '</div>';*/
                                                                $html[$count]['html'] .= ((defined('LOCATION') AND LOCATION == 'search')
                                                                        ? '<div style="padding-left:2px; padding-bottom:4px">
                                                                                <span style="float:right; padding-top:2px"><span class="smaller blue" style="font-size:11px"></span></span>
                                                                                <span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_' . $question['questionid'] . '_' . $i . '" /></span>
                                                                                <span class="black"><a href="' . $removeurl . '" onMouseOver="rollovericon(\'sel_' . $question['questionid'] . '_' . $i . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onMouseOut="rollovericon(\'sel_' . $question['questionid'] . '_' . $i . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . ilance_htmlentities($choice) . '</strong></a></span>' . $catcounter . '
                                                                           </div>'
                                                                        : '');
                                                        }
                                                        
                                                        // #### unselected genre questions (clickable)
                                                        else
                                                        {
                                                                if ($ilconfig['globalauctionsettings_seourls'])
                                                                {
                                                                        if (defined('LOCATION') AND LOCATION == 'search')
                                                                        {
                                                                                $html[$count]['html'] .= (($itemcount > 0)
                                                                                        ? '<div style="padding-left:2px; padding-bottom:4px">
                                                                                                <span style="float:right; padding-top:2px"><span class="smaller blue" style="font-size:11px"></span></span>
                                                                                                <span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_' . $question['questionid'] . '_' . $i . '" /></span>
                                                                                                <span class="blueonly">' . construct_seo_url($seotype, $cid, 0, $res['title'], '', 0, ilance_htmlentities($choice), $question['questionid'], (int)$i, '', 'onMouseOver="rollovericon(\'unsel_' . $question['questionid'] . '_' . $i . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onMouseOut="rollovericon(\'unsel_' . $question['questionid'] . '_' . $i . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')"') . '</span>' . $catcounter . '
                                                                                           </div>'
                                                                                        : '');
                                                                        }
                                                                        else
                                                                        {
                                                                                /*$html[$count]['html'] .= (($itemcount > 0)
                                                                                        ? '<div style="padding-left:' . ($this->fetch_level_padding($viewlevel) + 15) . 'px; padding-top:3px"><span class="blue">' . construct_seo_url($seotype, $cid, 0, $res['title'], '', 0, ilance_htmlentities($choice), $question['questionid'], (int)$i) . '</span>' . $catcounter . '</div>'
                                                                                        : '');*/
                                                                                $html[$count]['html'] .= (($itemcount > 0)
                                                                                        ? ''
                                                                                        : '');
                                                                                        
                                                                        }
                                                                }
                                                                else
                                                                {
                                                                        if (defined('LOCATION') AND LOCATION == 'search')
                                                                        {
                                                                                $html[$count]['html'] .= (($itemcount > 0)
                                                                                        ? '<div style="padding-left:2px; padding-bottom:4px">
                                                                                                <span style="float:right; padding-top:2px"><span class="smaller blue" style="font-size:11px"></span></span>
                                                                                                <span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_' . $question['questionid'] . '_' . $i . '" /></span>
                                                                                                <span class="blueonly"><a href="' . $urlbase . '?cid=' . $cid . print_hidden_fields(true, array('cid','cmd','page','list'), false, '', '', $htmlentities = true, $urldecode = true) . '&amp;qid=' . $question['questionid'] . '.' . (int)$i . '" onMouseOver="rollovericon(\'unsel_' . $question['questionid'] . '_' . $i . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onMouseOut="rollovericon(\'unsel_' . $question['questionid'] . '_' . $i . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . ilance_htmlentities($choice) . '</a></span>' . $catcounter . '
                                                                                           </div>'
                                                                                        : '');
                                                                        }
                                                                        else
                                                                        {
                                                                                /*$html[$count]['html'] .= (($itemcount > 0)
                                                                                        ? '<div style="padding-left:' . ($this->fetch_level_padding($viewlevel) + 16) . 'px; padding-top:3px"><span class="blue"><a href="' . $urlbase . '?cid=' . $cid . print_hidden_fields(true, array('cid','cmd','page','list'), false, '', '', $htmlentities = true, $urldecode = true) . '&qid[' . $question['questionid'] . ']=' . (int)$i . '">' . ilance_htmlentities($choice) . '</a></span>' . $catcounter . '</div>'
                                                                                        : '');*/
                                                                                $html[$count]['html'] .= (($itemcount > 0)
                                                                                        ? ''
                                                                                        : '');
                                                                        }
                                                                }        
                                                        }
                                                }
                                                
                                                if (!empty($html[$count]['html']))
                                                {
                                                        if (defined('LOCATION') AND LOCATION == 'search')
                                                        {
                                                                $clear = '';
                                                                if (!empty($ilance->GPC['qid']))
                                                                {
                                                                        $qids = explode(',', $ilance->GPC['qid']);
                                                                        foreach ($qids AS $keyquestionid => $keyanswerid)
                                                                        {
                                                                                $aids = explode('.', $keyanswerid);					
                                                                                if (isset($aids[1]) AND !empty($aids[1]))
                                                                                {
                                                                                        if (isset($aids[0]) AND $question['questionid'] == $aids[0])
                                                                                        {
                                                                                                //$showqidurl = rewrite_url(PAGEURL, 'qid[' . $keyquestionid . ']=' . $keyanswerid);
                                                                                                $showqidurl = rewrite_url(PAGEURL, 'qid=' . $aids[0] . '.' . $aids[1]);
                                                                                                $clear = '<span style="float:right; padding-top:2px"><span class="smaller blue" style="font-size:11px"><a href="' . $showqidurl . '">' . $phrase['_clear'] . '</a></span></span>';
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                                
                                                                // #### question group header 
                                                                $html[$count]['htmlhead'] = '
                                                                        <div class="block' . $block . '-content-' . $blockcolor . '" style="padding-top:9px; padding-bottom:9px" onClick="toggle(\'leftnav_specifics_' . $question['questionid'] . '\')" onMouseOver="this.style.cursor=\'pointer\'" onMouseOut="this.style.cursor=\'\'">' . $clear . '
                                                                                <span style="float:left; padding-top:5px; padding-right:10px"><img id="collapseimg_leftnav_specifics_' . $question['questionid'] . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'expand{collapse[collapseimg_leftnav_specifics_' . $question['questionid'] . ']}.gif" border="0" alt="" /></span>
                                                                                <span class="gray"><strong>' . $question['question'] . '</strong></span>
                                                                        </div>';
                                                        }
                                                        else
                                                        {
                                                                //$html[$count]['htmlhead'] = '<div class="black" style="padding-top:9px; padding-bottom:4px"><strong>' . $question['question'] . '</strong></div>';
                                                        }
                                                        
                                                        unset($clear, $showqidurl);
                                                }
                                        }
                                }
                                
                                $count++;                                
                        }
                        
                        $bit['visible'] = $bit['hidden'] = '';
                        
                        $hidden = (defined('LOCATION') AND LOCATION == 'search')
                                ? '<div class="block' . $block . '-content-' . $blockcolor . '" style="padding-top:9px; padding-bottom:9px"><div class="smaller"><a href="javascript:void(0)" onclick="toggle_more(\'showmore_' . $cid . '\', \'moretext_' . $cid . '\', \'' . $phrase['_more_options'] . '\', \'' . $phrase['_less_options'] . '\', \'showmoreicon_' . $cid . '\')"><span id="moretext_' . $cid . '" class="gray" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmore_$cid"]) ? $phrase['_less_options'] : $phrase['_more_options']) . '</strong></span></a> <img id="showmoreicon_' . $cid . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmore_$cid"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div></div>'
                                : '<div style="padding-left:' . $this->fetch_level_padding($viewlevel) . 'px; padding-bottom:6px; padding-top:5px" class="smaller"><a href="javascript:void(0)" onclick="toggle_more(\'showmore_' . $cid . '\', \'moretext_' . $cid . '\', \'' . $phrase['_more_options'] . '\', \'' . $phrase['_less_options'] . '\', \'showmoreicon_' . $cid . '\')"><span id="moretext_' . $cid . '" class="gray" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmore_$cid"]) ? $phrase['_less_options'] : $phrase['_more_options']) . '</strong></span></a> <img id="showmoreicon_' . $cid . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmore_$cid"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
                
                        if (!empty($html) AND is_array($html))
                        {
                                $c = 0;
                                foreach ($html AS $key => $array)
                                {
                                        if (!empty($array['htmlhead']) AND !empty($array['html']))
                                        {
                                                $c++;
                                                
                                                if ($c <= $ilconfig['globalauctionsettings_catquestiondepth'])
                                                {
                                                        $bit['visible'] .= $html[$key]['htmlhead'] . $html[$key]['htmlblockstart'] . $html[$key]['html'] . $html[$key]['htmlblockend'];
                                                }
                                                else
                                                {
                                                        $bit['hidden'] .= $html[$key]['htmlhead'] . $html[$key]['htmlblockstart'] . $html[$key]['html'] . $html[$key]['htmlblockend'];
                                                }
                                        }
                                }
                        }
                        
                        if ($questioncount <= $ilconfig['globalauctionsettings_catquestiondepth'])
                        {
                                $hidden = '';
                        }
                        
                        // rebuild display options
                        $formattedhtml = "$bit[visible] <div id=\"showmore_$cid\" style=\"" . (!empty($ilcollapse["showmore_$cid"]) ? $ilcollapse["showmore_$cid"] : 'display: none;') . "\">$bit[hidden]</div>$hidden";
                        
                        // tell our template we'll be showing category genre questions
                        $show['categoryfinder'] = ((empty($bit['visible']) OR $bit['visible'] == '') ? false : true);
                }
                
                $ilance->timer->stop();
                DEBUG("print_searchable_questions(\$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                
                return $formattedhtml;
        }
        
        /**
        * Function to fetch the padding based on a particular category level we're currently viewing
        *
        * @param       integer      level
        *
        * @return      string       Returns HTML formatted table with category results
        */
        function fetch_level_padding($level = 0)
        {
                $padding = array(
                        '0' => '0',
                        '1' => '0',
                        '2' => '15',
                        '3' => '30',
                        '4' => '45',
                        '5' => '60',
                        '6' => '75',
                        '7' => '90',
                        '8' => '105',
                );
                
                return $padding["$level"];
        }
        
        /**
        * Function to print a category's "new" icon representing any new auctions posted within that category
        *
        * @param       integer      category id
        * @param       bool         category type (service/product)
        * @param       integer      days (default 7)
        *
        * @return      string       Returns xxx
        */
        function print_category_newicon($cid = 0, $cattype = '', $daysnew = 7)
        {
                global $ilance, $myapi, $ilconfig, $phrase;
                
                $ilance->timer->start();
                
                return; // for now
                
                if (defined('LOCATION') AND LOCATION == 'portfolio')
                {
                        return;
                }
                if ($cattype != 'service' AND $cattype != 'product')
                {
                        return;
                }
                
                $html = false;
                $daysnew = $ilconfig['globalauctionsettings_newicondays'];
                
                $sql = $ilance->db->query("
                        SELECT project_id
                        FROM " . DB_PREFIX . "projects
                        WHERE cid = '" . intval($cid) . "'
                            AND status = 'open'
                            AND project_state = '" . $ilance->db->escape_string($cattype) . "'
                            AND date_added >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysnew DAY)
                            AND visible = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html = '<span title="' . $phrase['_new_projects_have_been_posted'] . ': ' . $phrase['_past'] . ' ' . $daysnew . ' ' . $phrase['_days_lower'] . '"><em><sup style="color:red">' . $phrase['_new'] . '</em></span>';
                }
                
                $ilance->timer->stop();
                DEBUG("print_category_newicon(\$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                
                return $html;
        }
            
        /**
        * Function to count the number of ads within a Want Ads category
        *
        * @param       integer      category id
        *
        * @return      string       Returns xxx
        */
        function wantads_in_category($cid = 0)
        {
                global $ilance, $myapi;
                
                $sql = $ilance->db->query("
                    SELECT COUNT(*) AS nr_pr
                    FROM " . DB_PREFIX . "wantads
                    WHERE cid = '" . intval($cid) . "'
                        AND wantstatus = 'wanted'
                        AND visible = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql)> 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        return intval($res['nr_pr']);
                }
                
                return 0;
        }
        
        /**
        * Function to bridge the category system for usage with Want Ads and other add on products like Stores
        *
        * @param       string       category mode
        * @param       integer      columns (default 3)
        * @param       integer      category id
        * @param       integer      store id (optional)
        *
        * @return      string       Returns HTML formatted category output display
        */
        function construct_categories($mode = '', $columns = 3, $cid = 0, $storeid = '')
        {
                global $ilance, $myapi, $phrase, $ilconfig, $ilpage;
                
                ($apihook = $ilance->api('categories_construct_categories_start')) ? eval($apihook) : false;
                ($apihook = $ilance->api('categories_construct_categories_end')) ? eval($apihook) : false;
                
                return $html;
        }
        
        /**
        * Function to
        *
        * @param       integer      parent category id
        * @param       integer      current level
        * @param       string       category type (service/product)
        * @param       string       option groupns
        * @param       string       add spaces (optional)
        *
        * @return      string       Returns xxx
        */
        function display_children($parentid = 0, $level = 0, $cattype = '', $optgroups = '', $addspaces = '')
        {
                global $ilance, $myapi;
                
                $ilance->timer->start();
                
                $html = '';
                
                // #### core service and product categories ####################
                if ($cattype == 'service' OR $cattype == 'product') 
                {
                        $result = $ilance->db->query("
                                SELECT cid, parentid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title, canpost
                                FROM " . DB_PREFIX . "categories
                                WHERE parentid = '" . intval($parentid) . "'
                                        AND visible = '1'
                                        AND cattype = '$cattype'
                        ", 0, null, __FILE__, __LINE__);
                }
        
                // #### other external addon product category logic ############
                ($apihook = $ilance->api('categories_display_children_condition')) ? eval($apihook) : false;
        
                // #### display each child #####################################
                while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                {
                        $html .= ($row['parentid'] == '-1' OR $row['parentid'] == '0' OR $row['parentid'] == '') ? '<option value="' . $row['cid'] . '">' . stripslashes($row['title']) . '</option>' : '<option value="' . $row['cid'] . '">' . str_repeat('&nbsp;&nbsp;&nbsp;', $level) . stripslashes($row['title']) . '</option>';
                        $html .= (isset($cattype) AND !empty($cattype)) ? $this->display_children($row['cid'], ($level + 1), $cattype) : '';
                }
                
                // #### other external addon product category logic ############
                ($apihook = $ilance->api('categories_display_children_end')) ? eval($apihook) : false;
                
                $ilance->timer->stop();
                DEBUG("display_children(\$parentid = $parentid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                
                return $html;
        }
        
        /**
        * Function to provide add-on products with a useable API bridge allowing the generation of multi-select fields such as categories.
        *
        * @param       string       category type
        * @param       string       selected category
        * @param       bool         single pulldown mode?
        * @param       bool         hide all categories? (default false)
        * @param       string       select field name
        * @param       bool         show "Please Select: " option in the pulldown (default false)
        * @param       integer      category level (used for pulldown option spacing) (default 1)
        *
        * @return      string       Returns HTML formatted pulldown/select menu
        */
        function api_multicategory_select($cattype = '', $selected = '', $singlepulldown = false, $hideallcats = false, $selectname = 'cid', $pleaseselect = false, $level = 0)
        {
                global $ilance, $myapi, $phrase;
                
                $ilance->timer->start();
                $html = '';
                
                // #### single pulldown select menu ############################
                if ($singlepulldown)
                {
                        // #### begin select menu ##############################
                        $html .= '<select name="' . $selectname . '" style="font-family: verdana">';
                        $html .= ($hideallcats == false) ? '<option value="">' . $phrase['_best_matching'] . '</option><option value="">----------------------------------</option>' : '';
                        $html .= ($pleaseselect) ? '<option value="">' . $phrase['_please_select'] . '</option>' : '';
                        
                        // #### handler for core ilance service/product categories
                        $html .= ($cattype == 'service' OR $cattype == 'product') ? $this->display_children($selected, ($level + 1), $cattype) : '';
                        
                        // #### handler for other addon products (lancebb, wantads, etc)
                        ($apihook = $ilance->api('categories_api_multicategory_select_single_condition')) ? eval($apihook) : false;
                        
                        // #### end select menu ################################
                        $html .= '</select>';
                }
                
                // #### multiple pulldown select menu ##########################
                else
                {
                        // #### handler for other addon products (lancebb, wantads, etc)
                        ($apihook = $ilance->api('categories_api_multicategory_select_multiple_condition')) ? eval($apihook) : false;
                }
                
                $ilance->timer->stop();
                DEBUG("api_multicategory_select(\$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                
                return $html;
        }
        
        /**
        * Function to set all the levels in proper format for the main category system
        */
        function set_levels()
        {
                global $ilance;
                
                $sql = $ilance->db->query("
                        SELECT cid, parentid, level
                        FROM " . DB_PREFIX . "categories
                ", 0, null, __FILE__, __LINE__);
                while ($cats = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        if ($cats['parentid'] == 0)
                        {    
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "categories
                                        SET level = '1'
                                        WHERE cid = '" . $cats['cid'] . "'
                                ", 0, null, __FILE__, __LINE__);                    
                        }
                        else
                        {
                                $level = 1;
                                $this->set_levels_update($cats['cid'], $cats['parentid'], $level);
                        }
                }
        }
        
        /**
        * Function to set all levels and to handle the updating
        *
        * @param       integer        category id
        * @param       integer        parent id
        * @param       integer        level
        * @param       integer        category id to save
        *
        * @return      nothing
        */
        function set_levels_update($cid, $parentid, $level, $cid_save = '')
        {
                global $ilance;
                
                if (empty($cid_save))
                {
                        $cid_save = $cid;
                }
                
                $sql = $ilance->db->query("
                        SELECT cid, parentid, level
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($parentid) . "'
                ", 0, null, __FILE__, __LINE__);
                                 
                $category = $ilance->db->fetch_array($sql, DB_ASSOC);
                
                if ($category['parentid'] == 0)
                {
                        $level = $level + 1;
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "categories
                                SET level = '" . intval($level) . "'
                                WHERE cid = '" . intval($cid_save) . "'
                        ", 0, null, __FILE__, __LINE__);                    
                }
                else
                {
                        $level = $level + 1;
                        $this->set_levels_update($category['cid'], $category['parentid'], $level, $cid_save);
                }                 
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
        function fetch_children_ids($cid = 'all', $cattype = 'service', $extraquery = '')
        {
                global $ilance, $show;
                
                ($apihook = $ilance->api('fetch_children_ids_start')) ? eval($apihook) : false;
                
                $c = 0;                
                $ids = '';
                
                if (($ids = $ilance->cache->fetch("fetch_children_ids:$cid:$cattype:$extraquery")) == false)
		{
                        if ($cid == 'all')
                        {
                                $sql = $ilance->db->query("
                                        SELECT cid
                                        FROM " . DB_PREFIX . "categories
                                        WHERE cattype = '" . $ilance->db->escape_string($cattype) . "'
                                                AND visible = '1'
                                                $extraquery
                                        ORDER BY lft ASC
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                        {
                                                $ids .= $res['cid'] . ',';
                                        }
                                }
                        }
                        else
                        {
                                $sql = $ilance->db->query("
                                        SELECT lft, rgt
                                        FROM " . DB_PREFIX . "categories
                                        WHERE cid = '" . intval($cid) . "'
                                                AND cattype = '" . $ilance->db->escape_string($cattype) . "'
                                                $extraquery
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                        
                                        $sql2 = $ilance->db->query("
                                                SELECT cid
                                                FROM " . DB_PREFIX . "categories
                                                WHERE lft >= '" . intval($res['lft']) . "'
                                                        AND rgt <= '" . intval($res['rgt']) . "'
                                                        AND cattype = '" . $ilance->db->escape_string($cattype) . "'
                                                        $extraquery
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
                                                {
                                                        if ($res2['cid'] != $cid)
                                                        {
                                                                $ids .= $res2['cid'] . ',';
                                                        }        
                                                }
                                        }
                                }
                        }
                        
                        $ilance->cache->store("fetch_children_ids:$cid:$cattype:$extraquery", $ids);
                }
                
                ($apihook = $ilance->api('fetch_children_ids_end')) ? eval($apihook) : false;
                
                return $ids;
        }
        
        /**
        * Function to fetch all parent category id numbers recursivly in comma separated values based on a child category id number.
        * This function is useful because it reads from the cache and does not hit the database.
        *
        * @param       string         category id number (or all)
        * @param       string         category type (service or product)
        *
        * @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
        */
        function fetch_parent_ids($cid = 0, $extraquery = '')
        {
                global $ilance;
                
                ($apihook = $ilance->api('fetch_parent_ids_start')) ? eval($apihook) : false;
                
                $ids = '';
                
                if (($ids = $ilance->cache->fetch("fetch_parent_ids:$cid:$extraquery")) == false)
		{
                        $sql = $ilance->db->query("
                                SELECT parent.cid
                                FROM " . DB_PREFIX . "categories AS node,
                                " . DB_PREFIX . "categories AS parent
                                WHERE node.lft BETWEEN parent.lft AND parent.rgt
                                        AND node.cid = '" . intval($cid) . "'
                                        $extraquery
                                ORDER BY parent.lft
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $ids .= $res['cid'] . ',';
                                }
                                
                                $ilance->cache->store("fetch_parent_ids:$cid:$extraquery", $ids);
                        }
                }
                
                ($apihook = $ilance->api('fetch_parent_ids_end')) ? eval($apihook) : false;
                
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
        function fetch_children($cid = 0, $cattype = 'service')
        {
                global $ilance;
                
                $ilance->timer->start();
                
                $ids = $this->fetch_children_ids($cid, $cattype);
                if (empty($ids))
                {
                        $ids = $cid;
                }
                else 
                {
                        $ids = $cid . ',' . mb_substr($ids, 0, -1);
                }
                
                $ilance->timer->stop();
                DEBUG("fetch_children(\$cid = $cid, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                
                return $ids;
        }
        
        /**
        * Function to print the category jump edit/delete/actions menu from within the AdminCP > Category Manager
        *
        * @param       string         form 1 id
        * @param       string         form 2 id
        * @param       string         category id field
        *
        * @return      string         Returns HTML formatted pulldown jump menu
        */
        function print_category_jump_js($formid1 = 'ilform', $formid2 = 'ilform2', $cidid = 'cid')
        {
                global $ilconfig, $ilpage, $phrase;
                
                $html = "
<script type=\"text/javascript\">
function category_jump(catinfo, pid, level, lft, rgt)
{
        if (catinfo == 0)
        {
                alert('" . $phrase['_please_select_a_category'] . "');
                return;
        }
        else if (typeof(document.$formid1.$cidid) != 'undefined')
        {
                action = document.$formid1.$cidid.options[document.$formid1.$cidid.selectedIndex].value;
        }
        else
        {
                action = eval(\"document.$formid1.$cidid\" + catinfo + \".options[document.$formid1.$cidid\" + catinfo + \".selectedIndex].value\");
        }
        
        if (action != '')
        {
                switch (action)
                {
                        case 'edit': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=editservicecat&cid=\"; break;
                        case 'questions': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=servicequestions&cid=\"; break;
                        case 'add': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=addservicecat&cid=\"; break;
                        case 'remove': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=removeservicecat&cid=\"; break;
                }
                
                document.$formid1.reset();
                
                switch (action)
                {
                        case 'edit': jumptopage = page + catinfo + \"&pid=\" + pid + \"&level=\" + level + \"&lft=\" + lft + \"&rgt=\" + rgt; break;
                        case 'questions': jumptopage = page + catinfo + \"\"; break;
                        case 'increments': jumptopage = page + catinfo + \"\"; break;
                        case 'add': jumptopage = page + catinfo + \"\"; break;
                        case 'remove': jumptopage = page + catinfo + \"\"; break;
                }
                
                if (action == 'remove')
                {
                        var agree = confirm_js(\"" . $phrase['_please_take_a_moment_to_confirm_your_action_continue'] . "\");
                        if (agree)
                        { 
                                return window.location = jumptopage;
                        }
                        else
                        {
                                return false;
                        }
                }
                else
                {
                        window.location = jumptopage;
                }
        }
        else
        {
                alert(\"" . $phrase['_invalid_action'] . "\");
        }
}
function category_jump2(catinfo, pid, level, lft, rgt)
{
        if (catinfo == 0)
        {
                alert('" . $phrase['_please_select_a_category'] . "');
                return;
        }
        else if (typeof(document.$formid2.$cidid) != 'undefined')
        {
                action = document.$formid2.$cidid.options[document.$formid2.$cidid.selectedIndex].value;
        }
        else
        {
                action = eval(\"document.$formid2.$cidid\" + catinfo + \".options[document.$formid2.$cidid\" + catinfo + \".selectedIndex].value\");
        }
        
        if (action != '')
        {
                switch (action)
                {
                        case 'edit': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=editproductcat&cid=\"; break;
                        case 'questions': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=productquestions&cid=\"; break;
                        case 'increments': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=editincrements&cid=\"; break;
                        case 'add': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=addproductcat&cid=\"; break;
                        case 'remove': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=removeproductcat&cid=\"; break;
                }
                
                document.$formid2.reset();
                
                switch (action)
                {
                        case 'edit': jumptopage = page + catinfo + \"&pid=\" + pid + \"&level=\" + level + \"&lft=\" + lft + \"&rgt=\" + rgt; break;
                        case 'questions': jumptopage = page + catinfo + \"\"; break;
                        case 'increments': jumptopage = page + catinfo + \"\"; break;
                        case 'add': jumptopage = page + catinfo + \"\"; break;
                        case 'remove': jumptopage = page + catinfo + \"\"; break;
                }
                
                if (action == 'remove')
                {
                        var agree = confirm_js(\"" . $phrase['_please_take_a_moment_to_confirm_your_action_continue'] . "\");
                        if (agree)
                        { 
                                return window.location = jumptopage;
                        }
                        else
                        {
                                return false;
                        }
                }
                else
                {
                        window.location = jumptopage;
                }
        }
        else
        {
                alert(\"" . $phrase['_invalid_action'] . "\");
        }
}

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
		return $html;
        }
        
        
        /**
        * Function to rebuild a nested category structure in the database
        *
        * @param       integer        parent category id number (default 0)
        * @param       integer        lft node (default 1)
        */
        function rebuild_category_tree($parentid = 0, $left = 1, $cattype = 'product', $slng = 'eng')
        {
                global $ilance;
                
                // the right value of this node is the left value + 1   
                $right = $left + 1;   
               
                // get all children of this node   
                $result = $ilance->db->query("
                        SELECT cid
                        FROM " . DB_PREFIX . "categories
                        WHERE parentid = '" . intval($parentid) . "'
                                AND cattype = '" . $ilance->db->escape_string($cattype) . "'
                        ORDER BY title_$slng ASC
                ", 0, null, __FILE__, __LINE__);
                while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                {   
                        // recursive execution of this function for each child of this node   
                        // $right is the current right value, which is incremented by the rebuild_tree function   
                        $right = $this->rebuild_category_tree($row['cid'], $right, $cattype, $slng);   
                }   
               
                // we've got the left value, and now that we've processed   
                // the children of this node we also know the right value   
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "categories
                        SET lft = '" . $left . "', rgt = '" . $right . "'
                        WHERE cid = '" . $parentid . "'
                ", 0, null, __FILE__, __LINE__);
                
                // return the right value of this node + 1   
                return $right + 1;   
        }
        
        /**
        * Function to rebuild the category structure geometry for ultra fast database queries
        */
        function rebuild_category_geometry()
        {
                global $ilance;
                
                // #### try to add if does not exist
                $ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories DROP `sets`", 0, null, __FILE__, __LINE__);
                $ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
                $ilance->db->query("UPDATE " . DB_PREFIX . "categories SET `sets` = LineString(Point(-1, lft), Point(1, rgt))", 0, null, __FILE__, __LINE__);
                $ilance->db->query("CREATE SPATIAL INDEX sx_categories_sets ON " . DB_PREFIX . "categories (sets)", 0, null, __FILE__, __LINE__);
        }
        
        function rebuild_category_geometry_install()
        {
                global $ilance;
                
                // #### try to add if does not exist
                $ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories DROP `sets`", 0, null, __FILE__, __LINE__);
                $ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
                $ilance->db->query("UPDATE " . DB_PREFIX . "categories SET `sets` = LineString(Point(-1, lft), Point(1, rgt))", 0, null, __FILE__, __LINE__);
        }
		
		function fetch_children_pcgs($series_id)
		{
		global $ilance;

		$result=$ilance->db->query("select PCGS from ".DB_PREFIX."catalog_coin where coin_series_unique_no='".$series_id."'");
		if($ilance->db->num_rows($result)>0)
		{
		$count=0;
		while($row=$ilance->db->fetch_array($result))
		{
			$rs[$count]=$row['PCGS'];
			$count++;
		}
		return $rs;
		}else
		{
		return '';
		}

		}

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>