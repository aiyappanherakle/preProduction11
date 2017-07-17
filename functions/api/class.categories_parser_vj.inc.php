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
if (!class_exists('categories')) {
	exit;
}
/**
 * Category parser class to perform the majority of category parsing functions within ILance.
 *
 * @package      iLance
 * @version	$Revision: 1.0.0 $
 * @author       ILance
 */
class categories_parser_vj extends categories {
	/**
	 * Function to fetch categories recursively
	 *
	 * @param       integer      parent id
	 * @param       integer      level (default 1)
	 * @param       string       category type field
	 * @param       string       category type
	 * @param       string       category database table
	 * @param       string       seo category type
	 * @param       array        detail page to attach links
	 * @param       boolean      show category counters
	 * @param       string       short language identifier
	 * @param       string       category id field name
	 * @param       string       category title
	 * @param       boolean      is category map?
	 * @param       string       parent category title link style
	 * @param       string       child category title link style
	 * @param       integer      subcategory depth
	 * @param       string       temp string holder for hidden links used in the more link logic
	 * @param       string       temp string holder for more link
	 *
	 * @return      string       Returns HTML formatted table with category results
	 */
	function fetch_recursive_categories($parentid = 0, $level = 1, $ctypefield = '', $ctype = '', $dbtable = '', $seotype = '', $detailpage = '', $showcount = 1, $slng = 'eng', $cidfield = '', $cidtitle = '', $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $displaycolumns = 3, $tempcount = 0, $hidden_html = '', $show_html = '', $categorycache = '') {
		global $ilance, $myapi, $recursive_html, $hidden_html, $show_html, $ilconfig, $storeid, $phrase;
		$ilance->timer->start();
		//echo $level.' (' . $parentid . '),';
		$this->cats = (empty($categorycache)) ? $ilance->categories->build_array($ctype, $slng, 0, true, '', '', 0, -1, 2, $parentid) : $categorycache;
		$cols = 0;
		$numrows = count($this->cats);
		$divideby = ceil($numrows / $displaycolumns);
		$html = array();
		if ($level == 1) {
			$count = 0;
		}
		for ($i = 0; $i < $numrows; $i++) {
			if ($this->cats[$i]['visible'] AND $this->cats[$i]['parentid'] == $parentid) {
				$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount AND isset($this->cats[$i]['auctioncount']))
				? '&nbsp;<span class="smaller gray">(' . $this->cats[$i]['auctioncount'] . ')</span>'
				: '';
				// #### main parent categories #################
				if ($level == 1) {
					// #### build our first table row ######
					$recursive_html .= ($count % $displaycolumns == 0) ? '<tr><td colspan="' . $displaycolumns . '"><hr size="1" width="100%" style="color:#cccccc" /></td></tr><tr>' : '';
					// #### build our first table column ###
					$recursive_html .= '<td width="25%" valign="top">';
					// #### build parent categories ########
					$recursive_html .= ($ilconfig['globalauctionsettings_seourls'])
					? '<div style="padding-top:4px; padding-left:' . $this->fetch_level_padding($level) . 'px"><span style="' . $parentstyle . '" class="bluecat">' . construct_seo_url($seotype, $this->cats[$i]['cid'], $storeid, stripslashes($this->cats[$i]['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</span></div>'
					: '<div style="padding-top:4px; padding-left:' . $this->fetch_level_padding($level) . 'px"><span style="' . $parentstyle . '" class="bluecat"><a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>';
					// #### build category questions #######
					$recursive_html .= ($ilconfig['globalauctionsettings_catmapgenres']) ? $this->print_searchable_questions($this->cats[$i]['cid'], $showcount, $level, 0, $forcelinks = true, $this->cats[$i]['cattype'], $this->cats[$i]['title']) : '';
				}
				// #### children categories ####################
				else if ($level <= $subcatdepth) {
					$tempcount++;
					// #### hold and store our visible categories
					if ($tempcount <= $ilconfig['globalauctionsettings_catcutoff']) {
						$recursive_html .= ($ilconfig['globalauctionsettings_seourls'])
						? '<div style="padding-top:4px; padding-left:' . $this->fetch_level_padding($level) . 'px" class="bluecat">' . construct_seo_url($seotype, $this->cats[$i]['cid'], $storeid, stripslashes($this->cats[$i]['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>'
						: '<div style="padding-top:4px; padding-left:' . $this->fetch_level_padding($level) . 'px" class="bluecat"><a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . '">' . stripslashes($this->cats[$i]['title']) . '</a>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>';
						$recursive_html .= ($ilconfig['globalauctionsettings_catmapgenres'] AND $ilconfig['globalauctionsettings_catmapgenredepth'] >= $subcatdepth)
						? $this->print_searchable_questions($this->cats[$i]['cid'], $showcount, $level, $level, $forcelinks = true, $this->cats[$i]['cattype'], $this->cats[$i]['title'])
						: '';
					}
					// #### hold and store our hidden categories
					else {
						$hidden_html .= ($ilconfig['globalauctionsettings_seourls'])
						? '<div style="padding-top:4px; padding-left:' . $this->fetch_level_padding($level) . 'px" class="bluecat">' . construct_seo_url($seotype, $this->cats[$i]['cid'], $storeid, stripslashes($this->cats[$i]['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>'
						: '<div style="padding-top:4px; padding-left:' . $this->fetch_level_padding($level) . 'px" class="bluecat"><a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . '">' . stripslashes($this->cats[$i]['title']) . '</a>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>';
						$hidden_html .= ($ilconfig['globalauctionsettings_catmapgenres'] AND $ilconfig['globalauctionsettings_catmapgenredepth'] >= $subcatdepth)
						? $this->print_searchable_questions($this->cats[$i]['cid'], $showcount, $level, $level, $forcelinks = true, $this->cats[$i]['cattype'], $this->cats[$i]['title'])
						: '';
					}
				}
				// #### category cutoff logic ##################
				if ($tempcount > $ilconfig['globalauctionsettings_catcutoff']) {
					$templevel = ($level > 2) ? ($level - 1) : ($level);
					// #### build our "more/less" category linkage presentation
					$show_html = "<div id=\"showmorecats_" . $this->cats[$i]['cid'] . "\" style=\"" . (!empty($ilcollapse["showmorecats_" . $this->cats[$i]['cid'] . ""]) ? $ilcollapse["showmorecats_" . $this->cats[$i]['cid'] . ""] : 'display: none;') . "\">$hidden_html</div>" . '<div style="padding-left:' . $this->fetch_level_padding($templevel) . 'px; padding-bottom:6px; padding-top:5px"><span class="blue"><a href="javascript:void(0)" onclick="toggle_more(\'showmorecats_' . $this->cats[$i]['cid'] . '\', \'moretext_' . $this->cats[$i]['cid'] . '\', \'' . $phrase['_more'] . '\', \'' . $phrase['_less'] . '\', \'showmoreicon_' . $this->cats[$i]['cid'] . '\')"><span id="moretext_' . $this->cats[$i]['cid'] . '" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmorecats_" . $this->cats[$i]['cid'] . ""]) ? $phrase['_less'] : $phrase['_more']) . '</span></a></span>&nbsp;<img id="showmoreicon_' . $this->cats[$i]['cid'] . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmorecats_" . $this->cats[$i]['cid'] . ""]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
				} else {
					// #### reset some vars ################
					$show_html = $hidden_html = '';
				}
				// #### recursive category handler #############
				$this->fetch_recursive_categories($this->cats[$i]['cid'], ($level + 1), $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $displaycolumns, $tempcount, $hidden_html, $show_html, $this->cats);
				// #### final presentation layer construction ##
				if ($level == 1) {
					// #### end our table column ###########
					$recursive_html .= "$show_html</td>";
					$show_html = '';
					// #### end our table row ##############
					if (($count % $displaycolumns) == $divideby) {
						$recursive_html .= '</tr>';
					}
					$cols++;
					$count++;
					// #### end our table row ##############
					if ($cols == $displaycolumns) {
						$recursive_html .= '</tr>';
						$cols = 0;
					}
				}
			}
		}
		// #### fix any missing table columns ##########################
		if ($cols != $displaycolumns AND $cols != 0) {
			$neededtds = ($displaycolumns - $cols);
			for ($i = 0; $i < $neededtds; $i++) {
				$recursive_html .= '<td></td>';
			}
			$recursive_html .= '</tr>';
		}
		$ilance->timer->stop();
	}
	/**
	 * Function to print the main subcategory columns of a particular category being viewed or selected
	 *
	 * @param	integer	        number of columns to display (default 1)
	 * @param        string          category type (service, product, serviceprovider, portfolio, stores, wantads)
	 * @param        bool            show subcategories?
	 * @param        string          short language code (default is eng)
	 * @param        integer         category id
	 * @param        string          extra (optional)
	 * @param        boolean         show category counts? (default yes)
	 * @param        boolean         showing category map? (default no)
	 * @param        string          style css for parent listing titles (default blank)
	 * @param        string          style css for child listing titles (default blank)
	 * @param        integer         subcategory depth level to display (default 0 = root)
	 * @param        string          cache id (to prevent similar cache pages) (default blank)
	 */
	function print_subcategory_columns($columns = 1, $cattype = 'service', $dosubcats = 1, $slng = 'eng', $cid = 0, $extra = '', $showcount = 1, $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $cacheid = '') {
		global $ilance, $myapi, $phrase, $ilconfig, $ilpage, $show, $storeid, $storetype, $sqlquery, $categoryfinderhtml, $sqlquery, $sqlqueryads, $recursive_html, $categorycache, $block, $blockcolor;
		$ilance->timer->start();
		if (!empty($cacheid)) {
			$cacheid = '_' . $cacheid;
		}
		if ($cattype == 'service' OR $cattype == 'portfolio' OR $cattype == 'serviceprovider' OR $cattype == 'product' OR $cattype == 'stores' OR $cattype == 'storesmain' OR $cattype == 'wantads') {
			// #### defaults #######################################
			$dbtable = DB_PREFIX . 'categories';
			$cidfield = 'cid';
			$cidtitle = "title_$slng";
			$ctypefield = 'cattype';
			$counttype = 'auctioncount';
			$auctionid = 0;
			//suku added 4:58 PM 1/23/2011
			$series_sql = '';
			if (isset($ilance->GPC['series']) and $ilance->GPC['series'] > 0) {
				$result2 = $ilance->db->query("select PCGS from ilance_catalog_coin where coin_series_unique_no='" . $ilance->GPC['series'] . "' order by PCGS", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($result2)) {
					$i = 0;
					while ($line2 = $ilance->db->fetch_array($result2)) {
						$i++;
						$rs[$i] = $line2['PCGS'];
					}
					$series_sql = " AND cid in (" . implode(",", $rs) . ") ";
				} else {
					$series_sql = " And cid in (0) ";
				}
			}
			$extraquery = $series_sql;
			$show['noquery'] = false;
			switch ($cattype) {
				// #### SERVICE ################################
				case 'service':
					{
						$dbtable2 = DB_PREFIX . "projects";
						$detailpage = $ilpage['rfp'];
						$seotype = ($iscatmap) ? 'servicecatmap' : 'servicecat';
						$seotype2 = 'servicecatmap';
						$ctype = 'service';
						$blockcolor = 'blue';
						$block = '2';
						// #### root categories ################
						if ($cid == 0) {
							$query = "
							SELECT *
							FROM $dbtable
							WHERE $ctypefield = '$ctype'
								$extraquery
								AND visible = '1'
								AND level <= '1'
							ORDER BY lft ASC";
						}
						// #### child categories ###############
						else {
							$query = "
							SELECT node.*
							FROM $dbtable hp
							JOIN $dbtable node ON node.lft BETWEEN hp.lft AND hp.rgt
							JOIN $dbtable hr ON MBRWithin(Point(0, node.lft), hr.sets)
							WHERE hp.cid = '" . intval($cid) . "'
								AND hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND hr.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND node.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND hp.visible = '1'
								AND hr.visible = '1'
								AND node.visible = '1'
							GROUP BY node.cid
							HAVING  COUNT(*) <=
							(
								SELECT  COUNT(*)
								FROM    $dbtable hp
								JOIN    $dbtable hrp
								ON      MBRWithin(Point(0, hp.lft), hrp.sets)
								WHERE   hp.cid = '" . intval($cid) . "'
								AND     hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND     hrp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND     hp.visible = '1'
								AND     hrp.visible = '1'
							) + 2
							ORDER BY node.lft"	;
						}
						break;
					}
				// #### PRODUCT ################################
				case 'product':
					{
						$dbtable2 = DB_PREFIX . "projects";
						$detailpage = $ilpage['merch'];
						$seotype = ($iscatmap) ? 'productcatmap' : 'productcat';
						$seotype2 = 'productcatmap';
						$ctype = 'product';
						$blockcolor = 'yellow';
						$block = '';
						$series_id = isset($ilance->GPC['series']) ? $ilance->GPC['series'] : 0;
						// #### root categories ################
						if ($cid == 0 and $series_id == 0) {
							$query = "
							SELECT *
							FROM $dbtable
							WHERE $ctypefield = '$ctype'
								$extraquery
								AND visible = '1'
								AND level <= '1'
							ORDER BY lft ASC";
						}
						// #### child categories ###############
						else {
							//suku
							$pcgs_list = $this->fetch_children_pcgs($series_id);
							if (!empty($pcgs_list)) {
								$query = "select * from $dbtable where cid in (" . $pcgs_list . ")";
							} else {
								$show['noquery'] = true;
							}

							/*$query = "
					SELECT node.*
					FROM $dbtable hp
					JOIN $dbtable node ON node.lft BETWEEN hp.lft AND hp.rgt
					JOIN $dbtable hr ON MBRWithin(Point(0, node.lft), hr.sets)
					WHERE hp.cid = '" . intval($cid) . "'
					AND hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
					AND hr.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
					AND node.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
					AND hp.visible = '1'
					AND hr.visible = '1'
					AND node.visible = '1'
					GROUP BY node.cid
					HAVING  COUNT(*) <=
					(
					SELECT  COUNT(*)
					FROM    $dbtable hp
					JOIN    $dbtable hrp
					ON      MBRWithin(Point(0, hp.lft), hrp.sets)
					WHERE   hp.cid = '" . intval($cid) . "'
					AND     hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
					AND     hrp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
					AND     hp.visible = '1'
					AND     hrp.visible = '1'
					) + 2
					ORDER BY node.lft";*/
						}
						break;
					}
				// #### EXPERTS ################################
				case 'serviceprovider':
					{
						$detailpage = $ilpage['members'];
						$seotype = 'serviceprovidercat';
						$seotype2 = 'serviceprovidercat';
						$ctype = 'service';
						$blockcolor = 'gray';
						$block = '3';
						// #### root categories ################
						if ($cid == 0) {
							$query = "
							SELECT *
							FROM $dbtable
							WHERE $ctypefield = '$ctype'
								$extraquery
								AND visible = '1'
								AND level <= '1'
							ORDER BY lft ASC";
						} else {
							$query = "
							SELECT node.*
							FROM $dbtable hp
							JOIN $dbtable node ON node.lft BETWEEN hp.lft AND hp.rgt
							JOIN $dbtable hr ON MBRWithin(Point(0, node.lft), hr.sets)
							WHERE hp.cid = '" . intval($cid) . "'
								AND hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND hr.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND node.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND hp.visible = '1'
								AND hr.visible = '1'
								AND node.visible = '1'
							GROUP BY node.cid
							HAVING  COUNT(*) <=
							(
								SELECT  COUNT(*)
								FROM    $dbtable hp
								JOIN    $dbtable hrp
								ON      MBRWithin(Point(0, hp.lft), hrp.sets)
								WHERE   hp.cid = '" . intval($cid) . "'
								AND     hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND     hrp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND     hp.visible = '1'
								AND     hrp.visible = '1'
							) + 2
							ORDER BY node.lft"	;
						}
						break;
					}
				// #### PORTFOLIO ##############################
				case 'portfolio':
					{
						$dbtable2 = DB_PREFIX . "portfolio";
						$dbtable3 = DB_PREFIX . "attachment";
						$detailpage = $ilpage['portfolio'];
						$seotype = ($iscatmap) ? 'portfoliocatmap' : 'portfoliocat';
						$seotype2 = 'portfoliocat';
						$ctype = 'service';
						$blockcolor = 'gray';
						$block = '3';
						if ($cid == 0) {
							$query = "
							SELECT parent.cid, parent.title_$slng, parent.parentid, parent.canpost, parent.views, parent.level, COUNT(attach.portfolio_id) AS auctioncount
							FROM $dbtable node
							LEFT JOIN $dbtable2 port ON (node.cid = port.category_id AND port.visible = '1')
							LEFT JOIN $dbtable3 attach ON (port.portfolio_id = attach.portfolio_id AND attach.visible = '1' AND attach.attachtype = 'portfolio')
							JOIN $dbtable parent ON (node.lft BETWEEN parent.lft AND parent.rgt)
							WHERE
								parent.cattype = '$ctype'
								AND parent.portfolio = '1'
								AND parent.visible = '1'
								AND node.portfolio = '1'
								AND node.cattype = '$ctype'
								AND node.visible = '1'
							GROUP BY parent.cid
							ORDER BY node.lft ASC";
						} else {
							$query = "
							SELECT parent.cid, parent.title_$slng, parent.parentid, parent.canpost, parent.views, parent.level, COUNT(port.portfolio_id) AS auctioncount
							FROM $dbtable AS node
							LEFT JOIN $dbtable3 AS port ON (node.cid = port.category_id AND port.attachtype = 'portfolio')
							JOIN $dbtable AS parent ON (node.lft BETWEEN parent.lft AND parent.rgt)
							AND parent.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND node.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND
							(
								SELECT cid
								FROM $dbtable
								WHERE lft <= node.lft
									AND rgt >= node.rgt
									AND visible = '1'
									AND portfolio = '1'
									AND cattype = '" . $ilance->db->escape_string($ctype) . "'
								LIMIT 1
							)
							GROUP BY parent.cid
							HAVING COUNT(*) <=
							(
								SELECT COUNT(*)
								FROM $dbtable hp
								JOIN $dbtable hrp ON MBRWithin(Point(0, hp.lft), hrp.sets)
								WHERE hp.cid = '" . intval($cid) . "'
									AND hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
									AND hrp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
									AND hp.visible = '1'
									AND hrp.visible = '1'
									AND hp.portfolio = '1'
									AND hrp.portfolio = '1'
							) + 2
							ORDER BY parent.lft"	;
						}
						break;
					}
				// #### STORES #################################
				case 'stores':
					{
						$show['noquery'] = true;
						$detailpage = $ilpage['stores'];
						$dbtable = DB_PREFIX . 'stores_category';
						$seotype = ($iscatmap) ? 'storescatmap' : 'store';
						$seotype2 = 'store';
						$ctypefield = 'type';
						$ctype = $cattype;
						$cidtitle = 'category_name';
						$counttype = 'itemcount';
						$auctionid = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : '';
						$query = "SELECT * FROM $dbtable WHERE visible = '1' $extraquery ORDER BY sort ASC";
						$blockcolor = 'yellow';
						$block = '';
						break;
					}
				case 'storesmain':
					{
						$show['noquery'] = true;
						$detailpage = $ilpage['stores'];
						$dbtable = DB_PREFIX . 'stores_category';
						$seotype = ($iscatmap) ? 'storescatmap' : 'storescat';
						$seotype2 = 'storescat';
						$ctypefield = 'type';
						$ctype = $cattype;
						$cidtitle = 'category_name';
						$counttype = 'itemcount';
						$auctionid = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : '';
						$query = "SELECT * FROM $dbtable WHERE visible = '1' AND storeid = '-1' ORDER BY sort ASC";
						$blockcolor = 'yellow';
						$block = '';
						break;
					}
				// #### WANT ADS ###############################
				case 'wantads':
					{
						$detailpage = $ilpage['wantads'];
						$dbtable = DB_PREFIX . 'wantads_category';
						$seotype = ($iscatmap) ? 'wantadscatmap' : 'wantadscat';
						$seotype2 = 'wantadscat';
						$ctype = 'wantads';
						$cidfield = 'cid';
						$cidtitle = "title_$slng";
						$ctypefield = 'type';
						$counttype = 'auctioncount';
						$query = "SELECT * FROM $dbtable WHERE visible = '1' $extraquery ORDER BY sort ASC";
						$blockcolor = 'gray';
						$block = '3';
						break;
					}
			}
		}
		switch ($columns) {
			// #### SINGLE COLUMN OUTPUT ###########################
			case '1':
				{
					$write_html = false;
					if ($ilconfig['categorymapcache'] AND defined('LOCATION') AND LOCATION != 'search') {
						// #### cache default ##########################
						//$cache['filename'] = $ctype . '_' . $columns . 'cols' . $cacheid . '_leftnav_cid' . $cid . '_' . $slng . '.html';
						$cache['filename'] = $cattype . '_' . $columns . 'cols' . $cacheid . '_leftnav_cid' . $cid . '_' . $slng . '.html';
						$cache['filepath'] = DIR_TMP . $cache['filename'];
						if (file_exists($cache['filepath'])) {
							$lastmod = filemtime($cache['filepath']);
							if (($lastmod + $ilconfig['categorymapcachetimeout'] * 60) < mktime()) {
								// #### cache template is outdated
								$write_html = true;
							}
						} else {
							// #### the cache template file does not exist! we need to generate something!
							$write_html = true;
						}
						// #### fetch cache if available #######
						if ($write_html == false) {
							$show['leftnavcategories'] = true;
							$html = file_get_contents($cache['filepath']);
							if (empty($html)) {
								$write_html = true;
							}
						}
					} else {
						$write_html = true;
					}
					// #### build new left nav #####################
					if ($write_html) {
						$html = $htmlbackto = $htmlallcats = $parentcategory = '';
						$mycats = $html2 = $html3 = array();
						$count = $count2 = $thisparentid = 0;
						$templevel = $currentlevel = 1;
						$paddingtop = 5;
						$htmlstart = '<div style="padding:1px">';
						$htmlend = '</div>';
						if ($show['noquery'] == false) {
							$result = $ilance->db->query($query, 0, null, __FILE__, __LINE__);
							while ($row = $ilance->db->fetch_array($result, DB_ASSOC)) {
								if ($cattype == 'serviceprovider') {
									// count providers opt'ed in this category
									$counter = 0;
									$count = $ilance->db->query("
									$sqlquery[select]
									$sqlquery[categories]
									$sqlquery[options]
									$sqlquery[keywords]
									$sqlquery[location]
									$sqlquery[radius]
									$sqlquery[skillsquery]
									$sqlquery[profileanswersquery]
									$sqlquery[pricerange]
									$sqlquery[groupby]
									$sqlquery[orderby]
								", 0, null, __FILE__, __LINE__);
									if ($ilance->db->num_rows($count) > 0) {
										while ($resexclude = $ilance->db->fetch_array($count)) {
											$counter++;
										}
									}
									$mycats[] = array(
										'cid' => $row[$cidfield],
										'title' => $row[$cidtitle],
										'parentid' => $row['parentid'],
										'canpost' => $row['canpost'],
										'views' => $row['views'],
										'auctioncount' => $counter,
										'level' => $row['level'],
									);
								} else if ($cattype == 'portfolio') {
									$mycats[] = array(
										'cid' => $row[$cidfield],
										'title' => $row[$cidtitle],
										'parentid' => $row['parentid'],
										'canpost' => $row['canpost'],
										'views' => $row['views'],
										'auctioncount' => $row['auctioncount'],
										'level' => $row['level'],
									);
								} else if ($cattype == 'wantads') {
									$mycats[] = array(
										'cid' => $row[$cidfield],
										'title' => $row[$cidtitle],
										'parentid' => $row['parentid'],
										'canpost' => $row['canpost'],
										'views' => '0',
										'auctioncount' => $row[$counttype],
										'level' => $row['level'],
									);
								} else {
									$mycats[] = array(
										'cid' => $row[$cidfield],
										'title' => $row[$cidtitle],
										'parentid' => $row['parentid'],
										'canpost' => $row['canpost'],
										'views' => $row['views'],
										'auctioncount' => $row[$counttype],
										'level' => $row['level'],
									);
								}
							}
							unset($row, $result);
						} else {
							$mycats = $categorycache[$_SESSION['ilancedata']['user']['slng']]['stores'];
						}
						$catcount = count($mycats);
						$this->cats = $mycats;
						$show['leftnavcategories'] = true;
						for ($i = 0; $i < $catcount; $i++) {
							if (isset($ilance->GPC['cid']) AND $this->cats[$i]['cid'] == $ilance->GPC['cid']) {
								$currentlevel = $this->cats[$i]['level'];
								$thisparentid = $this->cats[$i]['parentid'];
								$parentcategory = ($thisparentid > 0)
								? $this->parent_title($slng, $cattype, $this->cats[$i]['parentid'])
								: '';
								$templevel++;
								break;
							}
						}
						// #### find the parent category name for "Back To: Category"
						if ($ilconfig['globalauctionsettings_showbackto']) {
							// #### back-to logic for portfolios
							if ($cattype == 'portfolio') {
								$currentlevel++;
								if ($currentlevel > 1 AND !empty($ilance->GPC['cid'])) {
									$auctioncount = '';
									$htmlbackto = ($ilconfig['globalauctionsettings_seourls'])
									? '<span><span class="smaller gray">' . $phrase['_back_to'] . ':</span>&nbsp;<span class="blueonly"><a href="' . HTTP_SERVER . print_seo_url($ilconfig['portfolioslistingidentifier']) . '">' . $phrase['_gallery'] . '</a></span></span>' . $auctioncount . '<hr size="1" style="color:#cccccc" />'
									: '<span><span class="smaller gray">' . $phrase['_back_to'] . ':</span>&nbsp;<span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['portfolio'] . '">' . $phrase['_gallery'] . '</a></span></span>' . $auctioncount . '<hr size="1" style="color:#cccccc" />';
								}
							}
							// #### back-to logic for everything else
							else {
								if ($currentlevel > 1 AND !empty($ilance->GPC['cid']) AND !empty($parentcategory) AND $thisparentid > 0) {
									$auctioncount = '';
									$htmlbackto = ($ilconfig['globalauctionsettings_seourls'])
									? '<span><span class="smaller gray">' . $phrase['_back_to'] . ':</span>&nbsp;<span class="blueonly">' . construct_seo_url($seotype, $thisparentid, $auctionid, $parentcategory, $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0, $removevars = 'qid') . '</span></span>' . $auctioncount . '<hr size="1" style="color:#cccccc" />'
									: '<span><span class="smaller gray">' . $phrase['_back_to'] . ':</span>&nbsp;<span class="blueonly"><a href="' . $detailpage . '?cid=' . $thisparentid . '">' . $parentcategory . '</a></span></span>' . $auctioncount . '<hr size="1" style="color:#cccccc" />';
								}
							}
							($apihook = $ilance->api('print_subcategory_columns_back_to_end')) ? eval($apihook) : false;
						}
						for ($i = 0; $i < $catcount; $i++) {
							$html3[$count2]['html'] = '';
							if ($this->cats[$i]['parentid'] == $thisparentid) {
								if (!empty($sqlqueryads) AND is_array($sqlqueryads)) {
									// best match - uses existing search params to dig search patterns category by category
									// this variable will make a hit to the db to find results independant of the existing search being performed
									$catbitcounter = $this->bestmatch_auction_count($this->cats[$i]['cid'], $cattype, $sqlqueryads);
									$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
									? '&nbsp;<span class="smaller gray">(' . $catbitcounter . ')</span>'
									: '';
								} else if (!empty($sqlquery) AND is_array($sqlquery)) {
									// best match - uses existing search params to dig search patterns category by category
									// this variable will make a hit to the db to find results independant of the existing search being performed
									$catbitcounter = $this->bestmatch_auction_count($this->cats[$i]['cid'], $cattype, $sqlquery);
									$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
									? '&nbsp;<span class="smaller gray">(' . $catbitcounter . ')</span>'
									: '';
								} else {
									// regular auction count
									// this variable will not make another hit to the db
									if ($cattype == 'stores' AND $this->cats[$i]['parentid'] == '0') {
										$catbitcounter = $ilance->stores->print_total_category_parent_count($ilance->GPC['id']);
										$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
										? '&nbsp;<span class="smaller gray">(' . $catbitcounter . ')</span>'
										: '';
									} else {
										$catbitcounter = $this->cats[$i]['auctioncount'];
										$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
										? '&nbsp;<span class="smaller gray">(' . $catbitcounter . ')</span>'
										: '';
									}
								}
								// #### MAIN SELECTED CATEGORY ############################################
								if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] == $this->cats[$i]['cid']) {
									// #### CATEGORY FINDER ###########################################
									if ($cattype == 'service' OR $cattype == 'product') {
										// will populate $show['categoryfinder'] = true or false
										$categoryfinderhtml = $this->print_searchable_questions($this->cats[$i]['cid'], $showcount, $this->cats[$i]['level'], 0);
									}
									if ($ilconfig['globalauctionsettings_showcurrentcat']) {
										$html .= '<div style="padding-top:0px; padding-left:0px"><strong>' . stripslashes($this->cats[$i]['title']) . '' . $catbitcount . '</strong> ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>';
									}
									// #### SUBCATEORIES IN MAIN SELECTED CATEGORY #####################
									$html2 = array();
									if ($currentlevel >= 1) {
										$count = 0;
										foreach ($mycats AS $array) {
											$html2[$count]['html'] = '';
											if ($array['parentid'] == $this->cats[$i]['cid']) {
												if (!empty($sqlqueryads) AND is_array($sqlqueryads)) {
													// best match - uses existing search params to dig search patterns category by category
													// this variable will make a hit to the db to find results independant of the existing search being performed
													$catbitcounter2 = $this->bestmatch_auction_count($array['cid'], $cattype, $sqlqueryads);
													$catbitcount2 = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
													? '&nbsp;<span class="smaller gray">(' . $catbitcounter2 . ')</span>'
													: '';
												} else if (!empty($sqlquery) AND is_array($sqlquery)) {
													// best match - uses existing search params to dig search patterns category by category
													// this variable will make a hit to the db to find results independant of the existing search being performed
													$catbitcounter2 = $this->bestmatch_auction_count($array['cid'], $cattype, $sqlquery);
													$catbitcount2 = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
													? '&nbsp;<span class="smaller gray">(' . $catbitcounter2 . ')</span>'
													: '';
												} else {
													// regular auction count
													// this variable not make another hit to the db
													$catbitcounter2 = $array['auctioncount'];
													$catbitcount2 = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
													? '&nbsp;<span class="smaller gray">(' . $catbitcounter2 . ')</span>'
													: '';
												}
												// if we are hiding the main selected category then set the left-padding level to 1
												if ($ilconfig['globalauctionsettings_showcurrentcat'] == false) {
													$templevel = 1;
												}
												// if listing counter in this category is empty don't show subcategory!
												if ($catbitcounter2 > 0) {
													if ($ilconfig['globalauctionsettings_seourls']) {
														$html2[$count]['html'] .= '<div style="padding-top:3px; padding-left:' . $this->fetch_level_padding($templevel) . 'px"><span class="blueonly">' . construct_seo_url($seotype, $array['cid'], $auctionid, stripslashes($array['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . '</span>' . $catbitcount2 . ' ' . $this->print_category_newicon($array['cid'], $ctype) . '</div>';
													} else {
														$html2[$count]['html'] .= '<div style="padding-top:3px; padding-left:' . $this->fetch_level_padding($templevel) . 'px"><span class="blueonly"><a href="' . $detailpage . '?cid=' . $array['cid'] . print_hidden_fields($string = true, $excluded = array('page', 'mode', 'cid', 'cmd', 'state', 'id'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true) . '">' . stripslashes($array['title']) . '</a></span>' . $catbitcount2 . ' ' . $this->print_category_newicon($array['cid'], $ctype) . '</div>';
													}
													$count++;
												}
											}
										}
									}
									$bit['visible'] = $bit['hidden'] = '';
									$templevel = ($ilconfig['globalauctionsettings_showcurrentcat'] == false) ? 1 : 2;
									$hidden = '<div style="padding-left:' . $this->fetch_level_padding($templevel) . 'px; padding-bottom:6px; padding-top:5px" class="blueonly"><a href="javascript:void(0)" onclick="toggle_more(\'showmoresubcats_' . $cid . '\', \'moretext_' . $cid . '\', \'' . $phrase['_more'] . '\', \'' . $phrase['_less'] . '\', \'showmoreicon_' . $cid . '\')"><span id="moretext_' . $cid . '" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmoresubcats_$cid"]) ? $phrase['_less'] : $phrase['_more']) . '</span></a> <img id="showmoreicon_' . $cid . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmoresubcats_$cid"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
									if (!empty($html2) AND is_array($html2)) {
										$c = 0;
										foreach ($html2 AS $key => $array) {
											$c++;
											if ($c <= $ilconfig['globalauctionsettings_catcutoff']) {
												$bit['visible'] .= $html2[$key]['html'];
											} else {
												$bit['hidden'] .= $html2[$key]['html'];
											}
										}
									}
									if ($count <= $ilconfig['globalauctionsettings_catcutoff']) {
										$hidden = '';
									}
									// rebuild display options
									if (!empty($bit['visible'])) {
										$html .= "$bit[visible] <div id=\"showmoresubcats_$cid\" style=\"" . (!empty($ilcollapse["showmoresubcats_$cid"]) ? $ilcollapse["showmoresubcats_$cid"] : 'display: none;') . "\">$bit[hidden]</div>$hidden";
									}
								}
								// #### MAIN UNSELECTED CATEGORIES ###################################
								// this block will execute only if:
								// 1. main menu is being viewed
								// 2. if a user enters keywords but does not select a category
								else {
									// prevent all other root cats from showing underneat selected category
									if (empty($ilance->GPC['cid']) OR $ilance->GPC['cid'] == 0) {
										if ($this->cats[$i]['parentid'] == $thisparentid) {
											/*if (defined('LOCATION') AND LOCATION != 'search')
										{
										$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
										? '<div style="padding-top:3px; padding-left:0px"><span class="blueonly">' . construct_seo_url($seotype2, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>'
										: '<div style="padding-top:3px; padding-left:0px"><span class="blueonly"><a href="' . $detailpage . '?cmd=listings&amp;cid=' . $this->cats[$i]['cid'] . print_hidden_fields($string = true, $excluded = array('page','mode','cid','cmd','state','id','sort'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>');
										$count2++;
										}
										else
										{
										if (!empty($ilance->GPC['q']))
										{
										if ($catbitcounter > 0)
										{
										$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
										? '<div style="padding-top:3px; padding-left:0px"><span class="blueonly">' . construct_seo_url($seotype, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>'
										: '<div style="padding-top:3px; padding-left:0px"><span class="blueonly"><a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . print_hidden_fields($string = true, $excluded = array('page','mode','cid','cmd','state','id'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = false, $urldecode = true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>');
										$count2++;
										}
										}
										else
										{
										if ($catbitcounter > 0)
										{
										$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
										? '<div style="padding-top:3px; padding-left:0px"><span class="blueonly">' . construct_seo_url($seotype, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>'
										: '<div style="padding-top:3px; padding-left:0px"><span class="blueonly"><a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . print_hidden_fields($string = true, $excluded = array('page','mode','cid','cmd','state','id'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>');
										$count2++;
										}
										}
										}*/
											if (!empty($ilance->GPC['q'])) {
												if ($catbitcounter > 0) {
													$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
														? '<div style="padding-top:3px; padding-left:0px"><span class="blueonly">' . construct_seo_url($seotype, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>'
														: '<div style="padding-top:3px; padding-left:0px"><span class="blueonly"><a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . print_hidden_fields($string = true, $excluded = array('page', 'mode', 'cid', 'cmd', 'state', 'id'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = false, $urldecode = true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>');
													$count2++;
												}
											} else {
												if ($catbitcounter > 0) {
													$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
														? '<div style="padding-top:3px; padding-left:0px"><span class="blueonly">' . construct_seo_url($seotype, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>'
														: '<div style="padding-top:3px; padding-left:0px"><span class="blueonly"><a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . print_hidden_fields($string = true, $excluded = array('page', 'mode', 'cid', 'cmd', 'state', 'id'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>');
													$count2++;
												} else {
													if (defined('LOCATION') AND LOCATION == 'main') {
														$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
															? '<div style="padding-top:3px; padding-left:0px"><span class="blueonly">' . construct_seo_url($seotype2, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>'
															: '<div style="padding-top:3px; padding-left:0px"><span class="blueonly"><a href="' . $detailpage . '?cmd=listings&amp;cid=' . $this->cats[$i]['cid'] . print_hidden_fields($string = true, $excluded = array('page', 'mode', 'cid', 'cmd', 'state', 'id', 'sort'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['cid'], $ctype) . '</div>');
														$count2++;
													}
												}
											}
										}
									}
								}
							}
						}
						// #### determine if we're viewing categories on main menu to show all cats without user clicking "More"
						if (defined('LOCATION') AND LOCATION == 'main') {
							$ilconfig['globalauctionsettings_catcutoff'] = 1000;
						}
						$bit['visible'] = $bit['hidden'] = '';
						$templevel = ($ilconfig['globalauctionsettings_showcurrentcat'] == false OR $count2 > 0) ? 1 : 2;
						$hidden = '<div style="padding-left:' . $this->fetch_level_padding($templevel) . 'px; padding-bottom:6px; padding-top:5px"><span class="blueonly"><a href="javascript:void(0)" onclick="toggle_more(\'showmorecats_' . $cattype . '\', \'moretext_' . $cattype . '\', \'' . $phrase['_more'] . '\', \'' . $phrase['_less'] . '\', \'showmoreicon_' . $cattype . '\')"><span id="moretext_' . $cattype . '" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmorecats_$cattype"]) ? $phrase['_less'] : $phrase['_more']) . '</span></a></span> <img id="showmoreicon_' . $cattype . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmorecats_$cattype"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
						if (!empty($html3) AND is_array($html3)) {
							$c = 0;
							foreach ($html3 AS $key => $array) {
								$c++;
								if ($c <= $ilconfig['globalauctionsettings_catcutoff']) {
									$bit['visible'] .= $html3[$key]['html'];
								} else {
									$bit['hidden'] .= $html3[$key]['html'];
								}
							}
						}
						if ($count2 <= $ilconfig['globalauctionsettings_catcutoff']) {
							$hidden = '';
						}
						if ($count2 > 0) {
							// #### rebuild display options ########
							if (!empty($bit['visible'])) {
								$html .= "$bit[visible] <div id=\"showmorecats_$cattype\" style=\"" . (!empty($ilcollapse["showmorecats_$cattype"])
									? $ilcollapse["showmorecats_$cattype"]
									: 'display: none;') . "\">$bit[hidden]</div>$hidden";
							}
						}
						// #### category map urls ######################
						if (defined('LOCATION') AND LOCATION == 'main') {
							if ($cattype == 'service') {
								$htmlallcats = ($ilconfig['globalauctionsettings_seourls'])
								? '<div style="padding-top:4px" class="bluecat"><a href="' . HTTP_SERVER . print_seo_url($ilconfig['servicecatmapidentifier']) . '">' . $phrase['_view_all_categories'] . '</a></div>'
								: '<div style="padding-top:4px" class="bluecat"><a href="' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=listings">' . $phrase['_view_all_categories'] . '</a></div>';
							} else if ($cattype == 'product') {
								$htmlallcats = ($ilconfig['globalauctionsettings_seourls'])
								? '<div style="padding-top:4px" class="bluecat"><a href="' . HTTP_SERVER . print_seo_url($ilconfig['productcatmapidentifier']) . '">' . $phrase['_view_all_categories'] . '</a></div>'
								: '<div style="padding-top:4px" class="bluecat"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=listings">' . $phrase['_view_all_categories'] . '</a></div>';
							}
						}
						$show['leftnavcategories'] = (empty($html)) ? false : true;
						// #### build our left nav template ####
						$html = "$htmlstart $htmlbackto $html $htmlallcats $htmlend";
						// #### don't want to cache interactive search left nav category logic as it changes constantly for user selections
						if ($ilconfig['categorymapcache'] AND defined('LOCATION') AND LOCATION != 'search') {
							// #### cache default ##########################
							//$cache['filename'] = $ctype . '_' . $columns . 'cols' . $cacheid . '_leftnav_cid' . $cid . '_' . $slng . '.html';
							$cache['filename'] = $cattype . '_' . $columns . 'cols' . $cacheid . '_leftnav_cid' . $cid . '_' . $slng . '.html';
							$cache['filepath'] = DIR_TMP . $cache['filename'];
							$cache['unique_name'] = rand(0, 100000);
							while (file_exists(DIR_TMP . $cache['unique_name']));
							{
								$f = fopen(DIR_TMP . $cache['unique_name'], 'w');
								if ($f === false) {
									@unlink(DIR_TMP . $cache['unique_name']);
								} else {
									fwrite($f, $html);
									fclose($f);
									@unlink(DIR_TMP . $cache['filename']);
									@rename(DIR_TMP . $cache['unique_name'], DIR_TMP . $cache['filename']);
									@unlink(DIR_TMP . $cache['unique_name']);
								}
							}
						}
					}
					break;
				}
			// #### MULTIPLE COLUMN OUTPUT #########################
			default:
				{
					// #### category map caching enabled ###########
					if ($ilconfig['categorymapcache']) {
						// #### cache default ##########################
						//$cache['filename'] = $ctype . '_' . $columns . 'cols' . $cacheid . '_catmap_cid' . $cid . '_' . $slng . '.html';
						$cache['filename'] = $cattype . '_' . $columns . 'cols' . $cacheid . '_catmap_cid' . $cid . '_' . $slng . '.html';
						$cache['filepath'] = DIR_TMP . $cache['filename'];
						$cache['unique_name'] = '';
						// #### check if we need to rewrite cache template
						$write_html = false;
						if (file_exists($cache['filepath'])) {
							$lastmod = filemtime($cache['filepath']);
							if (($lastmod + $ilconfig['categorymapcachetimeout'] * 60) < mktime()) {
								// #### cache template is outdated
								$write_html = true;
							}
						} else {
							// #### the cache template file does not exist! we need to generate something!
							$write_html = true;
						}
						if ($write_html) {
							$cache['unique_name'] = rand(0, 100000);
							while (file_exists(DIR_TMP . $cache['unique_name']));
							{
								$f = fopen(DIR_TMP . $cache['unique_name'], 'w');
								if ($f === false) {
									@unlink(DIR_TMP . $cache['unique_name']);
								} else {
									global $recursive_html;
									$recursive_html = '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
									$this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $columns);
									$recursive_html .= '</table><!--<div style="padding-top:12px; padding-left:6px" class="smaller gray">Cached: ' . print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . '</div>-->';
									$html = $recursive_html;
									fwrite($f, $html);
									fclose($f);
									@unlink(DIR_TMP . $cache['filename']);
									@rename(DIR_TMP . $cache['unique_name'], DIR_TMP . $cache['filename']);
									@unlink(DIR_TMP . $cache['unique_name']);
								}
							}
						} else {
							// #### template cache exists - read it
							$html = file_get_contents($cache['filepath']);
						}
					}
					// #### category map caching disabled ##########
					else {
						global $recursive_html;
						$recursive_html = '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
						$this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $columns);
						$recursive_html .= '</table>';
						$html = $recursive_html;
					}
				}
		}
		$ilance->timer->stop();
		DEBUG("print_subcategory_columns(\$columns = $columns, \$cattype = $cattype, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}
	//suku
	function print_subcategory_columns_coin_series($denomination_id) {
		global $ilance, $ilconfig;
		$coin_series_detail = $this->fetch_coin_series($denomination_id);
		$html = '';
		$html .= '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		$check = 0;
		foreach ($coin_series_detail as $coin_series) {
			//$count=$this->get_project_count(0,$coin_series['id']);
			if ($check % 4 == 0) {
				$html .= '</tr><tr>';
			}
//karthik on jun16 for search
			if ($ilconfig['globalauctionsettings_seourls']) {
				if ((isset($ilance->GPC['ended']) and $ilance->GPC['ended']) OR (isset($ilance->GPC['cmd']) and $ilance->GPC['cmd'] == 'price_go')) {
					/*$result = $ilance->db->query("SELECT COUNT( * ) AS sold
														FROM  " . DB_PREFIX . "projects p
														WHERE p.coin_series_unique_no =  '" . $coin_series['coin_series_unique_no'] . "'
														AND p.haswinner =  '1'");*/
					$result=$ilance->db->query("SELECT COUNT( p.project_id ) AS sold
									FROM  " . DB_PREFIX . "projects p
									LEFT JOIN " . DB_PREFIX . "users u ON p.user_id = u.user_id
									WHERE p.visible = '1' AND (p.status != 'open') AND p.coin_series_unique_no =  '".$coin_series['coin_series_unique_no']."'
									AND ((p.haswinner = '1' AND p.winner_user_id > 0) OR p.hasbuynowwinner = '1') AND (p.project_state = 'product') AND u.status='active' "
								);
					$series_count = $ilance->db->fetch_array($result);
					$html .= '<td><a  href="' . HTTP_SERVER . 'CoinPrices/SeriesCoin/' . $coin_series['coin_series_unique_no'] . '/' . construct_seo_url_name($coin_series['coin_series_name']) . '" >' . $coin_series['coin_series_name'] . '&nbsp;(' . $series_count['sold'] . ')' . '</a></td>';
				} else {
					$html .= '<td><a  href="' . HTTP_SERVER . 'Series/' . $coin_series['coin_series_unique_no'] . '/' . construct_seo_url_name($coin_series['coin_series_name']) . '" >' . $coin_series['coin_series_name'] . '&nbsp;(' . $coin_series['auction_count'] . ')' . '</a></td>';
				}
			} else {
				$html .= '<td><a  href="' . HTTP_SERVER . 'search.php?mode=product&series=' . $coin_series['coin_series_unique_no'] . '" >' . $coin_series['coin_series_name'] . '&nbsp;(' . $coin_series['auction_count'] . ')' . '</a></td>';
			}
			$check++;
		}
		$html .= '<tr><td></td></table>';
		return $html;
	}

	function print_coin_series_html($denomination_id) {
		global $ilance, $ilconfig;
		$coin_series_detail = $this->fetch_coin_series($denomination_id);
		$html = '';
		$html .= '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		$check = 0;
		foreach ($coin_series_detail as $coin_series) {
			//$count=$this->get_project_count(0,$coin_series['id']);
			if ($check % 4 == 0) {
				$html .= '</tr><tr>';
			}
//karthik on jun16 for search
			if ($ilconfig['globalauctionsettings_seourls']) {
				if ((isset($ilance->GPC['ended']) and $ilance->GPC['ended']) OR (isset($ilance->GPC['cmd']) and $ilance->GPC['cmd'] == 'price_go')) {
					$result = $ilance->db->query("SELECT COUNT( * ) AS sold
														FROM  " . DB_PREFIX . "projects p
														WHERE p.coin_series_unique_no =  '" . $coin_series['coin_series_unique_no'] . "'
														AND p.haswinner =  '1'");
					$series_count = $ilance->db->fetch_array($result);
					$html .= '<td><h3 style="margin-top: 0px; margin-bottom: 0px;"><a  href="' . HTTP_SERVER . 'CoinPrices/SeriesCoin/' . $coin_series['coin_series_unique_no'] . '/' . construct_seo_url_name($coin_series['coin_series_name']) . '" >' . $coin_series['coin_series_name'] . '&nbsp;(' . $series_count['sold'] . ')' . '</a></h3></td>';
				} else {
					$html .= '<td><h3 style="margin-top: 0px; margin-bottom: 0px;"><a  href="' . HTTP_SERVER . 'Series/' . $coin_series['coin_series_unique_no'] . '/' . construct_seo_url_name($coin_series['coin_series_name']) . '" >' . $coin_series['coin_series_name'] . '&nbsp;(' . $coin_series['auction_count'] . ')' . '</a></h3></td>';
				}
			} else {
				$html .= '<td><h3 style="margin-top: 0px; margin-bottom: 0px;"><a  href="' . HTTP_SERVER . 'search.php?mode=product&series=' . $coin_series['coin_series_unique_no'] . '" >' . $coin_series['coin_series_name'] . '&nbsp;(' . $coin_series['auction_count'] . ')' . '</a></h3></td>';
			}
			$check++;
		}
		$html .= '<tr><td></td></table>';
		return $html;
	}

	function print_subcategory_columns_denominations($columns = 1, $cattype = 'product', $dosubcats = 1, $slng = 'eng', $cid = 0, $extra = '', $showcount = 1, $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $cacheid = '') {
		global $ilance, $myapi, $phrase, $ilconfig, $ilpage, $show, $storeid, $storetype, $sqlquery, $categoryfinderhtml, $sqlquery, $sqlqueryads, $recursive_html, $categorycache, $block, $blockcolor;
		$ilance->timer->start();
		if (!empty($cacheid)) {
			$cacheid = '_' . $cacheid;
		}
		$html = '';
		$html .= '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		/* $this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $columns);
		 */
		$denomination_details = $this->fetch_denominations();
		$check = 0;
		foreach ($denomination_details as $denom_detail) {
			//$count=$this->get_project_count($denom_detail['id']);
			if ($check % $columns == 0) {
				$html .= '</tr><tr>';
			}
			if ($ilconfig['globalauctionsettings_seourls']) {
				$html .= '<td><a  href="' . HTTP_SERVER . 'Denomination/' . $denom_detail['id'] . '/' . construct_seo_url_name($denom_detail['denomination_long']) . '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['auction_count'] . ')' . '</a></td>';
			} else {
				$html .= '<td><a  href="' . HTTP_SERVER . 'denomination.php?denomination=' . $denom_detail['id'] . '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['auction_count'] . ')' . '</a></td>';
			}

			$check++;
		}
		$html .= '<tr><td></td></table>';
		$ilance->timer->stop();
		DEBUG("print_subcategory_columns(\$columns = $columns, \$cattype = $cattype, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}
//karthik on may30
	function print_subcategory_columns_denominations_price($columns = 1, $cattype = 'product', $dosubcats = 1, $slng = 'eng', $cid = 0, $extra = '', $showcount = 1, $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $cacheid = '') {
		global $ilance, $myapi, $phrase, $ilconfig, $ilpage, $show, $storeid, $storetype, $sqlquery, $categoryfinderhtml, $sqlquery, $sqlqueryads, $recursive_html, $categorycache, $block, $blockcolor;
		$ilance->timer->start();
		if (!empty($cacheid)) {
			$cacheid = '_' . $cacheid;
		}
		$html = '';
		$html .= '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		/* $this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $columns);
		 */
		$denomination_details = $this->fetch_denominations();
		$check = 0;
		foreach ($denomination_details as $denom_detail) {
			//$count=$this->get_project_count($denom_detail['id']);
			if ($check % $columns == 0) {
				$html .= '</tr><tr>';
			}
			//karthik on jun16 for sold listing
			if ($ilance->GPC['cmd'] == 'CoinPrices') {
				/*$result=$ilance->db->query("select count(haswinner) as sold_count from " . DB_PREFIX . "projects p," . DB_PREFIX . "catalog_coin c
				where c.PCGS=p.cid
				AND p.haswinner=1
				AND c.coin_series_denomination_no ='".$denom_detail['id'] ."'
				AND p.status='expired'");*/
				$result = $ilance->db->query("select count(haswinner) as sold_count from " . DB_PREFIX . "projects p
																			where p.haswinner=1
																			AND p.coin_series_denomination_no ='" . $denom_detail['id'] . "'
																			AND p.status='expired'");

				$denom = $ilance->db->fetch_array($result);
				$denom_detail['auction_count'] = $denom['sold_count'];
			}
			//end on jun16
			//june 16 sekar changes merch into denomiation
			if ($ilconfig['globalauctionsettings_seourls']) {
				$html .= '<td><a  href="' . HTTP_SERVER . 'CoinPrices/' . $denom_detail['id'] . '/' . construct_seo_url_name($denom_detail['denomination_long']) . '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['auction_count'] . ')' . '</a></td>';
			} else {
				$html .= '<td><a  href="' . HTTP_SERVER . 'denomination.php?denomination=' . $denom_detail['id'] . '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['auction_count'] . ')' . '</a></td>';
			}

			$check++;
		}
		$html .= '<tr><td></td></table>';
		$ilance->timer->stop();
		DEBUG("print_subcategory_columns(\$columns = $columns, \$cattype = $cattype, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}

	function print_subcategory_columns_denominations_price_4567($columns = 1, $cattype = 'product', $dosubcats = 1, $slng = 'eng', $cid = 0, $extra = '', $showcount = 1, $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $cacheid = '') {
		global $ilance, $myapi, $phrase, $ilconfig, $ilpage, $show, $storeid, $storetype, $sqlquery, $categoryfinderhtml, $sqlquery, $sqlqueryads, $recursive_html, $categorycache, $block, $blockcolor;
		$ilance->timer->start();
		if (!empty($cacheid)) {
			$cacheid = '_' . $cacheid;
		}
		$html = '';
		$html .= '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		/* $this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $columns);
		 */
		$denomination_details = $this->fetch_denominations();
		$check = 0;
		foreach ($denomination_details as $denom_detail) {
			//$count=$this->get_project_count($denom_detail['id']);
			if ($check % $columns == 0) {
				$html .= '</tr><tr>';
			}
			//karthik on jun16 for sold listing
			if ($ilance->GPC['cmd'] == 'CoinPrices') {
				/*$result=$ilance->db->query("select count(haswinner) as sold_count from " . DB_PREFIX . "projects p," . DB_PREFIX . "catalog_coin c
				where c.PCGS=p.cid
				AND p.haswinner=1
				AND c.coin_series_denomination_no ='".$denom_detail['id'] ."'
				AND p.status='expired'");*/
				$result = $ilance->db->query("select count(haswinner) as sold_count from " . DB_PREFIX . "projects p
																			where p.haswinner=1
																			AND p.coin_series_denomination_no ='" . $denom_detail['id'] . "'
																			AND p.status='expired'");

				$denom = $ilance->db->fetch_array($result);
				$denom_detail['auction_count'] = $denom['sold_count'];
			}
			//end on jun16
			//june 16 sekar changes merch into denomiation
			if ($ilconfig['globalauctionsettings_seourls']) {
				$html .= '<td><a  href="' . HTTP_SERVER . 'TestPrices/' . $denom_detail['id'] . '/' . construct_seo_url_name($denom_detail['denomination_long']) . '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['auction_count'] . ')' . '</a></td>';
			} else {
				$html .= '<td><a  href="' . HTTP_SERVER . 'denomination_4567.php?denomination=' . $denom_detail['id'] . '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['auction_count'] . ')' . '</a></td>';
			}

			$check++;
		}
		$html .= '<tr><td></td></table>';
		$ilance->timer->stop();
		DEBUG("print_subcategory_columns(\$columns = $columns, \$cattype = $cattype, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}

	function print_subcategory_columns_coin_class($series_id) {
		global $ilance, $ilconfig, $ilpage;
		$coin_class_detail = $this->fetch_coin_class($series_id);
		$html = '';
		$html .= '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		$check = 0;
		foreach ($coin_class_detail as $coin_class) {
			if ($check % 4 == 0) {
				$html .= '</tr><tr>';
			}
			//$count=$this->get_project_count(0,0,$coin_class['PCGS']);
			$html .= '<td><a  href="' . $ilpage['search'] . '?mode=product&cid=' . $coin_class['PCGS'] . '" >' .
			$coin_class['coin_detail_year'] . " " .
			$coin_class['coin_detail_coin_series'] . " " .
			$coin_class['coin_detail_suffix'] . " " .
			$coin_class['coin_detail_major_variety'] . " " .
			$coin_class['coin_detail_suffix'] . ' (' . $$coin_class['auction_count'] . ')</a></td>';
			$check++;
		}
		$html .= '<tr><td></td></table>';
		return $html;
	}
	function fetch_denominations($denomination_id = 0, $columns = ' * ') {
		global $ilance;
		$subquery = '';
		if (is_array($columns)) {
			$columns = implode(', ', $columns);
		}

		if ($denomination_id != 0) {
			$subquery = " where id='" . $ilance->db->escape_string((int) $denomination_id) . "'";
		}

		$result = $ilance->db->query("select " . $columns . " from " . DB_PREFIX . "catalog_toplevel " . $subquery . " order by denomination_sort", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result)) {
			$count = 0;
			while ($row = $ilance->db->fetch_array($result)) {
				$denom[$count] = $row;
				$count++;
				if ($denomination_id != 0) {
					return $row;
				}

			}
		} else {
//		exit();
		}
		return $denom;
	}
	function fetch_coin_series($denomination_id = 0, $series_id = 0, $columns = ' * ') {
		global $ilance;
		if (is_array($columns)) {
			$columns = implode(', ', $columns);
		}

		if ($denomination_id != 0) {
			$sub_query = '';
			if ($series_id != 0) {
				$sub_query = " and id=" . $series_id;
			}
			$result = $ilance->db->query("select " . $columns . " from " . DB_PREFIX . "catalog_second_level
										where coin_series_denomination_no='" . $denomination_id . "' " . $sub_query . " order by coin_series_sort", 0, null, __FILE__, __LINE__);
		} else {
			$sub_query = '';
			if ($series_id != 0) {
				//new change id change to coin_series_unique_no
				//suku
				$sub_query = " where coin_series_unique_no=" . $ilance->db->escape_string((int) $series_id);
			}
			$result = $ilance->db->query("select " . $columns . " from " . DB_PREFIX . "catalog_second_level
										  " . $sub_query . " order by coin_series_sort", 0, null, __FILE__, __LINE__);
		}
		if ($ilance->db->num_rows($result)) {
			$count = 0;
			while ($row = $ilance->db->fetch_array($result)) {
				if ($series_id != 0) {
					return $row;
				}

				$rs[$count] = $row;
				$count++;
			}
		}
		return $rs;
	}
	function fetch_coin_class($series_id = 0, $coin_class_id = 0, $pcgs = 0, $columns = ' * ') {
		global $ilance;
		if (is_array($columns)) {
			$columns = implode(', ', $columns);
		}

		$sub_query = '';
		if ($pcgs > 0) {
			$sub_query = " or PCGS='" . $pcgs . "'";
		}

		if ($coin_class_id != 0) {
			$sub_query = ' and id=' . $coin_class_id;
		}

		$result = $ilance->db->query("select " . $columns . " from " . DB_PREFIX . "catalog_coin
										where coin_series_unique_no='" . $series_id . "' " . $sub_query . " order by coin_detail_sort", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result)) {
			$count = 0;
			while ($row = $ilance->db->fetch_array($result)) {
				if ($coin_class_id != 0) {
					return $row;
				}

				$rs[$count] = $row;
				$count++;
			}
		}
		return $rs;
	}

	function fetch_coin_class_pcgs($series_id = 0) {
		global $ilance;
		$rs=array();

		$result = $ilance->db->query("select PCGS from " . DB_PREFIX . "catalog_coin
										where coin_series_unique_no IN (". $series_id .") order by coin_detail_sort", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result)>0) 
		{
			$count = 0;
			while ($row = $ilance->db->fetch_array($result)) 
			{
				$rs[$count] = $row['PCGS'];
				$count++;
			}
		}
		return $rs;
	}

	function leftnav_denomination() {
		global $ilance, $ilconfig;
		if ($ilconfig['categorymapcache'] == false) {
			$denomination = $this->fetch_denominations();
			foreach ($denomination as $demon) {
				//$count=$this->get_project_count($demon['id']);
				/* if($ilconfig['globalauctionsettings_seourls'])*/
				$count = $this->get_project_count($demon['id']);
				$html .= '<li  nowrap="nowrap" style="list-style-type:none; height: 20px;"><h3 style="font-size: 10pt;font-family:arial,helvetica,verdana,sans-serif; height:30px; margin: 0px;"><a  href="' . HTTP_SERVER . 'Denomination/' . $demon['id'] . '/' . construct_seo_url_name($demon['denomination_long']) . '" >' . $demon['denomination_long'] . '</a><span style=" color:#666666; font-size:12px;"> (' . $demon['auction_count'] . ') </span></h3></li>';
/*		 else
$html.='<li  nowrap="nowrap" style="list-style-type:none; height: 20px;"><a  href="merch.php?denomination='.$demon['id'].'" >'.$demon['denomination_long'].'</a>&nbsp;<span style=" color:#666666; font-size:12px;"> ('.$demon['auction_count'].') </span></li>';*/
			}
		} else {
			$cache['filename'] = 'main_left_nav.html';
			$cache['filepath'] = DIR_TMP . $cache['filename'];
			$write_html = false;
			if (file_exists($cache['filepath'])) {
				$lastmod = filemtime($cache['filepath']);
				if (($lastmod + $ilconfig['categorymapcachetimeout'] * 60) < mktime()) {
					// #### cache template is outdated
					$write_html = true;
				}
			} else {
				// #### the cache template file does not exist! we need to generate something!
				$write_html = true;
			}
			if ($write_html == false) {
				$show['leftnavcategories'] = true;
				$html = file_get_contents($cache['filepath']);
				if (empty($html)) {
					$write_html = true;
				}
			}
			if ($write_html) {
				$html = '';
				$denomination = $this->fetch_denominations();
				foreach ($denomination as $demon) {
					$html .= '<li  nowrap="nowrap" style="list-style-type:none; height: 20px;"><h3 style="font-size:10pt;font-family:arial,helvetica,verdana,sans-serif; height:30px; margin: 0px;"><a  href="' . HTTP_SERVER . 'Denomination/' . $demon['id'] . '/' . construct_seo_url_name($demon['denomination_long']) . '" >' . $demon['denomination_long'] . '</a><span style=" color:#666666; font-size:12px;"> (' . $demon['auction_count'] . ') </span></h3></li>';
				}
				$f = fopen(DIR_TMP . $cache['filename'], 'w');
				if ($f === false) {
					@unlink(DIR_TMP . $cache['filename']);
				} else {
					fwrite($f, $html);
					fclose($f);
					@unlink(DIR_TMP . $cache['filename']);
				}
			}
		}
		return $html;
	}
	function html_denomination() {
		global $ilance;
		$html = '';
		$html .= $this->print_subcategory_columns_denominations(4, 'product', 1, 'eng', 0);
		return $html;
	}
//karthik  on may30
	function html_denomination_price() {
		global $ilance;
		$html = '';
		$html .= $this->print_subcategory_columns_denominations_price(4, 'product', 1, 'eng', 0);
		return $html;
	}

	function html_denomination_price_4567() {
		global $ilance;
		$html = '';
		$html .= $this->print_subcategory_columns_denominations_price_4567(4, 'product', 1, 'eng', 0);
		return $html;
	}

	function html_coin_series($denomination_id = 1) {
		global $ilance;
		$html = '';
		$html .= $this->print_subcategory_columns_coin_series($denomination_id);
		return $html;
	}

	function html_coin_class($series_id) {
		global $ilance;
		$html = '';
		$html .= $this->print_subcategory_columns_coin_class($series_id);
		return $html;
	}
	function demonomination_dropwdown($name = 'denominationid', $selected_id, $first_row = false) {
		global $ilance;
		$denomination = $this->fetch_denominations();
		$html = '<select name="' . $name . '" style="font-family: verdana;"  id="' . $name . '" >';
		$html .= $first_row ? '<option value="">All Denominations</option><option value="">----------------------------------</option>' : '';
		//print_r($denomination);
		foreach ($denomination as $denom) {
			if ($selected_id == $denom['id']) {
				$html .= '<option value="' . $denom['id'] . '"  selected="selected">' . $denom['denomination_long'] . '</option>';
			} else {
				$html .= '<option value="' . $denom['id'] . '" >' . $denom['denomination_long'] . '</option>';
			}

		}
		$html .= '</select>';
		return $html;
	}
	//new change merch page listing
	function demonomination_dropwdown_new($name = 'denominationid', $selected_id, $first_row = false) {
		global $ilance, $ilconfig;
		$denomination = $this->fetch_denominations();
		$html = '<select name="' . $name . '" style="font-family: verdana;" id="' . $name . '">';
		//$html.=$first_row?'<option value="">All Denominations</option><option value="">----------------------------------</option>':'';
		//print_r($denomination);
		foreach ($denomination as $denom) {
			if ($ilconfig['globalauctionsettings_seourls'] == '1') {
				if ($selected_id == $denom['id']) {
					$html .= '<option value="Denomination/' . $denom['id'] . '/' . construct_seo_url_name($denom['denomination_long']) . '"  selected="selected">' . $denom['denomination_long'] . '</option>';} else {
					$html .= '<option value="Denomination/' . $denom['id'] . '/' . construct_seo_url_name($denom['denomination_long']) . '" >' . $denom['denomination_long'] . '</option>';}
			} else {
				if ($selected_id == $denom['id']) {
					$html .= '<option value="' . $denom['id'] . '"  selected="selected">' . $denom['denomination_long'] . '</option>';} else {
					$html .= '<option value="' . $denom['id'] . '" >' . $denom['denomination_long'] . '</option>';}
			}
		}
		$html .= '</select>';
		return $html;
	}
//karthik on may30
	function demonomination_dropwdown_price_new($name = 'denominationid', $selected_id, $first_row = false) {
		global $ilance, $ilconfig;
		$denomination = $this->fetch_denominations();
		$html = '<select name="' . $name . '" style="font-family: verdana;" id="' . $name . '">';
		//$html.=$first_row?'<option value="">All Denominations</option><option value="">----------------------------------</option>':'';
		//print_r($denomination);
		foreach ($denomination as $denom) {
			if ($ilconfig['globalauctionsettings_seourls'] == '1') {
				if ($selected_id == $denom['id']) {
					$html .= '<option value="CoinPrices/' . $denom['id'] . '/' . construct_seo_url_name($denom['denomination_long']) . '"  selected="selected">' . $denom['denomination_long'] . '</option>';} else {
					$html .= '<option value="CoinPrices/' . $denom['id'] . '/' . construct_seo_url_name($denom['denomination_long']) . '" >' . $denom['denomination_long'] . '</option>';}
			} else {
				if ($selected_id == $denom['id']) {
					$html .= '<option value="' . $denom['id'] . '"  selected="selected">' . $denom['denomination_long'] . '</option>';} else {
					$html .= '<option value="' . $denom['id'] . '" >' . $denom['denomination_long'] . '</option>';}
			}
		}
		$html .= '</select>';
		return $html;
	}
	function series_dropwdown_new($denomination_id, $name = 'series', $selected_id, $first_row = false) {
		global $ilance;
		$series_details = $this->fetch_coin_series($denomination_id);
		$html = '<select name="' . $name . '" style="font-family: verdana;width: 280px;">';
		$html .= $first_row ? '<option value="">Select Series</option><option value="">----------------------------------</option>' : '';
		//print_r($denomination);
		foreach ($series_details as $series) {
			if ($selected_id == $series['id']) {
				$html .= '<option value="' . $series['id'] . '"  selected="selected">' . $series['coin_series_name'] . '</option>';
			} else {
				$html .= '<option value="' . $series['id'] . '" >' . $series['coin_series_name'] . '</option>';
			}

		}
		$html .= '</select>';
		return $html;
	}
	function series_dropwdown($denomination_id, $name = 'series_id', $selected_id, $first_row = false) {
		global $ilance;
		$series_details = $this->fetch_coin_series($denomination_id);
		$html = '<select name="' . $name . '" style="font-family: verdana;"  id="' . $name . '" >';
		$html .= $first_row ? '<option value="">Select Series</option><option value="">----------------------------------</option>' : '';
		//print_r($denomination);
		foreach ($series_details as $series) {
			if ($selected_id == $series['id']) {
				$html .= '<option value="' . $series['id'] . '"  selected="selected">' . $series['coin_series_name'] . '</option>';
			} else {
				$html .= '<option value="' . $series['id'] . '" >' . $series['coin_series_name'] . '</option>';
			}

		}
		$html .= '</select>';
		return $html;
	}
	function coin_class_dropwdown($series_id, $pcgs = 0, $name = 'cid', $selected_id, $first_row = false) {
		global $ilance;
		$coin_class_details = $this->fetch_coin_class($series_id);
		$html = '<select name="' . $name . '" style="font-family: verdana;"  id="' . $name . '" >';
		$html .= $first_row ? '<option value="">Select Series</option><option value="">----------------------------------</option>' : '';
		//print_r($denomination);
		foreach ($coin_class_details as $coin_class) {
			if ($selected_id == $coin_class['id'] or $pcgs = $coin_class['PCGS']) {
				$html .= '<option value="' . $coin_class['PCGS'] . '"  selected="selected">' .
				$coin_class['coin_detail_year'] . " " .
				$coin_class['coin_detail_coin_series'] . " " .
				$coin_class['coin_detail_suffix'] . " " .
				$coin_class['coin_detail_major_variety'] . " " . '</option>';
			} else {
				$html .= '<option value="' . $coin_class['PCGS'] . '">' .
				$coin_class['coin_detail_year'] . " " .
				$coin_class['coin_detail_coin_series'] . " " .
				$coin_class['coin_detail_suffix'] . " " .
				$coin_class['coin_detail_major_variety'] . " " . '</option>';
			}

		}
		$html .= '</select>';
		return $html;
	}
	function get_project_count($denomination_id = 0, $series_id = 0, $pcgs = 0) {
		global $ilance;
		$query = '';
		//PCGS, coin_series_unique_no, coin_series_denomination_no
		if ($denomination_id > 0) {
			$query = $ilance->db->query("select PCGS from " . DB_PREFIX . "catalog_coin where coin_series_denomination_no='" . $denomination_id . "'");
			while ($line = $ilance->db->fetch_array($query)) {
				if (!empty($line['PCGS'])) {
					$k[] = $line['PCGS'];
				}

			}
			$jions = implode(",", $k);
		}
		if ($series_id > 0) {
			$query = $ilance->db->query("select PCGS from " . DB_PREFIX . "catalog_coin where coin_series_unique_no='" . $series_id . "'");
			while ($line = $ilance->db->fetch_array($query)) {
				if (!empty($line['PCGS'])) {
					$k[] = $line['PCGS'];
				}

			}
			$jions = implode(",", $k);
		}
		if ($pcgs > 0) {
			$query = $pcgs;
		}
		$result = $ilance->db->query("select count(*) from " . DB_PREFIX . "projects where status='open' and cid in (" . $jions . ")");
		$row = $ilance->db->fetch_array($result);
		return $row[0];
	}
	function fetch_children_pcgs($series_id) {
		global $ilance;
		$result = $ilance->db->query("select PCGS from " . DB_PREFIX . "catalog_coin where coin_series_unique_no='" . $series_id . "'");
		if ($ilance->db->num_rows($result) > 0) {
			$count = 0;
			while ($row = $ilance->db->fetch_array($result)) {
				$rs[$count] = $row['PCGS'];
				$count++;
			}
			return implode(",", $rs);
		} else {
			return '';
		}
	}
	//sekar works on cache
	function print_popupintop_nav() {
		global $ilance, $ilconfig, $phrase;
		if ($ilconfig['categorymapcache'] == false) {
			$html = '<div class="grayborder"><div class="n"><div class="e"><div class="w"></div></div></div><div><div style="padding-left:6px; padding-top:3px; font-size:11px; font-family: verdana" class="blue">&nbsp;<span class="black">Popular Categories</span></div>' .
			$this->print_subcategory_columns_denominations(4, $ilconfig['categorylinkheaderpopuptype'], 1, $_SESSION['ilancedata']['user']['slng'], 0, '', 0, 1, 'font-size:11px; font-family: verdana', '', 1, 'topnav') .
			'</div><div class="s"><div class="e"><div class="w"></div></div></div></div>';
		} else {
			$cache['filename'] = 'topnav_denomination_popup.html';
			$cache['filepath'] = DIR_TMP . $cache['filename'];
			$write_html = false;
			if (file_exists($cache['filepath'])) {
				$lastmod = filemtime($cache['filepath']);
				if (($lastmod + $ilconfig['categorymapcachetimeout'] * 60) < mktime()) {
					// #### cache template is outdated
					$write_html = true;
				}
			} else {
				// #### the cache template file does not exist! we need to generate something!
				$write_html = true;
			}
			if ($write_html == false) {
				$show['leftnavcategories'] = true;
				$html = file_get_contents($cache['filepath']);
				if (empty($html)) {
					$write_html = true;
				}
			}
			if ($write_html) {
				$html = '<div class="grayborder">
									<div class="n"><div class="e"><
									div class="w"></div></div></div>
									<div>
									<div style="padding-left:6px; padding-top:3px; font-size:11px; font-family: verdana" class="blue">
									<strong>' . $phrase['_quick_nav'] . '</strong>&nbsp;<span class="black">' . $phrase['_categories'] . '</span>
									</div>' .
				$this->print_subcategory_columns_denominations(4, $ilconfig['categorylinkheaderpopuptype'], 1, $_SESSION['ilancedata']['user']['slng'], 0, '', 0, 1, 'font-size:11px; font-family: verdana', '', 1, 'topnav') .
				'</div><div class="s"><div class="e"><div class="w"></div></div></div></div>';
				$cache['unique_name'] = rand(0, 100000);
				while (file_exists(DIR_TMP . $cache['unique_name']));
				{
					$f = fopen(DIR_TMP . $cache['unique_name'], 'w');
					if ($f === false) {
						@unlink(DIR_TMP . $cache['filename']);
					} else {
						DIR_TMP . $cache['filename'];
						fwrite($f, $html);
						fclose($f);
						@unlink(DIR_TMP . $cache['filename']);
						rename(DIR_TMP . $cache['unique_name'], DIR_TMP . $cache['filename']);
						@unlink(DIR_TMP . $cache['unique_name']);
					}
				}
			}
		}
		return $html;
	}
//sekar on auction count
	function reset_auction_count() {
		global $ilance;
//update
		$firsttop = $ilance->db->query("UPDATE " . DB_PREFIX . "catalog_toplevel set auction_count='0'", 0, null, __FILE__, __LINE__);
		$secondlevel = $ilance->db->prefix("UPDATE " . DB_PREFIX . "catalog_second_level set auction_count='0'");
//update
		$level1 = $ilance->db->query("select * from " . DB_PREFIX . "catalog_toplevel", 0, null, __FILE__, __LINE__);
		while ($row = $ilance->db->fetch_array($level)) {
			$denomunino = $row['denomination_unique_no'];
			$toplavel_total_sum = 0;
			$level2 = $ilance->db->query("select * from " . DB_PREFIX . "catalog_second_level where coin_series_unique_no= '" . $denomunino . "'");
			while ($row1 = $ilance->db->fetch_array($level2)) {
				$coinseriesno = $row1['coin_series_unique_no'];
				$sum1 = 0;
				$level3 = $ilance->db->query("select * from " . DB_PREFIX . "catalog_coin where coin_series_unique_no='" . $row1['coin_series_unique_no'] . "'", 0, null, __FILE__, __LINE__);
				while ($row2 = $ilance->db->fetch_array($level3)) {
					$pcgs = $row2['PCGS'];
					$eversum = $ilance->db->query("select auctioncount from " . DB_PREFIX . "categories where cid='" . $pcgs . "'", 0, null, __FILE__, __LINE__);
					while ($row3 = $ilance->db->fetch_array($eversum)) {
						$eachsum = $row3['auctioncount'];
						$sum1 = $sum1 + $row3['auction_count'];
						$toplavel_total_sum = $toplavel_total_sum + $row3['auction_count'];
					}
				}
				//update second level count
				$secondlevel = $ilance->db->prefix("UPDATE " . DB_PREFIX . "catalog_second_level set auction_count='$sum1' where coin_series_unique_no= '" . $denomunino . "' ");
			}
			//update toplevel auction count>>    toplavel_total_sum
			$toplevel = $ilance->db->prefix("UPDATE " . DB_PREFIX . "catalog_toplevel set auction_count='$toplavel_total_sum' where denomination_unique_no= '" . $coinseriesno . "'");
		}
	}
//Nov - 11
	function demonomination_dropdwn($name = 'denominationid', $selected_id, $first_row = false, $date = '') {
		global $ilance, $ilconfig;
		$denomination = $this->fetch_auction($date);
		$html = '<select name="' . $name . '" style="font-family: verdana;" id="' . $name . '">';
		$html .= $denomination ? '' : '<option value="">No Category listed</option>';
		//print_r($denomination);
		foreach ($denomination as $denom) {
			if ($ilconfig['globalauctionsettings_seourls'] == '1') {
				if ($selected_id == $denom['id']) {
					$html .= '<option value="Denomination/' . $denom['id'] . '/' . construct_seo_url_name($denom['denomination_long']) . '/' . $date . '"  selected="selected">' . $denom['denomination_long'] . '</option>';} else {
					$html .= '<option value="Denomination/' . $denom['id'] . '/' . construct_seo_url_name($denom['denomination_long']) . '/' . $date . '" >' . $denom['denomination_long'] . '</option>';}
			} else {
				if ($selected_id == $denom['id']) {
					$html .= '<option value="' . $denom['id'] . '"  selected="selected">' . $denom['denomination_long'] . '</option>';} else {
					$html .= '<option value="' . $denom['id'] . '" >' . $denom['denomination_long'] . '</option>';}
			}
		}
		$html .= '</select>';
		return $html;
	}
	function html_denomination_new($date) {
		global $ilance;
		$html = '';
		$html .= $this->print_subcategory(4, 'product', 1, 'eng', 0, $date);
		return $html;
	}
	function print_subcategory($columns = 1, $cattype = 'product', $dosubcats = 1, $slng = 'eng', $cid = 0, $extra = '', $showcount = 1, $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $cacheid = '') {
		global $ilance, $myapi, $phrase, $ilconfig, $ilpage, $show, $storeid, $storetype, $sqlquery, $categoryfinderhtml, $sqlquery, $sqlqueryads, $recursive_html, $categorycache, $block, $blockcolor;
		$ilance->timer->start();
		if (!empty($cacheid)) {
			$cacheid = '_' . $cacheid;
		}
		$html = '';
		$html .= '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		/* $this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $columns);
		 */
		$denomination_details = $this->fetch_auction($extra);
		$check = 0;
		foreach ($denomination_details as $denom_detail) {
			//$count=$this->get_project_count($denom_detail['id']);
			if ($check % $columns == 0) {
				$html .= '</tr><tr>';
			}
			if ($ilconfig['globalauctionsettings_seourls']) {
				$html .= '<td><a  href="' . HTTP_SERVER . 'Denomination/' . $denom_detail['id'] . '/' . construct_seo_url_name($denom_detail['denomination_long']) . '/' . $extra . '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['cnt'] . ')' . '</a></td>';
			} else {
				$html .= '<td><a  href="' . HTTP_SERVER . 'denomination.php?denomination=' . $denom_detail['id'] . '&date_end=' . $extra . '" >' . $denom_detail['denomination_long'] . ' (' . $denom_detail['cnt'] . ')' . '</a></td>';
			}

			$check++;
		}
		$html .= '<tr><td></td></table>';
		$ilance->timer->stop();
		DEBUG("print_subcategory_columns(\$columns = $columns, \$cattype = $cattype, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}
	function fetch_auction($date = '') {
		global $ilance;
		$con = "AND date(date_end) = '" . $date . "'";
		$result = $ilance->db->query("SELECT count(*) as cnt, p.coin_series_denomination_no,c.denomination_long,c.id
			                                              FROM " . DB_PREFIX . "projects p,
                                                               " . DB_PREFIX . "catalog_toplevel c
										         	WHERE p.coin_series_denomination_no = c.denomination_unique_no
													AND  p.status =  'open'
													$con
													group by c.denomination_long
													order by c.denomination_unique_no asc
									", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result)) {
			$count = 0;
			while ($row = $ilance->db->fetch_array($result)) {
				$denom[$count] = $row;
				$count++;
				if ($denomination_id != 0) {
					return $row;
				}

			}
		} else {
//		exit();
		}
		return $denom;
	}
	//nov 22
	function series_dropwdwn_new($denomination_id, $name = 'series', $selected_id, $first_row = false, $date) {
		global $ilance;
		$series_details = $this->fetch_auction_new($denomination_id, $date);
		$html = '<select name="' . $name . '" style="font-family: verdana;width: 280px;">';
		$html .= $first_row ? '<option value="">Select Series</option><option value="">----------------------------------</option>' : '';
		//print_r($denomination);
		foreach ($series_details as $series) {
			if ($selected_id == $series['coin_series_unique_no']) {
				$html .= '<option value="' . $series['coin_series_unique_no'] . '"  selected="selected">' . $series['coin_series_name'] . '</option>';
			} else {
				$html .= '<option value="' . $series['coin_series_unique_no'] . '" >' . $series['coin_series_name'] . '</option>';
			}

		}
		$html .= '</select>';
		return $html;
	}
	function html_coin_series_new($id, $date) {
		global $ilance;
		$html = '';
		$html .= $this->print_subcategory_new(4, 'product', 1, 'eng', $id, $date);
		return $html;
	}
	function print_subcategory_new($columns = 1, $cattype = 'product', $dosubcats = 1, $slng = 'eng', $denom_id = 0, $extra = '', $showcount = 1, $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $cacheid = '') {
		global $ilance, $myapi, $phrase, $ilconfig, $ilpage, $show, $storeid, $storetype, $sqlquery, $categoryfinderhtml, $sqlquery, $sqlqueryads, $recursive_html, $categorycache, $block, $blockcolor;
		$ilance->timer->start();
		if (!empty($cacheid)) {
			$cacheid = '_' . $cacheid;
		}
		$date_end_link=(strlen($extra)==10)?'/'.$extra:'';
		$html = '';
		$html .= '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		/* $this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $columns);
		 */
		$denomination_details = $this->fetch_auction_new($denom_id, $extra);
		$check = 0;
		foreach ($denomination_details as $denom_detail) {
			//$count=$this->get_project_count($denom_detail['id']);
			if ($check % $columns == 0) {
				$html .= '</tr><tr>';
			}
			if ($ilconfig['globalauctionsettings_seourls']) {
				$html .= '<td><a  href="' . HTTP_SERVER . 'Series/' . $denom_detail['coin_series_unique_no'] . '/' . construct_seo_url_name($denom_detail['coin_series_name']).$date_end_link.'" >' . $denom_detail['coin_series_name'] . ' (' . $denom_detail['cnt'] . ')' . '</a></td>';
			} else {
				$html .= '<td><a  href="' . HTTP_SERVER . 'search.php?mode=product&series=' . $denom_detail['coin_series_unique_no'] . '" >' . $denom_detail['coin_series_name'] . ' (' . $denom_detail['cnt'] . ')' . '</a></td>';
			}

			$check++;
		}
		$html .= '<tr><td></td></table>';
		$ilance->timer->stop();
		DEBUG("print_subcategory_columns(\$columns = $columns, \$cattype = $cattype, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}
	function fetch_auction_new($id, $date = '') {
		global $ilance;
		$con = "AND date(date_end) = '" . $date . "'";
		$result = $ilance->db->query("SELECT count(*) as cnt, p.coin_series_unique_no,c.coin_series_name
			                                              FROM " . DB_PREFIX . "projects p,
                                                               " . DB_PREFIX . "catalog_second_level c
										         	WHERE p.coin_series_denomination_no='" . $id . "'
													AND p.coin_series_unique_no = c.coin_series_unique_no
													AND  p.status =  'open'
													$con
													group by c.coin_series_unique_no
													order by c.coin_series_unique_no asc
									", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result)) {
			$count = 0;
			while ($row = $ilance->db->fetch_array($result)) {
				$denom[$count] = $row;
				$count++;
				if ($denomination_id != 0) {
					return $row;
				}

			}
		} else {
//		exit();
		}
		return $denom;
	}
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>
