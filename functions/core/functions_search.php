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
* Core Search functions for ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/

function handle_search_verbose($text = '')
{
	$GLOBALS['verbose'][] = $text;
}

function handle_search_verbose_filters($text = '')
{
	$GLOBALS['verbose_filter'][] = $text;
}

function handle_search_verbose_save($text = '')
{
	$GLOBALS['verbose_save'][] = $text;
}

function print_search_verbose_saved($filter = '')
{
        $html = '';
        if (!empty($GLOBALS["$filter"]))
        {
                foreach ($GLOBALS["$filter"] AS $key => $text)
                {
                        $html .= $text;
                }
        }
        
        return $html;
}

/*
* Function to save search keywords inputted by users from the search menu input boxes throughout the marketplace into the db.
* Additionally, this function will work with multiple keywords separated via comma (ie: keyword1, keyword2, etc)
*
* @param       string         keywords
* @param       string         search mode
*
* @return      nothing
*/
function handle_search_keywords($keywords = '', $mode = '')
{
        global $ilance, $myapi;
        
        // allow the following modes only
        $staticmodes = array('service','product','experts','stores');
        if (!in_array($mode, $staticmodes))
        {
                $mode = '';
        }
        
        // use api hook below if you need to update $staticmodes for your custom code
        ($apihook = $ilance->api('handle_search_keywords_start')) ? eval($apihook) : false;
        
        if (!empty($keywords) AND strchr($keywords, ','))
        {
                $keywords = explode(',', $keywords);
                if (sizeof($keywords) > 1)
                {
                        for ($i = 0; $i < sizeof($keywords); $i++)
                        {
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "search
                                        WHERE keyword = '" . trim($ilance->db->escape_string($keywords[$i])) . "'
                                                AND searchmode = '" . $ilance->db->escape_string($mode) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "search
                                                (id, keyword, searchmode, count)
                                                VALUES(
                                                NULL,
                                                '" . trim($ilance->db->escape_string($keywords[$i])) . "',
                                                '" . $ilance->db->escape_string($mode) . "',
                                                '0')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "search
                                                SET count = count + 1
                                                WHERE keyword = '" . trim($ilance->db->escape_string($keywords[$i])) . "'
                                                        AND searchmode = '" . $ilance->db->escape_string($mode) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                // keep history of a registered users search patterns
                                if (!empty($_SESSION['ilancedata']['user']['userid']))
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "search_users
                                                (id, user_id, keyword, searchmode, added)
                                                VALUES(
                                                NULL,
                                                '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                                '" . trim($ilance->db->escape_string($keywords[$i])) . "',
                                                '" . $ilance->db->escape_string($mode) . "',
                                                '" . DATETIME24H . "')
                                        ", 0, null, __FILE__, __LINE__);        
                                }
                        }
                }
        }
        else 
        {
                if (!empty($keywords))
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "search
                                WHERE keyword = '" . trim($ilance->db->escape_string($keywords)) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) == 0)
                        {
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "search
                                        (id, keyword, searchmode, count)
                                        VALUES(
                                        NULL,
                                        '" . trim($ilance->db->escape_string($keywords)) . "',
                                        '" . $ilance->db->escape_string($mode) . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                        }
                        else
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "search
                                        SET count = count + 1
                                        WHERE keyword = '" . trim($ilance->db->escape_string($keywords)) . "'
                                                AND searchmode = '" . $ilance->db->escape_string($mode) . "'
                                ", 0, null, __FILE__, __LINE__);
                        }
                        
                        // keep history of a registered users search patterns
                        if (!empty($_SESSION['ilancedata']['user']['userid']))
                        {
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "search_users
                                        (id, user_id, keyword, searchmode, added)
                                        VALUES(
                                        NULL,
                                        '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                        '" . trim($ilance->db->escape_string($keywords)) . "',
                                        '" . $ilance->db->escape_string($mode) . "',
                                        '" . DATETIME24H . "')
                                ", 0, null, __FILE__, __LINE__);        
                        }
                }
        }
}

/*
* Function to print the active countries pulldown menu
*
* @param       string         fieldname
* @param       string         selected option value (if applicable)
* @param       string         short form language code (eng, ger, pol, etc)
*
* @return      string         Returns HTML representation of the pulldown menu
*/
function print_active_countries_pulldown($fieldname = '', $selected = '', $slng = 'eng', $showworldwide = true, $id = '', $shownames = false)
{
        global $ilance, $myapi, $phrase;
        
        $html = '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
        
        if ($showworldwide)
        {
                if ($selected == 'all')
                {
                        $html .= '<option value="all" selected="selected">' . (($showworldwide)
				? $phrase['_worldwide']
				: '-') . '</option>';
                }
                else
                {
                        $html .= '<option value="all">' . (($showworldwide)
				? $phrase['_worldwide']
				: '-') . '</option>';
                }
        }
        
        $sql = $ilance->db->query("
                SELECT p.countryid, c.locationid, c.location_$slng AS location
                FROM " . DB_PREFIX . "projects AS p,
                " . DB_PREFIX . "locations AS c
                WHERE p.countryid = c.locationid
                GROUP by p.countryid
        ", 0, null, __FILE__, __LINE__);
        while ($crow = $ilance->db->fetch_array($sql, DB_ASSOC))
        {
                if ((isset($ilance->GPC['country']) OR isset($ilance->GPC['radiuscountry'])) AND $crow['locationid'] == $selected)
                {
                        $html .= '<option value="' . (($shownames)
				? $crow['location']
				: $crow['locationid']) . '" selected="selected">' . stripslashes($crow['location']) . '</option>';
                }
                else if ((!isset($ilance->GPC['country']) OR !isset($ilance->GPC['radiuscountry'])) AND $crow['locationid'] == $selected)
                {
                        $html .= '<option value="' . (($shownames)
				? $crow['location']
				: $crow['locationid']) . '" selected="selected">' . stripslashes($crow['location']) . '</option>';
                }
                else
                {
                        $html .= '<option value="' . (($shownames)
				? $crow['location']
				: $crow['locationid']) . '">' . stripslashes($crow['location']) . '</option>';
                }
        }
	
        $html .= '</select>';
        
        return $html;
}

/*
* Function to print the regions (continents)
*
* @param       string         fieldname
* @param       string         selected option value (if applicable)
* @param       string         short form language code (eng, ger, pol, etc)
* @param       string         element object id (id="")
* @param       string         display type to print (pulldown or links)
* @param       boolean        determine if we want to handle onchange on pulldowns to disable distance bit when only a region contains no country id.
*                             Example: <option value="north_america" (disable via onchange) vs <option value="north_america.330" (since we have a country id)
* @param       integer        search form id (<form id="xx">..)
*
* @return      string         Returns HTML representation of the pulldown menu
*/
function print_regions($fieldname = '', $selected = '', $slng = 'eng', $id = '', $displaytype = 'pulldown', $onchange = false, $searchformid = '0')
{
        global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $ilregions;
        
	$html = '';
	$showonlycountryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
	
	$sql = $ilance->db->query("
		SELECT region
		FROM " . DB_PREFIX . "locations
		GROUP BY region
		ORDER BY region ASC
	", 0, null, __FILE__, __LINE__);
	
	if ($displaytype == 'pulldown')
	{
		if ($onchange AND $ilconfig['globalserver_enabledistanceradius'])
		{
			if ($searchformid == '1')
			{
				$distancediv1 = 'if (DISTANCE == 1){fetch_js_object(\'cb_servicedistance\').disabled = false; fetch_js_object(\'cb_servicedistance\').checked = false; toggle_show(\'toggleradiusservice\');}';
				$distancediv2 = 'if (DISTANCE == 1){fetch_js_object(\'cb_servicedistance\').disabled = true; fetch_js_object(\'serviceradius\').disabled = true; fetch_js_object(\'serviceradiuszip\').disabled = true; toggle_hide(\'toggleradiusservice\');}';
			}
			else if ($searchformid == '2')
			{
				$distancediv1 = 'if (DISTANCE == 1){fetch_js_object(\'cb_productdistance\').disabled = false; fetch_js_object(\'cb_productdistance\').checked = false; toggle_show(\'toggleradiusproduct\');}';
				$distancediv2 = 'if (DISTANCE == 1){fetch_js_object(\'cb_productdistance\').disabled = true; fetch_js_object(\'productradius\').disabled = true; fetch_js_object(\'productradiuszip\').disabled = true; toggle_hide(\'toggleradiusproduct\');}';
			}
			else if ($searchformid == '3')
			{
				$distancediv1 = 'if (DISTANCE == 1){fetch_js_object(\'cb_expertdistance\').disabled = false; fetch_js_object(\'cb_expertdistance\').checked = false; toggle_show(\'toggleradiusexperts\');}';
				$distancediv2 = 'if (DISTANCE == 1){fetch_js_object(\'cb_expertdistance\').disabled = true; fetch_js_object(\'expertradius\').disabled = true; fetch_js_object(\'expertradiuszip\').disabled = true; toggle_hide(\'toggleradiusexperts\');}';
			}
		}
		
		$onchangejs = ($onchange AND $ilconfig['globalserver_enabledistanceradius'])
			? ' onchange="javascript:
			if (DISTANCE == 1)
			{
				var idselected = fetch_js_object(\'' . $id . '\').value
				if (idselected.indexOf(\'.\') == \'-1\')
				{
					' . $distancediv2 . '
				}
				else
				{
					' . $distancediv1 . '
				}
			}"'
			: '';
			
		$html .= '<select name="' . $fieldname . '" id="' . $id . '"' . $onchangejs . ' style="font-family: verdana">';
		
		// #### show option to only show country of installed site #####
		$html .= ($showonlycountryid > 0) ? '<option value="' . strtolower(str_replace(' ', '_', fetch_region_title_by_countryid($showonlycountryid))) . '.' . $showonlycountryid . '">' . $phrase['_only'] . ' ' . handle_input_keywords($ilconfig['registrationdisplay_defaultcountry']) . '</option>' : '';
		
		// #### show option to show results worldwide ##################
		$html .= '<option value="worldwide">' . $phrase['_worldwide'] . '</option><option value="" disabled="disabled">-----------------</option>';
		
		// #### loop through accepted regions of the installed site ####
		while ($crow = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$region = strtolower(str_replace(' ', '_', $crow['region']));
			if (isset($ilregions["$region"]) AND $ilregions["$region"])
			{
				$html .= '<option value="' . $region . '">' . handle_input_keywords($crow['region']) . '</option>';
			}
		}
		$html .= '</select>';
	}
	else if ($displaytype == 'links')
	{
		$html .= '<div style="padding-top:7px"></div>';
		$selected2 = $countryid = '';
		
		if (!empty($selected) AND strrchr($selected, '.'))
		{
			$regtemp = explode('.', $selected);
			if (!empty($regtemp[0]))
			{
				$selected = $regtemp[0];
			}
			if (!empty($regtemp[1]))
			{
				$selected2 = '.' . $regtemp[1];
				$countryid = $regtemp[1]; 
			}
			unset($regtemp);
		}
		else if (!empty($selected))
		{
			$regionname = $selected;
		}
		
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$regiontype = isset($ilance->GPC['regiontype']) AND !empty($ilance->GPC['regiontype']) ? intval($ilance->GPC['regiontype']) : '';
		$removeurl = rewrite_url($php_self, 'region=' . $selected . $selected2);
		$removeurl = rewrite_url($removeurl, 'regiontype=' . $regiontype);
		$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
		$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . $ilance->GPC['country']) : $removeurl;
		$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
		$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;

		// #### worldwide ##############################################
		$html .= ($selected == 'worldwide' OR (empty($ilance->GPC['region'])))
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_worldwide" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_worldwide\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_worldwide\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>' . $phrase['_worldwide'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_worldwide" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;regiontype=1&amp;region=worldwide" onmouseover="rollovericon(\'unsel_worldwide\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_worldwide\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_worldwide'] . '</a></span></div>';
			
		// #### show option to only show country of installed site #####
		if ($showonlycountryid > 0)
		{
			if (empty($ilance->GPC['region']) OR strrchr($ilance->GPC['region'], '.') == false)
			{
				$html .= (!empty($ilance->GPC['country']) AND $ilance->GPC['country'] == $ilconfig['registrationdisplay_defaultcountry'])
					? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="unsel_worldwide2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_worldwide2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'unsel_worldwide2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_only'] . ' ' . handle_input_keywords($ilconfig['registrationdisplay_defaultcountry']) . '</strong></a></span></div>'
					: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="sel_worldwide2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;country=' . handle_input_keywords($ilconfig['registrationdisplay_defaultcountry']) . '" onmouseover="rollovericon(\'sel_worldwide2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'sel_worldwide2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_only'] . ' ' . handle_input_keywords($ilconfig['registrationdisplay_defaultcountry']) . '</a></span></div>';
			}
		}			
		unset($removeurl, $regiontype);
		
		while ($crow = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$removeurl = rewrite_url($php_self, 'region=' . $selected);
			$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
			$regiontype = isset($ilance->GPC['regiontype']) AND !empty($ilance->GPC['regiontype']) ? intval($ilance->GPC['regiontype']) : '';
			$currentregion = strtolower(str_replace(' ', '_', $crow['region']));

			if ($currentregion == $selected)
			{
				if (isset($ilregions["$currentregion"]) AND $ilregions["$currentregion"])
				{
					$removeurl = rewrite_url($php_self, 'region=' . $selected . $selected2);
					$removeurl = rewrite_url($removeurl, 'regiontype=' . $regiontype);
					$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
					$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . $ilance->GPC['country']) : $removeurl;
					$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
					$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
					$html .= '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_' . strtolower(str_replace(' ', '_', $crow['region'])). '" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_' . strtolower(str_replace(' ', '_', $crow['region'])). '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_' . strtolower(str_replace(' ', '_', $crow['region'])). '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $crow['region'] . '</strong></a></span></div>';
					
					if (!empty($countryid) AND !empty($selected2))
					{
						$removeurl = rewrite_url($php_self, $selected2);
						$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
						$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . $ilance->GPC['country']) : $removeurl;
						$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
						$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
						$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
						$html .= '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_' . strtolower(str_replace(' ', '_', $crow['region'])) . $selected2 . '" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_' . strtolower(str_replace(' ', '_', $crow['region'])). $selected2 . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_' . strtolower(str_replace(' ', '_', $crow['region'])) . $selected2 . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . print_country_name($countryid, $_SESSION['ilancedata']['user']['slng'], false) . '</strong></a></span></div>';
					}
				}
			}
			else
			{
				if (isset($ilregions["$currentregion"]) AND $ilregions["$currentregion"])
				{
					$removeurl = rewrite_url($php_self, 'region=' . $selected . $selected2);
					$removeurl = rewrite_url($removeurl, 'regiontype=' . $regiontype);
					$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
					$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . $ilance->GPC['country']) : $removeurl;
					$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
					$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
					$html .= '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_' . strtolower(str_replace(' ', '_', $crow['region'])). '" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;regiontype=1&amp;region=' . strtolower(str_replace(' ', '_', $crow['region'])). '" onmouseover="rollovericon(\'unsel_' . strtolower(str_replace(' ', '_', $crow['region'])). '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_' . strtolower(str_replace(' ', '_', $crow['region'])). '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $crow['region'] . '</a></span></div>';
				}
			}
			
			unset($removeurl, $regiontype);
		}
	}
        
        return $html;
}

function print_buying_formats()
{
	global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $show, $clear_listtype_url;
	
	$html = '';
	
	$auction = (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'] > 0) ? intval($ilance->GPC['auction']) : '';
	$buynow = (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'] > 0) ? intval($ilance->GPC['buynow']) : '';
	$inviteonly = (isset($ilance->GPC['inviteonly']) AND $ilance->GPC['inviteonly'] > 0) ? intval($ilance->GPC['inviteonly']) : '';
	$scheduled = (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled'] > 0) ? intval($ilance->GPC['scheduled']) : '';
	$lub = (isset($ilance->GPC['lub']) AND $ilance->GPC['lub'] > 0 AND $ilconfig['enable_uniquebidding']) ? intval($ilance->GPC['lub']) : '';
	$penny = (isset($ilance->GPC['penny']) AND $ilance->GPC['penny'] > 0) ? intval($ilance->GPC['penny']) : '';
	
	$removeurlall = rewrite_url($php_self, 'auction=' . $auction);
	$removeurlall = rewrite_url($removeurlall, 'buynow=' . $buynow);
	$removeurlall = rewrite_url($removeurlall, 'inviteonly=' . $inviteonly);
	$removeurlall = rewrite_url($removeurlall, 'scheduled=' . $scheduled);
	$removeurlall = rewrite_url($removeurlall, 'lub=' . $lub);
	$removeurlall = rewrite_url($removeurlall, 'penny=' . $penny);
	$clear_listtype_url = $removeurlall;

	// all
	$html .= ($show['allbuyingformats'])
		? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_auctiontype0" /></span><span class="blueonly"><a href="' . $removeurlall . '" onmouseover="rollovericon(\'sel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>' . $phrase['_any'] . '</strong></a></span></div>'
		: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype0" /></span><span class="blueonly"><a href="' . $removeurlall . '" onmouseover="rollovericon(\'unsel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_any'] . '</a></span></div>';
	
	// make sure our php_self string contains a ?
	if (strrchr($php_self, "?") == false)
	{
		// we'll include our master variable which should rewrite our urls nice and friendly
		$php_self = $php_self . '?mode=' . $ilance->GPC['mode'];
	}
	
	if ($ilance->GPC['mode'] == 'product')
	{
		// forward auction
		$removeurl = rewrite_url($php_self, 'auction=' . $auction);
		$html .= (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_forward_auction'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;auction=1" onmouseover="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_forward_auction'] . '</a></span></div>';
			
		$removeurl = rewrite_url($php_self, 'buynow=' . $buynow);
		$html .= (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_buy_now'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype5" /></span><span class="blueonly"><a href="' . $php_self . '&amp;buynow=1" onmouseover="rollovericon(\'unsel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_buy_now'] . '</a></span></div>';
		
		// lowest unique bid events
		if ($ilconfig['enable_uniquebidding'])
		{
			$removeurl = rewrite_url($php_self, 'lub=' . $lub);
			$html .= (isset($ilance->GPC['lub']) AND $ilance->GPC['lub'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_lowest_unique_bid'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype4" /></span><span class="blueonly"><a href="' . $php_self . '&amp;lub=1" onmouseover="rollovericon(\'unsel_auctiontype4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_lowest_unique_bid'] . '</a></span></div>';
		}
		
		/*$removeurl = rewrite_url($php_self, 'penny=' . $penny);
		$html .= (isset($ilance->GPC['penny']) AND $ilance->GPC['penny'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_penny" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_penny\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_penny\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_penny_auctions'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_penny" /></span><span class="blueonly"><a href="' . $php_self . '&amp;penny=1" onmouseover="rollovericon(\'unsel_penny\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_penny\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_penny_auctions'] . '</a></span></div>';*/
				
	}
	else if ($ilance->GPC['mode'] == 'service')
	{
		// reverse auction
		$removeurl = rewrite_url($php_self, 'auction=' . $auction);
		$html .= (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_reverse_auction'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;auction=1" onmouseover="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_reverse_auction'] . '</a></span></div>';
		
		// invite only
		$removeurl = rewrite_url($php_self, 'inviteonly=' . $inviteonly);		
		$html .= (isset($ilance->GPC['inviteonly']) AND $ilance->GPC['inviteonly'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_invite_only'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;inviteonly=1" onmouseover="rollovericon(\'unsel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_invite_only'] . '</a></span></div>';
			
	}
	
	// upcoming scheduled
	$removeurl = rewrite_url($php_self, 'scheduled=' . $scheduled);
	$html .= (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled'] == '1')
		? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_scheduled'] . '</strong></a></span></div>'
		: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype3" /></span><span class="blueonly"><a href="' . $php_self . '&amp;scheduled=1" onmouseover="rollovericon(\'unsel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_scheduled'] . '</a></span></div>';
	
	return $html;
}

function print_options($mode = 'product')
{
	global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $show, $clear_options, $clear_options_all;
	
	$html = '<div style="padding-top:7px"></div>';

	if ($mode == 'service' OR $mode == 'product')
	{
		$images = (isset($ilance->GPC['images']) AND $ilance->GPC['images'] > 0) ? intval($ilance->GPC['images']) : '';
		$publicboard = (isset($ilance->GPC['publicboard']) AND $ilance->GPC['publicboard'] > 0) ? intval($ilance->GPC['publicboard']) : '';
		$freeshipping = (isset($ilance->GPC['freeshipping']) AND $ilance->GPC['freeshipping'] > 0) ? intval($ilance->GPC['freeshipping']) : '';
		$listedaslots = (isset($ilance->GPC['listedaslots']) AND $ilance->GPC['listedaslots'] > 0) ? intval($ilance->GPC['listedaslots']) : '';
		$escrow = (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'] > 0 AND $ilconfig['escrowsystem_enabled']) ? intval($ilance->GPC['escrow']) : '';
		$budget = (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] > 0) ? intval($ilance->GPC['budget']) : '';
		$donation = (isset($ilance->GPC['donation']) AND $ilance->GPC['donation'] > 0) ? intval($ilance->GPC['donation']) : '';
		$completed = (isset($ilance->GPC['completed']) AND $ilance->GPC['completed'] > 0) ? intval($ilance->GPC['completed']) : '';
		
		$removeurlall = rewrite_url($php_self, 'images=' . $images);
		$removeurlall = rewrite_url($removeurlall, 'publicboard=' . $publicboard);
		$removeurlall = rewrite_url($removeurlall, 'freeshipping=' . $freeshipping);
		$removeurlall = rewrite_url($removeurlall, 'listedaslots=' . $listedaslots);
		$removeurlall = rewrite_url($removeurlall, 'escrow=' . $escrow);
		$removeurlall = rewrite_url($removeurlall, 'budget=' . $budget);
		$removeurlall = rewrite_url($removeurlall, 'donation=' . $donation);
		$removeurlall = rewrite_url($removeurlall, 'completed=' . $completed);
		$clear_options = $removeurlall;
		$clear_options_all = (empty($images) AND empty($publicboard) AND empty($freeshipping) AND empty($listedaslots) AND empty($escrow) AND empty($donation) AND empty($completed))
			? ''
			: $removeurlall;
	
		// make sure our php_self string contains a ?
		if (strrchr($php_self, "?") == false)
		{
			// we'll include our master variable which should rewrite our urls nice and friendly
			$php_self = $php_self . '?mode=' . $ilance->GPC['mode'];
		}
		// murugan Changes Jan 13
		// show with message board
		/*$removeurl = rewrite_url($php_self, 'publicboard=' . $publicboard);
		$html .= (isset($ilance->GPC['publicboard']) AND $ilance->GPC['publicboard'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_listings_with_active_public_message_boards'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;publicboard=1" onmouseover="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_listings_with_active_public_message_boards'] . '</a></span></div>';*/
		
		/*if ($ilance->GPC['mode'] == 'product')
		{
			// show with images only
			$removeurl = rewrite_url($php_self, 'images=' . $images);
			$html .= (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_only_with_images'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;images=1" onmouseover="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_only_with_images'] . '</a></span></div>';
			
			// free shipping
			$removeurl = rewrite_url($php_self, 'freeshipping=' . $freeshipping);		
			$html .= (isset($ilance->GPC['freeshipping']) AND $ilance->GPC['freeshipping'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_items_with_free_shipping'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options3" /></span><span class="blueonly"><a href="' . $php_self . '&amp;freeshipping=1" onmouseover="rollovericon(\'unsel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_items_with_free_shipping'] . '</a></span></div>';
			
			// items listed as lots
			$removeurl = rewrite_url($php_self, 'listedaslots=' . $listedaslots);
			$html .= (isset($ilance->GPC['listedaslots']) AND $ilance->GPC['listedaslots'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_items_listed_as_lots'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options4" /></span><span class="blueonly"><a href="' . $php_self . '&amp;listedaslots=1" onmouseover="rollovericon(\'unsel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_items_listed_as_lots'] . '</a></span></div>';
			
			if ($ilconfig['escrowsystem_enabled'])
			{
				// items being sold via escrow
				$removeurl = rewrite_url($php_self, 'escrow=' . $escrow);
				$html .= (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'] == '1')
					? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_items_that_sellers_require_secure_escrow'] . '</strong></a></span></div>'
					: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options5" /></span><span class="blueonly"><a href="' . $php_self . '&amp;escrow=1" onmouseover="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_items_that_sellers_require_secure_escrow'] . '</a></span></div>';
			}
			
			// include nonprofit selling items
			$removeurl = rewrite_url($php_self, 'donation=' . $donation);
			$html .= (isset($ilance->GPC['donation']) AND $ilance->GPC['donation'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options6" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_donation_items'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options6" /></span><span class="blueonly"><a href="' . $php_self . '&amp;donation=1" onmouseover="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_donation_items'] . '</a></span></div>';
				
		}*/
		else if ($ilance->GPC['mode'] == 'service')
		{
			if ($ilconfig['escrowsystem_enabled'])
			{
				// items being sold via escrow
				$removeurl = rewrite_url($php_self, 'escrow=' . $escrow);
				$html .= (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'] == '1')
					? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_projects_that_use_secure_escrow'] . '</strong></a></span></div>'
					: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options5" /></span><span class="blueonly"><a href="' . $php_self . '&amp;escrow=1" onmouseover="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_projects_that_use_secure_escrow'] . '</a></span></div>';
			}
			
			// show with specific budget range
			$removeurl = rewrite_url($php_self, 'budget=' . $budget);
			$html .= (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options6" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_only_show_projects_with_nondisclosed_budgets'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options6" /></span><span class="blueonly"><a href="' . $php_self . '&amp;budget=1" onmouseover="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_only_show_projects_with_nondisclosed_budgets'] . '</a></span></div>';
		}
		
		// completed listings
		$removeurl = rewrite_url($php_self, 'completed=' . $completed);
		$html .= (isset($ilance->GPC['completed']) AND $ilance->GPC['completed'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_completed" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_completed_listings'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_completed" /></span><span class="blueonly"><a href="' . $php_self . '&amp;completed=1" onmouseover="rollovericon(\'unsel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_completed_listings'] . '</a></span></div>';
		

	}
	else if ($mode == 'experts')
	{
		$images = (isset($ilance->GPC['images']) AND $ilance->GPC['images'] > 0) ? intval($ilance->GPC['images']) : '';
		$isonline = (isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline'] > 0) ? intval($ilance->GPC['isonline']) : '';
		$business = (isset($ilance->GPC['business']) AND $ilance->GPC['business'] > 0) ? intval($ilance->GPC['business']) : '';
		$individual = (isset($ilance->GPC['individual']) AND $ilance->GPC['individual'] > 0) ? intval($ilance->GPC['individual']) : '';
		
		$removeurlall = rewrite_url($php_self, 'images=' . $images);
		$removeurlall = rewrite_url($removeurlall, 'isonline=' . $isonline);
		$removeurlall = rewrite_url($removeurlall, 'business=' . $business);
		$removeurlall = rewrite_url($removeurlall, 'individual=' . $individual);
		$clear_options = $removeurlall;
		
		$clear_options_all = $removeurlall;	
		if (empty($images) AND empty($isonline))
		{
			$clear_options_all = '';
		}
	
		// make sure our php_self string contains a ?
		if (strrchr($php_self, "?") == false)
		{
			// we'll include our master variable which should rewrite our urls nice and friendly
			$php_self = $php_self . '?mode=' . $ilance->GPC['mode'];
		}
		
		// show only businesses
		$removeurl = rewrite_url($php_self, 'business=' . $business);
		$html .= (isset($ilance->GPC['business']) AND $ilance->GPC['business'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_business" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_only_businesses'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_business" /></span><span class="blueonly"><a href="' . $php_self . '&amp;business=1" onmouseover="rollovericon(\'unsel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_only_businesses'] . '</a></span></div>';
		
		// show only individuals
		$removeurl = rewrite_url($php_self, 'individual=' . $individual);
		$html .= (isset($ilance->GPC['individual']) AND $ilance->GPC['individual'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_individual" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_only_individuals'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_individual" /></span><span class="blueonly"><a href="' . $php_self . '&amp;individual=1" onmouseover="rollovericon(\'unsel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_only_individuals'] . '</a></span></div>';
		
		// showing only experts online right now
		$removeurl = rewrite_url($php_self, 'isonline=' . $isonline);
		$html .= (isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_only_show_members_that_are_online_and_logged_in'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;isonline=1" onmouseover="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_only_show_members_that_are_online_and_logged_in'] . '</a></span></div>';
			
		// show with images only
		$removeurl = rewrite_url($php_self, 'images=' . $images);
		$html .= (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_only_profile_logos'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;images=1" onmouseover="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_only_profile_logos'] . '</a></span></div>';
	}	
	
	return $html;
}

function print_currencies($dbtable = '', $fieldname = '', $selected = '', $maxcurrencies = 5, $sqlextra = '')
{
	global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $show, $clear_currencies, $clear_currencies_all;
	
	$html = '';
	
	$sql = $ilance->db->query("
                SELECT $fieldname, c.currency_id, c.currency_abbrev
                FROM " . DB_PREFIX . "$dbtable,
                " . DB_PREFIX . "currency AS c
                WHERE $fieldname = c.currency_id
		$sqlextra
                GROUP by $fieldname
		ORDER BY currency_abbrev ASC
        ", 0, null, __FILE__, __LINE__);
	
	$html .= '<div style="padding-top:2px"></div>';
	
	// make sure our php_self string contains a ?
	$php_self = (strrchr($php_self, "?") == false)
		? $php_self . '?sort=' . intval($ilance->GPC['sort'])
		: $php_self;
		
	$removeurl = rewrite_url(urldecode($php_self), 'cur=' . $selected);
	$removeurlall = rewrite_url(urldecode($php_self), 'cur=' . $selected);
	$clear_currencies_all = (empty($ilance->GPC['cur']))
		? ''
		: $removeurlall;

	// #### all currencies #################################################
	$html .= (empty($selected))
		? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_allcurrencies" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_allcurrencies\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_allcurrencies\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>' . $phrase['_any'] . '</strong></a></span></div>'
		: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_allcurrencies" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_allcurrencies\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_allcurrencies\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_any'] . '</a></span></div>';
		
	unset($removeurl);
	
	// #### handle currency url input ######################################
	$currentlyselected = '';
	$tmp = array();
	if ($selected != '' AND strrchr($selected, ',') == true)
	{
		$temp = explode(',', $selected);
		foreach ($temp AS $key => $value)
		{
			if ($value != '')
			{
				$tmp[] = intval($value);
			}
		}
		unset($temp);
	}
	else if ($selected != '' AND strrchr($selected, ',') == false)
	{
		$tmp[] = intval($selected);
	}
	
	foreach ($tmp AS $key => $value)
	{
		$currentlyselected .= ',' . $value;
	}
	
	// #### loop through all currencies ####################################
	$count = 1;
	while ($crow = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		// #### currently selected #####################################
		if ($selected != '' AND in_array($crow['currency_id'], $tmp))
		{
			$newcur = '';
			if (count($tmp) == 1)
			{
				$removeurl = rewrite_url(urldecode($php_self), 'cur=' . $selected);
			}
			else if (count($tmp) > 1)
			{
				foreach ($tmp AS $key => $value)
				{
					if ($value != $crow['currency_id'])
					{
						$newcur .= $value . ',';
					}
				}
				if ($newcur != '')
				{
					$newcur = substr($newcur, 0, -1);
					$removeurl = rewrite_url(urldecode($php_self), 'cur=' . $selected);
					$removeurl = (strrchr($removeurl, "?") == false)
						? $removeurl . '?cur=' . $newcur
						: $removeurl . '&amp;cur=' . $newcur;
				}
			}
			
			$html .= '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_currencyid_' . $crow['currency_id'] . '" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_currencyid_' . $crow['currency_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_currencyid_' . $crow['currency_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $crow['currency_abbrev'] . '</strong></a></span></div>';
			unset($removeurl);
		}
		// #### unselected #############################################
		else
		{
			$removeurl = rewrite_url(urldecode($php_self), 'cur=' . $selected);
			$removeurl = (strrchr($removeurl, "?") == false)
				? $removeurl . '?sort=' . $ilance->GPC['sort']
				: $removeurl;
				
			$html .= '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_currencyid_' . $crow['currency_id'] . '" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;cur=' . $crow['currency_id'] . $currentlyselected . '" onmouseover="rollovericon(\'unsel_currencyid_' . $crow['currency_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_currencyid_' . $crow['currency_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $crow['currency_abbrev'] . '</a></span></div>';
			unset($removeurl);
		}
		
		$count++;
	}
	unset($tmp);
	
	return $html;
}

function fetch_region_title($region = '')
{
	global $ilance, $phrase;
	
	$region = str_replace('_', ' ', $region);
	$region = ucwords($region);
	
	$sql = $ilance->db->query("
		SELECT region
		FROM " . DB_PREFIX . "locations
		GROUP BY region
		ORDER BY region ASC
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			if ($res['region'] == $region)
			{
				return $res['region'];
			}
		}
	}
	
	return false;
}

function fetch_region_title_by_countryid($countryid = 0)
{
	global $ilance, $phrase;
	
	$sql = $ilance->db->query("
		SELECT region
		FROM " . DB_PREFIX . "locations
		WHERE locationid = '" . intval($countryid) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			return $res['region'];
		}
	}
	
	return false;
}

function fetch_country_ids_by_region($region = '')
{
	global $ilance, $phrase;
	
	$query = (mb_strtolower($region) == 'worldwide' OR empty($region)) ? "" : "WHERE region = '" . $ilance->db->escape_string($region) . "'";
	
	if (empty($query))
	{
		return false;
	}
	
	$ids = '';
	
	$sql = $ilance->db->query("
		SELECT locationid
		FROM " . DB_PREFIX . "locations
		$query
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$ids .= $res['locationid'] . ',';
		}
	}
	
	$ids = (!empty($ids) AND strrchr($ids, ',')) ? substr($ids, 0, -1) : $ids;
	
	return $ids;
}

/*
* Function to print the bid range pulldown menu.
*
* @param       string         selected option value (if applicable)
* @param       string         fieldname
* @param       string         element id (id="")
* @param       string         display type (pulldown or links) (default pulldown)
*
* @return      string         Returns HTML representation of the pulldown or links menu
*/
function print_bid_range_pulldown($selected = '', $fieldname = 'bidrange', $id = '', $displaytype = 'pulldown')
{
        global $ilance, $ilconfig, $phrase, $php_self;
        
	$html = '';
	if ($displaytype == 'pulldown')
	{
		$html .= '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
		$html .= (empty($selected)) ? '<option value="-1" selected="selected">' : '<option value="-1">';
		$html .= $phrase['_1_or_more_bids'] . '</option><option value="1"';
		$html .= (isset($selected) AND $selected == '1') ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_less_than_10_upper'] . '</option><option value="2"';
		$html .= (isset($selected) AND $selected == '2') ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_between_10_and_20_upper'] . '</option><option value="3"';
		$html .= (isset($selected) AND $selected == '3') ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_more_than_20_upper'] . '</option></select>';
	}
	else if ($displaytype == 'links')
	{
		$html .= '<div style="padding-top:4px"></div>';
		
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$bidrange = isset($ilance->GPC['bidrange']) AND !empty($ilance->GPC['bidrange']) ? intval($ilance->GPC['bidrange']) : '-1';
		
		// any number of bids
		$removeurl = rewrite_url($php_self, 'bidrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= ($selected == '-1' OR empty($ilance->GPC['bidrange']))
			? '<div style="padding-bottom:4px" class="gray"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_bidrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>' . $phrase['_any_number_of_bids_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_any_number_of_bids_upper'] . '</a></span></div>';
			
		// less than 10 bids
		$removeurl = rewrite_url($php_self, 'bidrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_less_than_10_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange1" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;bidrange=1" onmouseover="rollovericon(\'unsel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_less_than_10_upper'] . '</a></span></div>';
			
		// between 10 and 20 bids
		$removeurl = rewrite_url($php_self, 'bidrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '2')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_between_10_and_20_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;bidrange=2" onmouseover="rollovericon(\'unsel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_between_10_and_20_upper'] . '</a></span></div>';
		
		// more than 20 bids
		$removeurl = rewrite_url($php_self, 'bidrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '3')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_more_than_20_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange3" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;bidrange=3" onmouseover="rollovericon(\'unsel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_more_than_20_upper'] . '</a></span></div>';
			
		unset($removeurl, $bidrange);
	}

        return $html;
}

/*
* Function to print the award range pulldown menu.
*
* @param       string         selected option value (if applicable)
* @param       string         fieldname
*
* @return      string         Returns HTML representation of the pulldown menu
*/
function print_award_range_pulldown($selected = '', $fieldname = 'projectrange', $id = '', $displaytype = 'pulldown')
{
        global $ilance, $ilconfig, $phrase, $php_self;
        
	if ($displaytype == 'pulldown')
	{
		$html = '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
		$html .= (empty($selected)) ? '<option value="-1" selected="selected">' . $phrase['_any'] . '</option>' : '<option value="-1">' . $phrase['_any'] . '</option>';
		$html .= '<option value="1"';
		$html .= (isset($selected) AND $selected == '1') ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_less_than_10_upper'] . '</option>';
		$html .= '<option value="2"';
		$html .= (isset($selected) AND $selected == '2') ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_between_10_and_20_upper'] . '</option>';
		$html .= '<option value="3"';
		$html .= (isset($selected) AND $selected == '3') ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_more_than_20_upper'] . '</option></select>';	
	}
	else if ($displaytype == 'links')
	{
		$html = '';
		
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$projectrange = isset($ilance->GPC['projectrange']) AND !empty($ilance->GPC['projectrange']) ? intval($ilance->GPC['projectrange']) : '-1';
		
		// any number of bids
		$removeurl = rewrite_url($php_self, 'projectrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= ($selected == '-1' OR empty($ilance->GPC['projectrange']))
			? '<div style="padding-bottom:4px" class="gray"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_projectrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_projectrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_projectrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>' . $phrase['_any'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_projectrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_projectrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_projectrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_any'] . '</a></span></div>';
			
		// less than 10 bids
		$removeurl = rewrite_url($php_self, 'projectrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['projectrange']) AND $ilance->GPC['projectrange'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_projectrange1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_projectrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_projectrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_less_than_10_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_projectrange1" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;projectrange=1" onmouseover="rollovericon(\'unsel_projectrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_projectrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_less_than_10_upper'] . '</a></span></div>';
			
		// between 10 and 20 bids
		$removeurl = rewrite_url($php_self, 'projectrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['projectrange']) AND $ilance->GPC['projectrange'] == '2')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_projectrange2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_projectrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_projectrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_between_10_and_20_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_projectrange2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;projectrange=2" onmouseover="rollovericon(\'unsel_projectrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_projectrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_between_10_and_20_upper'] . '</a></span></div>';
		
		// more than 20 bids
		$removeurl = rewrite_url($php_self, 'projectrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['projectrange']) AND $ilance->GPC['projectrange'] == '3')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_projectrange3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_projectrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_projectrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_more_than_20_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_projectrange3" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;projectrange=3" onmouseover="rollovericon(\'unsel_projectrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_projectrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_more_than_20_upper'] . '</a></span></div>';
			
		unset($removeurl, $bidrange);
	}
        
        return $html;
}

/*
* Function to print the rating range pulldown menu.
*
* @param       string         selected option value (if applicable)
* @param       string         fieldname
*
* @return      string         Returns HTML representation of the pulldown menu
*/
function print_rating_range_pulldown($selected = '', $fieldname = 'rating', $id = 'rating', $displaytype = 'pulldown')
{
        global $ilance, $ilconfig, $phrase, $php_self;
        
	if ($displaytype == 'pulldown')
	{
		$html = '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
		$html .= empty($selected) ? '<option value="0" selected="selected">' . $phrase['_all_ratings_upper'] . '</option>' : '<option value="0">' . $phrase['_all_ratings_upper'] . '</option>';
		$html .= '<option value="5"';
		$html .= (isset($selected) AND $selected == 5) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_five_stars_upper'] . '</option>';
		$html .= '<option value="4"';
		$html .= (isset($selected) AND $selected == 4) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_at_least_four_stars_upper'] . '</option>';
		$html .= '<option value="3"';
		$html .= (isset($selected) AND $selected == 3) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_at_least_three_stars_upper'] . '</option>';
		$html .= '<option value="2"';
		$html .= (isset($selected) AND $selected == 2) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_at_least_two_stars_upper'] . '</option>';
		$html .= '<option value="1"';
		$html .= (isset($selected) AND $selected == 1) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_one_star_upper'] . '</option></select>';
	}
	else if ($displaytype == 'links')
	{
		$html = '';
		
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$rating = isset($ilance->GPC['rating']) AND !empty($ilance->GPC['rating']) ? intval($ilance->GPC['rating']) : '0';
		
		// any ratings
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= ($selected == '0' OR empty($ilance->GPC['rating']))
			? '<div style="padding-bottom:4px" class="gray"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_ratingrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_ratingrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>' . $phrase['_all_ratings_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_ratingrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_all_ratings_upper'] . '</a></span></div>';
			
		// at least 1 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_one_star_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange1" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=1" onmouseover="rollovericon(\'unsel_ratingrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_one_star_upper'] . '</a></span></div>';
			
		// at least 2 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '2')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_at_least_two_stars_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=2" onmouseover="rollovericon(\'unsel_ratingrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_at_least_two_stars_upper'] . '</a></span></div>';
		
		// at least 3 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '3')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_at_least_three_stars_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange3" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=3" onmouseover="rollovericon(\'unsel_ratingrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_at_least_three_stars_upper'] . '</a></span></div>';
	
		// at least 4 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '4')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_at_least_four_stars_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange4" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=4" onmouseover="rollovericon(\'unsel_ratingrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_at_least_four_stars_upper'] . '</a></span></div>';
		
		// at least 5 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '5')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_five_stars_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange5" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=5" onmouseover="rollovericon(\'unsel_ratingrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_five_stars_upper'] . '</a></span></div>';
			
		unset($removeurl, $rating);
	}
        
        return $html;
}

function print_feedback_range_pulldown($selected = '', $fieldname = 'feedback', $id = 'feedback', $displaytype = 'pulldown')
{
        global $ilance, $ilconfig, $phrase, $php_self;
        
	if ($displaytype == 'pulldown')
	{
		$html = '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
		$html .= empty($selected) ? '<option value="0" selected="selected">' . $phrase['_all'] . '</option>' : '<option value="0">' . $phrase['_all'] . '</option>';
		$html .= '<option value="5"';
		$html .= (isset($selected) AND $selected == 5) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_above_95_positive'] . '</option>';
		$html .= '<option value="4"';
		$html .= (isset($selected) AND $selected == 4) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_above_90_positive'] . '</option>';
		$html .= '<option value="3"';
		$html .= (isset($selected) AND $selected == 3) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_above_85_positive'] . '</option>';
		$html .= '<option value="2"';
		$html .= (isset($selected) AND $selected == 2) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_above_75_positive'] . '</option>';
		$html .= '<option value="1"';
		$html .= (isset($selected) AND $selected == 1) ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_above_50_positive'] . '</option></select>';
	}
	else if ($displaytype == 'links')
	{
		$html = '';
		
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$feedback = isset($ilance->GPC['feedback']) AND !empty($ilance->GPC['feedback']) ? intval($ilance->GPC['feedback']) : '0';
		
		// any feedback rating
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= ($selected == '0' OR empty($ilance->GPC['feedback']))
			? '<div style="padding-bottom:4px" class="gray"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_feedbackrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_feedbackrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>' . $phrase['_any'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_feedbackrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_any'] . '</a></span></div>';
			
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_above_50_positive'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange1" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=1" onmouseover="rollovericon(\'unsel_feedbackrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_above_50_positive'] . '</a></span></div>';
		
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '2')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_above_75_positive'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=2" onmouseover="rollovericon(\'unsel_feedbackrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_above_75_positive'] . '</a></span></div>';
			
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '3')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_above_85_positive'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange3" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=3" onmouseover="rollovericon(\'unsel_feedbackrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_above_85_positive'] . '</a></span></div>';
		
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '4')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_above_90_positive'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange4" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=4" onmouseover="rollovericon(\'unsel_feedbackrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_above_90_positive'] . '</a></span></div>';
		
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '5')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_above_95_positive'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange5" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=5" onmouseover="rollovericon(\'unsel_feedbackrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_above_95_positive'] . '</a></span></div>';
			
		unset($removeurl, $rating);
	}
        
        return $html;
}

/*
* Function for fetching the state date / end date SQL condition for the search system.
*
* @param       integer        filter that is selected (-1 = any date), 1 = 1 hour, 2 = 2 hours, etc.
* @param       string         MySQL function to use (DATEADD, DATESUB), etc
* @param       string         field name in the database table to use
* @param       string         operator (>, <, =, etc)
*
* @return      string         Valid SQL condition code to include in main SQL code to parse
*/
function fetch_startend_sql($endstart_filter, $mysqlfunction, $field, $operator)
{
	global $ilance, $myapi;
        
        $sql = '';
	switch ($endstart_filter)
	{
		case '-1':
                {
                        // any date
                        $sql = "";
                        break;
                }	    
		case '1':
                {
                        // 1 hour
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"01:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '2':
                {
                        // 2 hours
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"02:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '3':
                {
                        // 3 hours
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"03:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '4':
                {
                        // 4 hours
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"04:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '5':
                {
                        // 5 hours
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"05:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '6':
                {
                        // 12 hours
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"12:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '7':
                {
                        // 24 hours
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"24:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '8':
                {
                        // 2 days
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 2 DAY) ";
                        break;
                }	    
		case '9':
                {
                        // 3 days
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 3 DAY) ";
                        break;
                }	    
		case '10':
                {
                        // 4 days
                	$sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 4 DAY) ";
                	break;
                }	    
		case '11':
                {
                        // 5 days
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 5 DAY) ";
                        break;
                }	    
		case '12':
                {
                        // 6 days
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 6 DAY) ";
                        break;
                }	    
		case '13':
                {
                        // 7 days
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 7 DAY) ";
                        break;
                }	    
		case '14':
                {
                        // 2 weeks
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 14 DAY) ";
                        break;
                }	    
		case '15':
                {
                        // 1 month
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 1 MONTH) ";
                        break;
                }
                case '16':
                {
                        // 2 months
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 2 MONTH) ";
                        break;
                }
                case '17':
                {
                        // 3 months
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 3 MONTH) ";
                        break;
                }
                case '18':
                {
                        // 6 months
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 6 MONTH) ";
                        break;
                }
                case '19':
                {
                        // 1 year
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 1 YEAR) ";
                        break;
                }
	}
        
	return $sql;
}

/*
* Function for fetching the state date / end date phrase.
*
* @param       integer        filter that is selected (-1 = any date), 1 = 1 hour, 2 = 2 hours, etc.
*
* @return      string         HTML representation of the question title
*/
function fetch_startend_phrase($endstart_filter)
{
	global $ilance, $myapi, $phrase;
        
	switch ($endstart_filter)
	{
		case '-1':
		$sql = $phrase['_any_date'];
		break;
	    
		case '1':
		$sql = '1 ' . $phrase['_hour'];
		break;
	    
		case '2':
		$sql = '2 ' . $phrase['_hours'];
		break;
	    
		case '3':
		$sql = '3 ' . $phrase['_hours'];
		break;
	    
		case '4':
		$sql = '4 ' . $phrase['_hours'];
		break;
	    
		case '5':
		$sql = '5 ' . $phrase['_hours'];
		break;
	    
		case '6':
		$sql = '12 ' . $phrase['_hours'];
		break;
	    
		case '7':
		$sql = '24 ' . $phrase['_hours'];
		break;
	    
		case '8':
		$sql = '2 ' . $phrase['_days'];
		break;
	    
		case '9':
		$sql = '3 ' . $phrase['_days'];
		break;
	    
		case '10':
		$sql = '4 ' . $phrase['_days'];
		break;
	    
		case '11':
		$sql = '5 ' . $phrase['_days'];
		break;
	    
		case '12':
		$sql = '6 ' . $phrase['_days'];
		break;
	    
		case '13':
		$sql = '7 ' . $phrase['_days'];
		break;
	    
		case '14':
		$sql = '2 ' . $phrase['_weeks'];
		break;
	    
		case '15':
		$sql = '1 ' . $phrase['_month'];
		break;
	}
        
	return $sql;
}


/*
* Function to update default search options for a particular user who is registered and logged in.  This function will also update the existing session of the
* logged in user so it's realtime.
*
* @param        integer      user id
* @param        array        array with default search options
*
* @return	nothing
*/
function update_default_searchoptions($userid = 0, $defaultoptions = '')
{
        global $ilance;
        
        if (isset($userid) AND $userid > 0)
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET searchoptions = '" . $ilance->db->escape_string($defaultoptions) . "'
                        WHERE user_id = '" . intval($userid) . "'
                ");
        }
        
        $_SESSION['ilancedata']['user']['searchoptions'] = $defaultoptions;
}

/*
* Function to update default search options for all guests and visitors connecting to the marketplace
*
* @param        integer      user id
* @param        array        array with default search options
*
* @return	nothing
*/
function update_default_searchoptions_guests($defaultoptions = '')
{
        global $ilance;
        
        if (isset($defaultoptions))
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '" . $ilance->db->escape_string($defaultoptions) . "'
                        WHERE name = 'searchdefaultcolumns'
                ");
        }
}

/*
* Function to update default search options for all members in the system
*
* @param        array        array with default search options
*
* @return	nothing
*/
function update_default_searchoptions_users($defaultoptions = '')
{
        global $ilance;
        
        if (isset($defaultoptions))
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET searchoptions = '" . $ilance->db->escape_string($defaultoptions) . "'
                ");
        }
}

/*
* Function to print the per-page search option logic
*
* @param        string        css class to use
*
* @return	string        Returns HTML per page pulldown menu
*/
function print_perpage_searchoption($class = '')
{
        global $phrase;
        
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $pptemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $perpagevalue = (int)$pptemp['perpage'];

                $perpage = '
                <select name="perpage" style="font-family: verdana" class="' . $class . '">
                <optgroup label="' . $phrase['_list'] . '">
                <option value="5" ' . (($perpagevalue == 5) ? 'selected="selected"' : '') . '>5</option>
                <option value="10" ' . (($perpagevalue == 10) ? 'selected="selected"' : '') . '>10</option>
                <option value="25" ' . (($perpagevalue == 25) ? 'selected="selected"' : '') . '>25</option>
                <option value="50" ' . (($perpagevalue == 50) ? 'selected="selected"' : '') . '>50</option>
                <option value="100" ' . (($perpagevalue == 100) ? 'selected="selected"' : '') . '>100</option>
                <option value="200" ' . (($perpagevalue == 200) ? 'selected="selected"' : '') . '>200</option>
                </optgroup>
                <optgroup label="' . $phrase['_gallery'] . '">
                <option value="6" ' . (($perpagevalue == 6) ? 'selected="selected"' : '') . '>6</option>
                <option value="9" ' . (($perpagevalue == 9) ? 'selected="selected"' : '') . '>9</option>
                <option value="12" ' . (($perpagevalue == 12) ? 'selected="selected"' : '') . '>12</option>
                <option value="24" ' . (($perpagevalue == 24) ? 'selected="selected"' : '') . '>24</option>
                <option value="48" ' . (($perpagevalue == 48) ? 'selected="selected"' : '') . '>48</option>
                <option value="96" ' . (($perpagevalue == 96) ? 'selected="selected"' : '') . '>96</option>
                <option value="192" ' . (($perpagevalue == 192) ? 'selected="selected"' : '') . '>192</option>
                </optgroup>
                </select>';
        }
        else
        {
                $perpage = '
                <select name="perpage" style="font-family: verdana">
                <option value="5">5</option>
                <option value="10" selected="selected">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="200">200</option>
                </select>';
        }
        
        return $perpage;
}

/*
* Function to print the per-page search option logic
*
* @param        string        css class to use
*
* @return	string        Returns HTML per page pulldown menu
*/
function print_colsperrow_searchoption($class = '')
{
        global $phrase;
        
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $pptemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $colsperrowvalue = isset($pptemp['colsperrow']) ? (int)$pptemp['colsperrow'] : '3';

                $colsperrow = '
                <select name="colsperrow" style="font-family: verdana" class="' . $class . '">
                <optgroup label="' . $phrase['_gallery'] . '">
		<option value="2" ' . (($colsperrowvalue == 2) ? 'selected="selected"' : '') . '>2</option>
		<option value="3" ' . (($colsperrowvalue == 3) ? 'selected="selected"' : '') . '>3</option>
		<option value="4" ' . (($colsperrowvalue == 4) ? 'selected="selected"' : '') . '>4</option>
                <option value="5" ' . (($colsperrowvalue == 5) ? 'selected="selected"' : '') . '>5</option>
                </optgroup>
                </select>';
        }
        else
        {
                $colsperrow = '
                <select name="colsperrow" style="font-family: verdana">
                <option value="2">2</option>
                <option value="3" selected="selected">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                </select>';
        }
        
        return $colsperrow;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_checkbox_status($cbname)
{
        $cb = '';
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $cbtemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($cbtemp[$cbname]))
                {
                        $cb = $cbtemp[$cbname];
                }
                if (isset($cb) AND $cb == 'true')
                {
                        $cb = 'checked="checked"';
                }
                else if (isset($cb) AND $cb == 'false')
                {
                        $cb = '';        
                }
        }
        
        return $cb;        
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_time_static_radiobox_status()
{
        $rb = 'checked="checked"';
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $rbtemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($rbtemp['showtimeas']))
                {
                        $rb = $rbtemp['showtimeas'];
                        if (isset($rb) AND $rb == 'static')
                        {
                                $rb = 'checked="checked"';
                        }
                }
        }
        
        return $rb;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_time_flash_radiobox_status()
{
        $rb = '';
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $rbtemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($rbtemp['showtimeas']))
                {
                        $rb = $rbtemp['showtimeas'];
                }
                if (isset($rb) AND $rb == 'flash')
                {
                        $rb = 'checked="checked"';
                }
                else
                {
                        $rb = '';
                }
        }
        
        return $rb;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_list_gallery_radiobox_status()
{
        $rb = '';
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $rbtemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($rbtemp['list']))
                {
                        $rb = $rbtemp['list'];
                }
                if (isset($rb) AND $rb == 'gallery')
                {
                        $rb = 'checked="checked"';
                }
                else
                {
                        $rb = '';
                }
        }
        
        return $rb;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_list_list_radiobox_status()
{
        $rb = 'checked="checked"';
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $rbtemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($rbtemp['list']))
                {
                        $rb = $rbtemp['list'];
                        if (isset($rb) AND $rb == 'list')
                        {
                                $rb = 'checked="checked"';
                        }
                }
        }
        
        return $rb;
}

/*
* ...
*
* @param       
*
* @return      
*/
function sortable_array_handler($mode = 'listings')
{
        global $ilance, $ilconfig, $show;
        
        // #### defaults #######################################################
        $array = array(
                // time_ending_soonest
                '01' => array(
                        'field' => 'UNIX_TIMESTAMP(p.date_end)',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // time_newly_listed
                '02' => array(
                        'field' => 'UNIX_TIMESTAMP(p.date_end)',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // price_lowest_first
                '11' => array(
                        'field' => 'p.currentprice',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // price_highest_first
                '12' => array(
                        'field' => '`p`.`currentprice`',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // bids_sort_up
				// murugan changes on feb18 for coin sort
                '21' => array(
                        'field' => 'p.project_id',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // bids_sort_down
                '22' => array(
                        'field' => 'p.project_id',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // category
				//karthik apr23
                '31' => array(
                        'field' => 'p.Orderno',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // category
				// murugan changes here for categort sort on feb 22
				//karthik apr23
                '32' => array(
                         'field' => 'p.Orderno',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // feedback_lowest_first
                '41' => array(
                                'field' => 'p.cid',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                // feedback_highest_first
                '42' => array(
                        'field' => 'u.feedback',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // rated_lowest_first
                '51' => array(
                        'field' => 'u.rating',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // rated_highest_first
                '52' => array(
                        'field' => 'u.rating',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // expert_sort_up
                '61' => array(
                        'field' => 'u.username',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // expert_sort_down
                '62' => array(
                        'field' => 'u.username',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // city_sort_up
                '71' => array(
                        'field' => 'u.city',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // city_sort_down
                '72' => array(
                        'field' => 'u.city',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // country_sort_up
                '81' => array(
                        'field' => 'u.country',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // country_sort_down
                '82' => array(
                        'field' => 'u.country',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // zip_sort_up
                '91' => array(
                        'field' => 'u.zip_code',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // zip_sort_down
                '92' => array(
                        'field' => 'u.zip_code',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // earnings_lowest_first
                '101' => array(
                        'field' => 'u.income_reported',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // earnings_highest_first
                '102' => array(
                        'field' => 'u.income_reported',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // awards_lowest_first
                '111' => array(
                        'field' => 'u.serviceawards',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // awards_highest_first
                '112' => array(
                        'field' => 'u.serviceawards',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // distance
                '121' => array (
                        'field' => 'distance',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                '122' => array(
                        'field' => 'distance',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
				 '333' => array(
                                'field' => 'p.project_title',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
						 '335' => array(
                                'field' => 'p.project_title',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
        );
        
        if ($mode == 'listings')
        {
                $array = array(
                        // time_ending_soonest
                        '01' => array(
                                'field' => 'UNIX_TIMESTAMP(p.date_end)',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // time_newly_listed
                        '02' => array(
                                'field' => 'UNIX_TIMESTAMP(p.date_end)',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // price_lowest_first
                        '11' => array(
                                'field' => '`p`.`currentprice`',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // price_highest_first
                        '12' => array(
                                'field' => '`p`.`currentprice`',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // bids_sort_up
                        '21' => array(
                                'field' => 'p.bids',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // bids_sort_down
                        '22' => array(
                                'field' => 'p.bids',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // category
						//karthik apr23
                        '31' => array(
                                 'field' => 'p.Orderno',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // category
						//karthik apr23
                        '32' => array(
                                 'field' => 'p.Orderno',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // feedback_lowest_first
						// murugan changes on feb 22 for sort order
                        '41' => array(
                                'field' => 'p.cid',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // feedback_highest_first
                        '42' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // rated_lowest_first
                        '51' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // rated_highest_first
                        '52' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // expert_sort_up
                        '61' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // expert_sort_down
                        '62' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // city_sort_up
                        '71' => array(
                                'field' => 'p.city',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // city_sort_down
                        '72' => array(
                                'field' => 'p.city',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // country_sort_up
                        '81' => array(
                                'field' => 'p.countryid',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // country_sort_down
                        '82' => array(
                                'field' => 'p.countryid',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // zip_sort_up
                        '91' => array(
                                'field' => 'p.zipcode',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // zip_sort_down
                        '92' => array(
                                'field' => 'p.zipcode',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // earnings_lowest_first
                        '101' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // earnings_highest_first
                        '102' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // awards_lowest_first
                        '111' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // awards_highest_first
                        '112' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // distance
                        '121' => array (
                                'field' => 'distance',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        '122' => array(
                                'field' => 'distance',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
						 '333' => array(
                                'field' => 'p.project_title',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
						 '335' => array(
                                'field' => 'p.project_title',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                );        
        }
        else if ($mode == 'experts')
        {
                $array = array(
                        // time_ending_soonest
                        '01' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // time_newly_listed
                        '02' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // price_lowest_first
                        '11' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // price_highest_first
                        '12' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // bids_sort_up
                        '21' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // bids_sort_down
                        '22' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                         // category
						//karthik apr23
                        '31' => array(
                                 'field' => 'p.Orderno',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // category
                        '32' => array(
                                 'field' => 'p.Orderno',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // feedback_lowest_first
                        '41' => array(
                                'field' => 'u.feedback',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // feedback_highest_first
                        '42' => array(
                                'field' => 'u.feedback',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // rated_lowest_first
                        '51' => array(
                                'field' => 'u.rating',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // rated_highest_first
                        '52' => array(
                                'field' => 'u.rating',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // expert_sort_up
                        '61' => array(
                                'field' => 'u.username',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // expert_sort_down
                        '62' => array(
                                'field' => 'u.username',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // city_sort_up
                        '71' => array(
                                'field' => 'u.city',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // city_sort_down
                        '72' => array(
                                'field' => 'u.city',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // country_sort_up
                        '81' => array(
                                'field' => 'u.country',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // country_sort_down
                        '82' => array(
                                'field' => 'u.country',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // zip_sort_up
                        '91' => array(
                                'field' => 'u.zip_code',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // zip_sort_down
                        '92' => array(
                                'field' => 'u.zip_code',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // earnings_lowest_first
                        '101' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // earnings_highest_first
                        '102' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // awards_lowest_first
                        '111' => array(
                                'field' => 'u.serviceawards',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // awards_highest_first
                        '112' => array(
                                'field' => 'u.serviceawards',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // distance
                        '121' => array (
                                'field' => 'distance',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        '122' => array(
                                'field' => 'distance',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                );
        }
        
        
	// #### $show['radiussearch'] is generated from search.php
	if ($ilconfig['globalserver_enabledistanceradius'] == false AND isset($show['radiussearch']) AND $show['radiussearch'])
        {
                if ($mode == 'listings')
                {
                        $array['121'] = array(
                                 'field' => 'p.date_end',
                                 'sort' => 'ASC',
                                 'extra' => ''
                        );
                        
                        $array['122'] = array(
                                 'field' => 'p.date_end',
                                 'sort' => 'DESC',
                                 'extra' => ''
                        );
                }
                else if ($mode == 'experts')
                {
                        $array['121'] = array(
                                 'field' => 'u.income_reported',
                                 'sort' => 'ASC',
                                 'extra' => ''
                        );
                        
                        $array['122'] = array(
                                 'field' => 'u.income_reported',
                                 'sort' => 'DESC',
                                 'extra' => ''
                        );        
                }
        }
        
        return $array;
}

/*
* ...
*
* @param       
*
* @return      
*/
function fetch_sort_options($mode = 'service')
{
        global $ilance, $ilconfig;
        
        if ($mode == 'experts')
        {
                $sortoptions = array(
                        '41' => '_feedback_lowest_first',
                        '42' => '_feedback_highest_first',
                        '51' => '_rated_lowest_first',
                        '52' => '_rated_highest_first',
                        '61' => '_expert_sort_up',
                        '62' => '_expert_sort_down',
                        '71' => '_city_sort_up',
                        '72' => '_city_sort_down',
                        '81' => '_country_sort_up',
                        '82' => '_country_sort_down',
                        '91' => '_zip_sort_up',
                        '92' => '_zip_sort_down',
                        '101' => '_earnings_lowest_first',
                        '102' => '_earnings_highest_first',
                        '111' => '_awards_lowest_first',
                        '112' => '_awards_highest_first'
                );
                
                if ($ilconfig['globalserver_enabledistanceradius'])
                {
                        $sortoptions['121'] = '_distance_closest_first';
                        $sortoptions['122'] = '_distance_furthest_first';
                }
        }
        else if ($mode == 'service')
        {
                $sortoptions = array(
                        '01' => '_time_ending_soonest',
                        '02' => '_time_newly_listed',
                        '21' => '_bids_sort_up',
                        '22' => '_bids_sort_down',
                        // '71' => '_city_sort_up',
                        // '72' => '_city_sort_down',
                        // '81' => '_country_sort_up',
                        // '82' => '_country_sort_down',
                        // '91' => '_zip_sort_up',
                        // '92' => '_zip_sort_down',
                        '31' => '_coin_catalog_17922011',
						'32' => '_coin_catalog_20111792',
                );
                
                if ($ilconfig['globalserver_enabledistanceradius'])
                {
                        $sortoptions['121'] = '_distance_closest_first';
                        $sortoptions['122'] = '_distance_furthest_first';
                }
        }
        else if ($mode == 'product')
        {
		///Remove the city,country and zip up and down options this is client requirements kannan
		// coin sort up is working as per project cid
                $sortoptions = array(
                        '01' => '_time_ending_soonest',
                        '02' => '_time_newly_listed',
                        '11' => '_price_lowest_first',
                        '12' => '_price_highest_first',
						'31' => '_coin_catalog_17922011',
						'32' => '_coin_catalog_20111792',
                );
                        /*,
                       '31' => '_coin_sort_up',
                        '41' => '_coin_sort_down''71' => '_city_sort_up',
                        '72' => '_city_sort_down',
                        '81' => '_country_sort_up',
                        '82' => '_country_sort_down',
                        '91' => '_zip_sort_up',
                        '92' => '_zip_sort_down',
                        '31' => '_coin_the_catalog'*/
                
                if ($ilconfig['globalserver_enabledistanceradius'])
                {
                        $sortoptions['121'] = '_distance_closest_first';
                        $sortoptions['122'] = '_distance_furthest_first';
                }
        }
        
        ($apihook = $ilance->api('print_sort_options_end')) ? eval($apihook) : false;
        
        return $sortoptions;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_sort_pulldown($selected = '', $fieldname = 'sort', $mode = 'service')
{
        global $phrase, $show;
        
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $temp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                
                $options = array();
                $options = fetch_sort_options($mode);
                
                $html = '<select name="' . $fieldname . '" style="font-family: verdana" onchange="srch.submit()">';
                foreach ($options AS $key => $value)
                {
                        if (isset($selected) AND $selected == $key)
                        {                                
                                $html .= '<option value="' . $key . '" selected="selected">' . $phrase[$options[$key]] . '</option>';
                        }
                        else
                        {
                                if (isset($temp['sort']) AND $temp['sort'] == $key)
                                {
                                        $html .= '<option value="' . $key . '" selected="selected">' . $phrase[$options[$key]] . '</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . $key . '">' . $phrase[$options[$key]] . '</option>';
                                }
                        }
                }
                $html .= '</select>';
        }
        else
        {
                $options = array();
                $options = fetch_sort_options($mode);
                
                $html = '<select name="' . $fieldname . '" style="font-family: verdana">';
                foreach ($options AS $key => $value)
                {
                        if (isset($selected) AND $selected == $key)
                        {
                                $html .= '<option value="' . $key . '" selected="selected">' . $phrase["$value"] . '</option>';
                        }
                        else
                        {
                                $html .= '<option value="' . $key . '">' . $phrase["$value"] . '</option>';
                        }
                }
                $html .= '</select>';
        }
        
        return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function fetch_perpage()
{
        global $ilance, $ilconfig;
        
        // set the default per page limit
        $perpage = $ilconfig['globalfilters_maxrowsdisplay'];
        
        // check if we're a user logged with existing search parameters setup
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                if (empty($ilance->GPC['pp']))
                {
                        // user hasn't specified a url base per page value .. use default from existing search setup
                        $pptemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                        $perpage = (int)$pptemp['perpage'];
                }
                else
                {
                        // user is requesting a specific per page limit
                        $perpage = intval($ilance->GPC['pp']);
                        if ($perpage == 0 OR $perpage < 0)
                        {
                                // if user get's fancy with pp url value we'll use the safest option available.. the default!
                                $perpage = $ilconfig['globalfilters_maxrowsdisplay'];
                        }
                }
        }
        else
        {
                if (!empty($ilance->GPC['pp']) AND $ilance->GPC['pp'] > 0)
                {
                        // user is requesting a specific per page limit
                        $perpage = intval($ilance->GPC['pp']);
                        if ($perpage == 0 OR $perpage < 0)
                        {
                                // if user get's fancy with pp url value we'll use the safest option available.. the default!
                                $perpage = $ilconfig['globalfilters_maxrowsdisplay'];
                        }
                }
        }
        
        if ($perpage < 0)
        {
                $perpage = $ilconfig['globalfilters_maxrowsdisplay'];
        }
        
        return $perpage;
}

/*
* Function to return the opposite value of the perpage result limit when switching between list view and gallery view.  This function
* will prevent "blocks" in the search results (when viewing gallery mode) from being blank and will fill up with all results available.
*
* @param        string       list viewing type currently selected (list/gallery)
* @param        integer      actual per page value being used
*
* @return       integer      Returns integer with opposite per page value
*/
function fetch_proper_perpage($listview = '')
{
        global $ilance;
        
        $pp = fetch_perpage();
        
        $array = array();        
        if ($listview == 'gallery')
        {
                $array = array(
                        '1' => '6',
                        '2' => '6',
                        '3' => '6',
                        '4' => '6',
                        '5' => '6',
                        '6' => '6',
                        '10' => '12',
                        '12' => '12',
                        '24' => '24',
                        '25' => '24',
                        '48' => '48',
                        '50' => '48',
                        '96' => '96',
                        '100' => '96',
                        '192' => '192',
                        '200' => '192'
                );
        }
        else if ($listview == 'list')
        {
                $array = array(
                        '1' => '5',
                        '2' => '5',
                        '3' => '5',
                        '4' => '5',
                        '5' => '5',
                        '6' => '5',
                        '10' => '10',
                        '12' => '10',
                        '24' => '25',
                        '25' => '25',
                        '48' => '50',
                        '50' => '50',
                        '96' => '100',
                        '100' => '100',
                        '192' => '200',
                        '200' => '200'
                );
        }
        
        return $array["$pp"];
}

/*
* Function responsible for printing the main search results table within the search system.  This function handles
* all logic for building custom searchable display columns, gallery view, list view and more.
*
* @param        array        search results array
* @param        string       category type (product)
* @param        string       constructed pagnation output
*
* @return      
*/
function print_search_results_table($searchresults = array(), $mode = 'product', $prevnext = '', $n_varname = '', $d_varname = '', $scrpage = '')
{

 
        global $ilance, $ilconfig, $phrase, $show, $textgenre, $ilpage, $php_self_urlencoded;
        
        $ilance->template_columns = construct_object('api.template_columns');
        
        $tdclass = $tdfooterclass = '';
        
         if ($mode == 'product')
        { 
		
		        //new change
		        if($n_varname !='')
				{
				 $tdphrase = 'Welcome '.$n_varname;
				}
				else
				//new change on Dec-04
				{
					if(isset($ilance->GPC['ended']) OR isset($ilance->GPC['completed']))
					{
					$tdphrase = '<h2 style="font-family: Arial,Helvetica,sans-serif;font-size: medium;font-weight: bold; margin: 0;">Coin Auction Archive and Price Guide</h2>';
					}
					else
					{
					$tdphrase = '<h2 style="font-family: Arial,Helvetica,sans-serif;font-size: medium;font-weight: bold; margin: 0;">Coin Auctions and Buy Now Coins</h2>';
					}
				}
        }
       
        
        if($scrpage == 'search_won')
                $scriptpage = 'search_won.php'. print_hidden_fields(true, array('budget','list'), true, '', '', true);
        else        
                $scriptpage = $ilpage['search'] . print_hidden_fields(true, array('budget','list'), true, '', '', true);
        
        // user is overriding his/her list preference for a moment.. 
        if (isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'list')
        {
                $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                $forcepp = fetch_proper_perpage('list');
                $_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $_SESSION['ilancedata']['user']['searchoptions']['list'] = 'list';
                $_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
                $_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
        }
        else if (isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'gallery')
        {
                $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                $forcepp = fetch_proper_perpage('gallery');
                $_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $_SESSION['ilancedata']['user']['searchoptions']['list'] = 'gallery';
                $_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
                $_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
        }
        
        // generate list view or gallery view icons
        $opts = array();
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $opts = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($opts['list']) AND $opts['list'] == 'list')
                {
                        $forcepp = fetch_proper_perpage('gallery');
                        $listviewtype = '<span style="float:right" class="smaller"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /><a href="' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp . '" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                }
                else if (isset($opts['list']) AND $opts['list'] == 'gallery')
                {
                        $forcepp = fetch_proper_perpage('list');
                        $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></span>';
                }
        }
        else
        {
                $opts = fetch_default_searchoptions();
                $opts = unserialize($opts);
                $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                if (!isset($ilance->GPC['list']))
                {
			$opts['list'] = 'list';
                }
                else
                {
			$opts['list'] = $ilance->GPC['list'];
                }
                
                if ($opts['list'] == 'list')
                {
                        $forcepp = fetch_proper_perpage('gallery');
                        $listviewtype = '<span style="float:right" class="smaller"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /> <a href="' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp . '" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                }
                else if ($opts['list'] == 'gallery')
                {
                        $forcepp = fetch_proper_perpage('list');
                        $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></span>';
                }        
        }
        
        // fetch html columns for printing along with the colspan info
        $data = $ilance->template_columns->print_table_head_columns($searchresults, $mode, $opts['list']);

        $tablecolumns = $data['columns'];
        $tablerows = $data['rows'];
        $colspan = $data['colspan'];
                
        // free up some memory
        unset($data);
        
        // #### SEARCH HEADER TABS #############################################
        $html = '';
        
        if ($mode == 'product')
        {
                $blockcss = '';
                $blockclass = 'block-content-yellow';
        }
	
        
	($apihook = $ilance->api('print_search_results_header_tab_condition')) ? eval($apihook) : false;
	
	            //new change
		        if($d_varname !='')
				$new_var_des = '<table><tr><td>'.$d_varname.'</td></tr></table>';
				else
                $new_var_des = '';
				
				if($d_varname !='' || $n_varname !='')
				$list_var_new = '<div class="block' . $blockcss . '-header">' .  $tdphrase . '</div>';
				else
				$list_var_new ='<div class="block' . $blockcss . '-header">' . $listviewtype . $tdphrase . '</div>';
        // #### SEARCH HEADER TABLE ############################################
        $html .= '
        <div class="block-wrapper">
                <div class="block' . $blockcss . '">
                                <div class="block' . $blockcss . '-top">
                                        <div class="block' . $blockcss . '-right">
                                                <div class="block' . $blockcss . '-left"></div>
                                        </div>
                                </div>
                                ' . $list_var_new . '
                                <div class="block' . $blockcss . '-content" style="padding:0px">
								'.$new_var_des.'
                                <table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" dir="' . $ilconfig['template_textdirection'] . '">';
                                
        // parse and print user generated table head columns
        $html .= $tablecolumns;
        
        // parse and print generate user column display data rows
        $html .= $tablerows;
        
        // determine if we need to display " no results found "
        if (isset($show['no_rows_returned']) AND $show['no_rows_returned'])
        {
                $html .= '<tr class="alt1"><td colspan="' . $colspan . '" align="center"><div style="padding-top:8px; padding-bottom:8px">' . $phrase['_no_results_found'] . '</div></td></tr>';
				
		($apihook = $ilance->api('print_search_results_table_no_results_end')) ? eval($apihook) : false;
				
		$html .= '<tr class="alt2_top"><td colspan="' . $colspan . '" align="center">';
		
		$helpsearchurl = ($ilconfig['globalauctionsettings_seourls'])
			? HTTP_SERVER . 'search-help'
			: HTTP_SERVER . $ilpage['search'] . '?cmd=help';
		
                $html .= ((isset($ilconfig['fulltextsearch']) AND $ilconfig['fulltextsearch'])
			  ? '<div align="left"><span style="font-size:13px; font-weight:bold">' . $phrase['_learn_more_about_searching'] . ':</span> <span class="blue"><a href="' . $helpsearchurl . '" rel="nofollow">' . $phrase['_advanced_search_commands'] . '</a></span></div>'
			  : '');
		
                $html .= '</td></tr>';
        }
        
        // print our table footer which includes any pulldown menus and/or selection menu buttons / controls or widgets
        if (isset($show['no_rows_returned']) AND $show['no_rows_returned'] == false)
        {
                $html .= '<tr class="alt2_top"><td colspan="' . $colspan . '" class="' . $tdfooterclass . '"><span style="float:left; padding-top:4px;"><span class="blue" style="font-size:14px"><a href="javascript:void(0)" onclick="inlineCB.check_all(true)" style="text-decoration:underline">' . $phrase['_select_all'] . '</a></span>&nbsp;&nbsp; <span class="blue" style="font-size:14px"><a href="javascript:void(0)" onclick="inlineCB.check_all(false)" style="text-decoration:underline">' . $phrase['_deselect_all'] . '</a></span>&nbsp;&nbsp;<span class="blue" style="font-size:14px"><a href="javascript:void(0)" onclick="inlineCB.check_all(\'invert\')" style="text-decoration:underline">' . $phrase['_invert'] . '</a></span>&nbsp;&nbsp;</span><span style="float:right;">';
                                                
		if (isset($show['no_rows_returned']) AND $show['no_rows_returned'] == false)
		{
			$html .= '<select name="action" style="font-family: verdana"><optgroup label="' . $phrase['_select_action'] . '">
			' .  ($mode != 'experts' ? '<option value="compare">' . $phrase['_compare'] . '</option>' : '')  . '<option value="watchlist"  selected="selected" >' . $phrase['_add_to_watchlist'] . '</option>
			' .  ($mode == 'experts' ? '<option value="invite">' . $phrase['_invite_to_bid'] . '</option>' : '')  . '</optgroup>';
			
			if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
			{
				if ($mode == 'product')
				{
					$html .= '<optgroup label="' . $phrase['_moderation_tools'] . '">
					<option value="delist">' . $phrase['_delist'] . '</option>
					<option value="featured">' . $phrase['_featured'] . '</option>
					<option value="highlight">' . $phrase['_highlight'] . '</option>
					<option value="bold">' . $phrase['_bold'] . '</option>
					<option value="relist">' . $phrase['_autorelist'] . '</option>
					<option value="extend">' . $phrase['_extend_duration'] . '</option>
					<option value="movecategory">' . $phrase['_category_move'] . '</option>
					<option value="email">' . $phrase['_send_email'] . '</option>
					</optgroup>';
				}
			}
			
			$html .= '</select>&nbsp;<input type="submit" class="buttons" value=" ' . $phrase['_go'] . ' " id="inlinebutton" />
			<script type="text/javascript"><!--
			inlineCB = new iL_Inline(\'inlineCB\', \'' . $mode . '\', \'searchform\', \'' . $phrase['_go'] . '\');
			//--></script>';
		}
                                                
                $html .= '</span></td></tr>';
        }
        
        $html .= '<tr><td colspan="' . $colspan . '" class="' . $tdfooterclass . '">' . $prevnext . '</td></tr>';
               
        ($apihook = $ilance->api('print_search_results_table_end')) ? eval($apihook) : false;
        
        $html .= '</table></div><div class="block' . $blockcss . '-footer"><div class="block' . $blockcss . '-right"><div class="block' . $blockcss . '-left"></div></div></div></div></div>';
        
        return $html;
}

//arsath added for search page in staff 05-oct-2010
function print_search_results_table_new($searchresults = array(), $mode = 'service', $prevnext = '')
{
        global $ilance, $ilconfig, $phrase, $show, $textgenre, $ilpage, $php_self_urlencoded;
        
        $ilance->template_columns = construct_object('api.template_columns');
        
        $tdclass = $tdfooterclass = '';
        
        if ($mode == 'service')
        {
                $tdphrase = $phrase['_service_auction_listings'];
        }
        else if ($mode == 'product')
        {
                $tdphrase = $phrase['_items'];
        }
        else if ($mode == 'experts')
        {
                $tdphrase = $phrase['_experts'];
        }
        
        $scriptpage = $ilpage['search'] . print_hidden_fields(true, array('budget','list'), true, '', '', true);
        
        // user is overriding his/her list preference for a moment.. 
        if (isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'list')
        {
                $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                $forcepp = fetch_proper_perpage('list');
                $_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $_SESSION['ilancedata']['user']['searchoptions']['list'] = 'list';
                $_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
                $_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
        }
        else if (isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'gallery')
        {
                $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                $forcepp = fetch_proper_perpage('gallery');
                $_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $_SESSION['ilancedata']['user']['searchoptions']['list'] = 'gallery';
                $_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
                $_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
        }
        
        // generate list view or gallery view icons
        $opts = array();
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $opts = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($opts['list']) AND $opts['list'] == 'list')
                {
                        $forcepp = fetch_proper_perpage('gallery');
                        $listviewtype = '';
                }
                else if (isset($opts['list']) AND $opts['list'] == 'gallery')
                {
                        $forcepp = fetch_proper_perpage('list');
                        $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></span>';
                }
        }
        else
        {
                $opts = fetch_default_searchoptions();
                $opts = unserialize($opts);
                $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                if (!isset($ilance->GPC['list']))
                {
			$opts['list'] = 'list';
                }
                else
                {
			$opts['list'] = $ilance->GPC['list'];
                }
                
                if ($opts['list'] == 'list')
                {
                        $forcepp = fetch_proper_perpage('gallery');
                        $listviewtype = '<span style="float:right" class="smaller"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /> <a href="' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp . '" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                }
                else if ($opts['list'] == 'gallery')
                {
                        $forcepp = fetch_proper_perpage('list');
                        $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></span>';
                }        
        }
        
        // fetch html columns for printing along with the colspan info
        $data = $ilance->template_columns->print_table_head_columns($searchresults, $mode, $opts['list']);

        $tablecolumns = $data['columns'];
        $tablerows = $data['rows'];
        $colspan = $data['colspan'];
                
        // free up some memory
        unset($data);
        
        // #### SEARCH HEADER TABS #############################################
        $html = '';
        
        if ($mode == 'product')
        {
                $blockcss = '';
                $blockclass = 'block-content-yellow';
        }
	else if ($mode == 'service')
	{
		$blockcss = '2';
		$blockclass = 'block2-content-blue';	
	}
	else if ($mode == 'experts')
	{
		$blockcss = '3';
		$blockclass = 'block2-content-gray';	
	}
        
	($apihook = $ilance->api('print_search_results_header_tab_condition')) ? eval($apihook) : false;
	
        // #### SEARCH HEADER TABLE ############################################
        $html .= '
        <div class="block-wrapper">
                <div class="block' . $blockcss . '">
                                <div class="block' . $blockcss . '-top">
                                        <div class="block' . $blockcss . '-right">
                                                <div class="block' . $blockcss . '-left"></div>
                                        </div>
                                </div>
                                <div class="block' . $blockcss . '-header">' . $listviewtype . $tdphrase . '</div>
                                <div class="block' . $blockcss . '-content" style="padding:0px">
                                <table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" dir="' . $ilconfig['template_textdirection'] . '">';
                                
        // parse and print user generated table head columns
        $html .= $tablecolumns;
        
        // parse and print generate user column display data rows
        $html .= $tablerows;
        
        // determine if we need to display " no results found "
        if (isset($show['no_rows_returned']) AND $show['no_rows_returned'])
        {
                $html .= '<tr class="alt1"><td colspan="' . $colspan . '" align="center"><div style="padding-top:8px; padding-bottom:8px">' . $phrase['_no_results_found'] . '</div></td></tr>';
				
		($apihook = $ilance->api('print_search_results_table_no_results_end')) ? eval($apihook) : false;
				
		$html .= '<tr class="alt2_top"><td colspan="' . $colspan . '" align="center">';
		
		$helpsearchurl = ($ilconfig['globalauctionsettings_seourls'])
			? HTTP_SERVER . 'search-help'
			: HTTP_SERVER . $ilpage['search'] . '?cmd=help';
		
                $html .= ((isset($ilconfig['fulltextsearch']) AND $ilconfig['fulltextsearch'])
			  ? '<div align="left"><span style="font-size:13px; font-weight:bold">' . $phrase['_learn_more_about_searching'] . ':</span> <span class="blue"><a href="' . $helpsearchurl . '" rel="nofollow">' . $phrase['_advanced_search_commands'] . '</a></span></div>'
			  : '');
		
                $html .= '</td></tr>';
        }
        
        // print our table footer which includes any pulldown menus and/or selection menu buttons / controls or widgets
        if (isset($show['no_rows_returned']) AND $show['no_rows_returned'] == false AND !empty($_SESSION['ilancedata']['user']['userid']))
        {
                $html .= '';
                                                
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND isset($show['no_rows_returned']) AND $show['no_rows_returned'] == false)
		{
			$html .= '';
			
			if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
			{
				if ($mode == 'service' OR $mode == 'product')
				{
					$html .= '';
				}
			}
			
			$html .= '';
		}
                                                
                $html .= '</span></td></tr>';
        }
        
        $html .= '<tr><td colspan="' . $colspan . '" class="' . $tdfooterclass . '">' . $prevnext . '</td></tr>';
               
        ($apihook = $ilance->api('print_search_results_table_end')) ? eval($apihook) : false;
        
        $html .= '</table></div><div class="block' . $blockcss . '-footer"><div class="block' . $blockcss . '-right"><div class="block' . $blockcss . '-left"></div></div></div></div></div>';
        
        return $html;
}
/*
* Function responsible for printing the list to add & manage watchlist table within the watchlist system.  This function handles
* all logic for building custom watchlist display columns, gallery view, list view and more.
*
* @param        array        watchlist manage array
* @param        string       constructed pagnation output
*
* @return      
*/
function print_watchlist_results_table($searchresults = array(), $mode = '', $prevnext = '', $n_varname = '', $d_varname = '')
{

 
        global $ilance, $ilconfig, $phrase, $show, $textgenre, $ilpage, $php_self_urlencoded;
        
        $ilance->template_columns = construct_object('api.template_columns');
        
        $tdclass = $tdfooterclass = '';
        
       if ($mode == 'product')
        { 
		
		        //new change
		        if($n_varname !='')
				{
				 $tdphrase = 'Welcome '.$n_varname;
				}
				else
				//new change on Dec-04
				{
					if(isset($ilance->GPC['ended']) OR isset($ilance->GPC['completed']))
					{
					$tdphrase = '<h2 style="font-family: Arial,Helvetica,sans-serif;font-size: medium;font-weight: bold; margin: 0;">Coin Auction Archive and Price Guide</h2>';
					}
					else
					{
					$tdphrase = '<h2 style="font-family: Arial,Helvetica,sans-serif;font-size: medium;font-weight: bold; margin: 0;">Coin Auctions and Buy Now Coins</h2>';
					}
				}
        }
        
        $scriptpage = '5204.php' . print_hidden_fields(true, array('budget','list'), true, '', '', true);
        
        // user is overriding his/her list preference for a moment.. 
        if (isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'list')
        {
                $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                $forcepp = fetch_proper_perpage('list');
                $_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $_SESSION['ilancedata']['user']['searchoptions']['list'] = 'list';
                $_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
                $_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
        }
        else if (isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'gallery')
        {
                $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                $forcepp = fetch_proper_perpage('gallery');
                $_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $_SESSION['ilancedata']['user']['searchoptions']['list'] = 'gallery';
                $_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
                $_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
        }
        
        // generate list view or gallery view icons
        $opts = array();
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $opts = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($opts['list']) AND $opts['list'] == 'list')
                {
                        $forcepp = fetch_proper_perpage('gallery');
                        $listviewtype = '<span style="float:right" class="smaller"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /><a href="' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp . '" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                }
                else if (isset($opts['list']) AND $opts['list'] == 'gallery')
                {
                        $forcepp = fetch_proper_perpage('list');
                        $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></span>';
                }
        }
        else
        {
                $opts = fetch_default_searchoptions();
                $opts = unserialize($opts);
                $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                if (!isset($ilance->GPC['list']))
                {
			$opts['list'] = 'list';
                }
                else
                {
			$opts['list'] = $ilance->GPC['list'];
                }
                
                if ($opts['list'] == 'list')
                {
                        $forcepp = fetch_proper_perpage('gallery');
                        $listviewtype = '<span style="float:right" class="smaller"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="' . $phrase['_list_view'] . '" /> <a href="' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp . '" title="' . $phrase['_gallery_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></a></span>';
                }
                else if ($opts['list'] == 'gallery')
                {
                        $forcepp = fetch_proper_perpage('list');
                        $listviewtype = '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="' . $phrase['_list_view'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="' . $phrase['_list_view'] . '" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="' . $phrase['_gallery_view'] . '" /></span>';
                }        
        }
        
        // fetch html columns for printing along with the colspan info
        $data = $ilance->template_columns->print_table_head_columns($searchresults, $mode, $opts['list']);

        $tablecolumns = $data['columns'];
        $tablerows = $data['rows'];
        $colspan = $data['colspan'];
                
        // free up some memory
        unset($data);
        
        // #### SEARCH HEADER TABS #############################################
        $html = '';
        
        if ($mode == 'product')
        {
                $blockcss = '';
                $blockclass = 'block-content-yellow';
        }
	
        
	($apihook = $ilance->api('print_search_results_header_tab_condition')) ? eval($apihook) : false;
	
	            //new change
		        if($d_varname !='')
				$new_var_des = '<table><tr><td>'.$d_varname.'</td></tr></table>';
				else
                $new_var_des = '';
				
				if($d_varname !='' || $n_varname !='')
				$list_var_new = '<div class="block' . $blockcss . '-header">' .  $tdphrase . '</div>';
				else
				$list_var_new ='<div class="block' . $blockcss . '-header">' . $listviewtype . $tdphrase . '</div>';
        // #### SEARCH HEADER TABLE ############################################
        $html .= '
        <div class="block-wrapper">
                <div class="block' . $blockcss . '">
                                <div class="block' . $blockcss . '-top">
                                        <div class="block' . $blockcss . '-right">
                                                <div class="block' . $blockcss . '-left"></div>
                                        </div>
                                </div>
                                ' . $list_var_new . '
                                <div class="block' . $blockcss . '-content" style="padding:0px">
								'.$new_var_des.'
                                <table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" dir="' . $ilconfig['template_textdirection'] . '">';
                                
        // parse and print user generated table head columns
        $html .= $tablecolumns;
        
        // parse and print generate user column display data rows
        $html .= $tablerows;
        
        // determine if we need to display " no results found "
        if (isset($show['no_rows_returned']) AND $show['no_rows_returned'])
        {
                $html .= '<tr class="alt1"><td colspan="' . $colspan . '" align="center"><div style="padding-top:8px; padding-bottom:8px">' . $phrase['_no_results_found'] . '</div></td></tr>';
				
					
		
        }
        
        // print our table footer which includes any pulldown menus and/or selection menu buttons / controls or widgets
        if (isset($show['no_rows_returned']) AND $show['no_rows_returned'] == false)
        {
                $html .= '<tr class="alt2_top"><td colspan="' . $colspan . '" class="' . $tdfooterclass . '"><span style="float:left; padding-top:4px;"><span class="blue" style="font-size:14px"><a href="javascript:void(0)" onclick="inlineCB.check_all(true)" style="text-decoration:underline">' . $phrase['_select_all'] . '</a></span>&nbsp;&nbsp; <span class="blue" style="font-size:14px"><a href="javascript:void(0)" onclick="inlineCB.check_all(false)" style="text-decoration:underline">' . $phrase['_deselect_all'] . '</a></span>&nbsp;&nbsp;<span class="blue" style="font-size:14px"><a href="javascript:void(0)" onclick="inlineCB.check_all(\'invert\')" style="text-decoration:underline">' . $phrase['_invert'] . '</a></span>&nbsp;&nbsp;</span><span style="float:right;">';
                                                
		if (isset($show['no_rows_returned']) AND $show['no_rows_returned'] == false)
		{
			$html .= '<select name="action" style="font-family: verdana"><optgroup label="' . $phrase['_select_action'] . '">
			' .  ($mode != 'experts' ? '<option value="compare">' . $phrase['_compare'] . '</option>' : '')  . '<option value="watchlist"  selected="selected" >' . $phrase['_add_to_watchlist'] . '</option>
			' .  ($mode == 'experts' ? '<option value="invite">' . $phrase['_invite_to_bid'] . '</option>' : '')  . '</optgroup>';
			
			
			
			$html .= '</select>&nbsp;<input type="submit" class="buttons" value=" ' . $phrase['_go'] . ' " id="inlinebutton" />
			<script type="text/javascript"><!--
			inlineCB = new iL_Inline(\'inlineCB\', \'' . $mode . '\', \'searchform\', \'' . $phrase['_go'] . '\');
			//--></script>';
		}
                                                
                $html .= '</span></td></tr>';
        }
        
        $html .= '<tr><td colspan="' . $colspan . '" class="' . $tdfooterclass . '">' . $prevnext . '</td></tr>';
               
        ($apihook = $ilance->api('print_search_results_table_end')) ? eval($apihook) : false;
        
        $html .= '</table></div><div class="block' . $blockcss . '-footer"><div class="block' . $blockcss . '-right"><div class="block' . $blockcss . '-left"></div></div></div></div></div>';
        
        return $html;
}
/*
* Function to print skills used within the provider search results.
*
* @param       integer       user id
* @param       integer       maximum number of skills to display (default 100)
* @param       boolean       no url links (default false)
*
* @return      
*/
function print_skills($userid = 0, $showmaxskills = 100, $nourls = false)
{
        global $ilance, $phrase, $ilpage, $ilconfig;
        
        $html = $phrase['_pending'];
        
        $sql = $ilance->db->query("
                SELECT a.cid, s.title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
                FROM " . DB_PREFIX . "skills_answers a
                LEFT JOIN " . DB_PREFIX . "skills s ON s.cid = a.cid
                WHERE a.user_id = '" . intval($userid) . "'
                ORDER BY a.cid ASC
                LIMIT $showmaxskills
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $html = '';
                
                if (defined('PHP_SELF'))
                {
                        $scriptpage = PHP_SELF;
                }
                else
                {
                        $scriptpage = $ilpage['search'];
                }
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $title = stripslashes($res['title']);
                        if (!empty($ilance->GPC['sid'][$res['cid']]))
                        {
                                $removeurl = rewrite_url($scriptpage, $remove = 'sid[' . $ilance->GPC['sid'][$res['cid']] . ']=true');
                        }
                        else
                        {
                                $removeurl = $scriptpage;
                        }
                        
                        if ($nourls == false)
                        {
                                if (isset($ilance->GPC['sid']) AND !empty($ilance->GPC['sid'][$res['cid']]))
                                {
                                        $html .= '<span style="color:#000"><strong>' . $title . '</strong></span>, ';
                                }
                                else
                                {
                                        $html .= '<a href="' . $removeurl  . '&amp;sid[' . $res['cid'] . ']=true" title="' . $phrase['_show_only_providers_skilled_in'] . ' ' . $title . '" class="gray">' . $title . '</a>, ';
                                }
                        }
                        else
                        {
                                $html .= $title . ', ';
                        }
                                
                }
                if (!empty($html))
                {
                        $html = mb_substr($html, 0, -2);
                }
        }
        
        return $html;
}

/*
* Function to print out the skill title based on a particular category id.
*
* @param       integer         category id
*
* @return      
*/
function print_skill_title($sid = 0)
{
        global $ilance, $phrase;
        
        $html = $ilance->db->fetch_field(DB_PREFIX . "skills", "cid = '" . $sid . "'", "title_" . $_SESSION['ilancedata']['user']['slng']);
         
        return $html;
}

/*
* Function to actually do all the independent searching based on a user entering many keywords for their search pattern.
* Additionally, this function will output a formatted <li></li> result set allowing the user to click those links to refine
* their search.
*
* @param       array         keywords being used (array)
* @param       string        category type (service/product)
* @param       integer       results found     
*
* @return      
*/
function fetch_fewer_keyword_links($keywords = array(), $cattype = '', $limit = 4)
{
        global $ilance, $ilpage, $phrase;
        
        $html = array('html' => '', 'count' => '4');
        if (isset($keywords) AND is_array($keywords))
        {
                // ie: "50 Double Dual Layer Blank Disk"
                // 1. "Double Dual Layer Blank Disk"
                // 2. "50 Double Dual Layer"
                // 3. "Double Dual Layer Blank"
                // 4. "Double Dual Blank Disk"
                
                $html['html'] = '<ul>';
                $num = 0;
                foreach ($keywords as $keyword)
                {
                        if ($num <= $limit)
                        {
                                $html['html'] .= '<li><span><a href="' . $ilpage['search'] .'?mode=' . $cattype . '&amp;q=' . urlencode($keyword) . '"><strong>4 items</strong></a> found for ' . $keyword . ' <b>50</b> <b>Double</b> <b>Dual</b> <b>Layer</b> <strike>Blank</strike> <strike>Disk</strike></li>';
                                //$html['html'] = '<li><span><a href="#" style="text-decoration:none"><strong>1 items</strong></a> found for <b>50</b> <b>Double</b> <b>Dual</b> <b>Layer</b> <strike>Blank</strike> <strike>Disk</strike> </span></li><li><span><a href="#" style="text-decoration:none"><strong>4 items</strong></a> found for <strike>50</strike> <b>Double</b> <b>Dual</b> <b>Layer</b> <b>Blank</b> <strike>Disk</strike> </span></li><li><span><a href="#" style="text-decoration:none"><strong>4 items</strong></a> found for <strike>50</strike> <b>Double</b> <b>Dual</b> <strike>Layer</strike> <b>Blank</b> <b>Disk</b> </span></li>';
                                $num++;
                        }
                        
                }
                $html['html'] .= '</ul>';
                
                /*
                <li><span><a href="#" style="text-decoration:none"><strong>1 items</strong></a> found for <b>50</b> <b>Double</b> <b>Dual</b> <b>Layer</b> <strike>Blank</strike> <strike>Disk</strike> </span></li>
                <li><span><a href="#" style="text-decoration:none"><strong>4 items</strong></a> found for <strike>50</strike> <b>Double</b> <b>Dual</b> <b>Layer</b> <b>Blank</b> <strike>Disk</strike> </span></li>
                <li><span><a href="#" style="text-decoration:none"><strong>4 items</strong></a> found for <strike>50</strike> <b>Double</b> <b>Dual</b> <strike>Layer</strike> <b>Blank</b> <b>Disk</b> </span></li>                 
                */
        }
                
        return $html;
}

/*
* This function will display a few links if the user entered many keywords allowing them to refine their search.
* For example, if a user searches for "50 Double Dual Layer Blank Disk", the search system will attempt to independently
* find other results (with count > 0) based on various mixing and matching of the various keywords.
*
* Example keyword: "50 Double Dual Layer Blank Disk"
*
* 1. "Double Dual Layer Blank Disk"
* 2. "50 Double Dual Layer"
* 3. "Double Dual Layer Blank"
* 4. "Double Dual Blank Disk"
*
* @param       array         keywords being used (array)
* @param       string        category type (service/product)
* @param       integer       results found
*
* @return      
*/
function print_fewer_keywords_search($keywords = array(), $cattype = '', $resultsfound = 0)
{
        global $ilance, $phrase, $show;
        
        $show['showfewerkeywords'] = false;
        
        return; // not ready
        
        $html = '';
        
        // checks if keywords array is not empty, if more than 2 keywords and results equal to zero or results less than 5
        // we will search and find more matches for user based on fewer keywords
        if (isset($keywords) AND is_array($keywords) AND count($keywords) >= 2 AND ($resultsfound == 0 OR $resultsfound < 5))
        {
                $temp = fetch_fewer_keyword_links($keywords, $cattype, $limit = 4);
                
                // html of the links returned from other fewer results found
                $fewerhtml = $temp['html'];
                
                // number of fewer result links found
                $count = $temp['count'];
                
                if ($count > 0)
                {
                        $show['showfewerkeywords'] = true;
                        $html = '<div class="bluehlite"><div><strong>' . $phrase['_get_more_results_with_fewer_keywords'] . '</strong></div>' . $fewerhtml . '</div>';
                }
                else
                {
                        $show['showfewerkeywords'] = false;
                }
                unset($temp);
        }
        
        return $html;
}

function fetch_filtered_searchoptions()
{
        global $ilance, $ilconfig, $phrase;
        
	$filtercolumns = array();
	
        $sql = $ilance->db->query("
                SELECT questionid, question, filtertype
                FROM " . DB_PREFIX . "profile_questions
                WHERE isfilter = '1'
                ORDER BY sort ASC
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $filtercolumns['profile_' . $res['questionid']] = stripslashes($res['question']);
                }
        }
        
        return $filtercolumns;
}

function build_expert_search_exclusion_sql($fieldidentifier = '', $permission = '')
{
        global $ilance, $ilconfig, $phrase;
        
        $html = '';
        
        $sql = $ilance->db->query("
                SELECT user_id
                FROM " . DB_PREFIX . "users
                WHERE status != 'active' AND displayprofile = '0' OR status = 'active' AND displayprofile = '0'
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $excluded = 0;
                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        $ids[] = $res['user_id'];
                }
                
                if (isset($ids) AND count($ids) > 0)
                {
			$html = "AND " . $fieldidentifier . "user_id NOT IN (" . implode(',', $ids) . ") ";
                }
        }
        
        return $html;
}

function build_skills_inclusion_sql($fieldidentifier = '', $keywords = '')
{
        global $ilance, $ilconfig;
        
        if (empty($keywords))
        {
                return '';
        }
        
        $html = '';
        
        $sql = $ilance->db->query("
                SELECT cid
                FROM " . DB_PREFIX . "skills
                WHERE MATCH (title_" . $_SESSION['ilancedata']['user']['slng'] . ") AGAINST ('" . $ilance->db->escape_string($keywords) . "' IN BOOLEAN MODE)
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $ids[] = $res['cid'];
                }
                
                if (isset($ids) AND count($ids) > 0)
                {
                        $html = "AND (FIND_IN_SET(" . $fieldidentifier . "cid, '" . implode(',', $ids) . ",'))";
                }
        }
        
        return $html;
}

function print_did_you_mean($query = '', $mode = 'service', $lang = 'en', $url = 'http://www.google.com/search?hl=#LANGUAGE#&q=#QUERY#&meta=')
{
        global $ilance, $ilpage, $ilconfig, $phrase;
        
        return false;
        
        $result = file_get_contents(str_replace(array('#LANGUAGE#','#QUERY#'), array($lang, urlencode($query)), $url));
        preg_match_all("/class\=spell\>\<b\>\<i\>(.*?)\<\/i\>\<\/b\>/i", $result, $matches);
        
        $correctword = isset($matches[1][0]) ? $matches[1][0] : $query;
        if ($correctword != $query)
        {
                switch ($mode)
                {
                        case 'service':
                        {
                                return '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'trail.gif" border="0" alt="" id="" /> <span class="gray"><strong>' . $phrase['_did_you_mean'] . '</strong> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=service&amp;q=' . handle_input_keywords($correctword) . '"><span style="font-size:13px"><strong><em>' . $correctword . '</em></strong></span>?</a></span></span>';
                                break;
                        }
                        case 'product':
                        {
                                return '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'trail.gif" border="0" alt="" id="" /> <span class="gray"><strong>' . $phrase['_did_you_mean'] . '</strong> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;q=' . handle_input_keywords($correctword) . '"><span style="font-size:13px"><strong><em>' . $correctword . '</em></strong></span>?</a></span></span>';
                                break;
                        }
                        case 'experts':
                        {
                                return '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'trail.gif" border="0" alt="" id="" /> <span class="gray"><strong>' . $phrase['_did_you_mean'] . '</strong> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=experts&amp;q=' . handle_input_keywords($correctword) . '"><span style="font-size:13px"><strong><em>' . $correctword . '</em></strong></span>?</a></span></span>';
                                break;
                        }
                }
                
                ($apihook = $ilance->api('print_did_you_mean_end')) ? eval($apihook) : false;
        }
        
        unset($result, $correctword);
        
        return false;
}

/**
* Function for fetching the answer title for the searchable listing answers logic via urls.
*
* @param       integer        question id
* @param       mixed          answer id (could be integer or string)
* @param       string         category type (service or product)
*
* @return      string         HTML representation of the answer title
*/
function fetch_searchable_answer_title($qid = 0, $aid = '', $cattype = '')
{
        global $ilance, $myapi;
        
        $html = '';
        
        $table = ($cattype == 'service') ? "project_questions" : "product_questions";
        
        $sql = $ilance->db->query("
                SELECT multiplechoice
                FROM " . DB_PREFIX . $table . "
                WHERE questionid = '" . intval($qid) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                if (!empty($res['multiplechoice']))
                {
                        $choices = explode('|', $res['multiplechoice']);
                        $i = 0;
                        foreach ($choices AS $choice)
                        {
                               $i++;
                               if ($i == intval($aid))
                               {
                                        //$html = stripslashes(htmlentities($choice, ENT_QUOTES));
					$html = stripslashes(ilance_htmlentities($choice));
                               }
                        }
                }
                else
                {
                        $html = $aid;                
                }
        }
        
        return $html;
}

/**
* Function for fetching the question title for the searchable listing answers logic via urls.
*
* @param       integer        question id
* @param       string         category type (service or product)
*
* @return      string         HTML representation of the question title
*/
function fetch_searchable_question_title($qid = 0, $cattype = '')
{
        global $ilance, $myapi;
        
        $html = '';
        
        $table = ($cattype == 'service') ? "project_questions" : "product_questions";
        
        $sql = $ilance->db->query("
                SELECT question_" . $_SESSION['ilancedata']['user']['slng'] . " AS question
                FROM " . DB_PREFIX . $table . "
                WHERE questionid = '" . intval($qid) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql);
                $html = stripslashes($res['question']);
        }
        
        return $html;
}

/**
* Function for fetching the SQL conditions within the search system for the searchable listing answers logic via urls.
*
* @param       integer        question id
* @param       mixed          answer id (could be integer or string)
* @param       string         category type
*
* @return      string         Valid SQL code to be executed
*/
function fetch_searchable_sql_condition($qid = 0, $aid = '', $cattype = '')
{
        global $ilance, $myapi;
        
        if ($cattype == 'service')
        {
                $table1 = "project_answers";
                $table2 = "project_questions";
        }
        else if ($cattype == 'product')
        {
                $table1 = "product_answers";
                $table2 = "product_questions";           
        }
        else
        {
                return;
        }
        
        $query = '';
        
        $sql = $ilance->db->query("
                SELECT a.answer, a.project_id, q.multiplechoice
                FROM " . DB_PREFIX . $table1 . " a
                LEFT JOIN " . DB_PREFIX . $table2 . " q ON (a.questionid = q.questionid)
                WHERE a.questionid = '" . intval($qid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        if (isset($res['answer']) AND isset($aid))
                        {
                                if (!empty($res['multiplechoice']))
                                {
                                        if (is_serialized($res['answer']))
                                        {
                                                $answers = unserialize($res['answer']);
                                                $choices = explode('|', $res['multiplechoice']);
						
                                                $i = 1;
                                                foreach ($choices AS $choice)
                                                {
                                                        if (!empty($choice))
                                                        {
                                                                $fixedchoices[$i] = mb_strtolower($choice);
                                                                $i++;
                                                        }
                                                }
						
                                                $choices = $fixedchoices;
						
                                                foreach ($answers AS $answer)
                                                {
                                                        if (!empty($answer))
                                                        {
                                                                $fixedanswers[$i] = mb_strtolower($answer);
                                                                $i++;
                                                        }
                                                }
						
                                                $answers = $fixedanswers;
                                                
                                                if (!empty($choices["$aid"]) AND !empty($answers) AND is_array($answers) AND in_array(ilance_htmlentities($choices["$aid"]), $answers))
                                                {
                                                        $query .= $res['project_id'] . ',';
                                                }
                                        }
                                }
                                else
                                {
                                        if (mb_strtolower($aid) == mb_strtolower($res['answer']))
                                        {
                                                $query .= $res['project_id'] . ',';
                                        }
                                }         
                        }
                }
        }
    
        return $query;
}

//amutha buying format left nav function
function print_buying_formats1($denomination)
{
	global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $show, $clear_listtype_url;
	$html = '';
	
	$auction = (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'] > 0) ? intval($ilance->GPC['auction']) : '';
	$buynow = (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'] > 0) ? intval($ilance->GPC['buynow']) : '';
	$inviteonly = (isset($ilance->GPC['inviteonly']) AND $ilance->GPC['inviteonly'] > 0) ? intval($ilance->GPC['inviteonly']) : '';
	$scheduled = (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled'] > 0) ? intval($ilance->GPC['scheduled']) : '';
	$lub = (isset($ilance->GPC['lub']) AND $ilance->GPC['lub'] > 0 AND $ilconfig['enable_uniquebidding']) ? intval($ilance->GPC['lub']) : '';
	$penny = (isset($ilance->GPC['penny']) AND $ilance->GPC['penny'] > 0) ? intval($ilance->GPC['penny']) : '';
	$ilance->GPC['mode'] ='product';
	$removeurlall = rewrite_url($php_self, 'auction=' . $auction);
	$removeurlall = rewrite_url($removeurlall, 'buynow=' . $buynow);
	$removeurlall = rewrite_url($removeurlall, 'inviteonly=' . $inviteonly);
	$removeurlall = rewrite_url($removeurlall, 'scheduled=' . $scheduled);
	$removeurlall = rewrite_url($removeurlall, 'lub=' . $lub);
	$removeurlall = rewrite_url($removeurlall, 'penny=' . $penny);
	$clear_listtype_url = $removeurlall;
	
	// make sure our php_self string contains a ?
	if (strrchr($php_self, "?") == false)
	{
		// we'll include our master variable which should rewrite our urls nice and friendly
		$php_self = $php_self . '?mode=' . $ilance->GPC['mode'];
	}
	else
	{
	$php_self='denomination.php?denomination='.$denomination.'&amp;mode=' . $ilance->GPC['mode'];
	}
	
	if ($ilance->GPC['mode'] == 'product')
	{
		
		$html .= (isset($ilance->GPC['mode']) AND $ilance->GPC['mode'] == 'product')
			
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_auctiontype0" /></span><span class="blueonly"><a href="' .$php_self . '" onmouseover="rollovericon(\'sel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>' . $phrase['_any'] . '</strong></a></span></div>'
		: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype0" /></span><span class="blueonly"><a href="' . $php_self . '&amp;all=1" onmouseover="rollovericon(\'unsel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_any'] . '</a></span></div>';
		// forward auction
		$removeurl = rewrite_url($php_self, 'auction=' . $auction);
		$html .= (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_forward_auction'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;auction=1" onmouseover="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_forward_auction'] . '</a></span></div>';
			
		$removeurl = rewrite_url($php_self, 'buynow=' . $buynow);
		$html .= (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_buy_now'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype5" /></span><span class="blueonly"><a href="' . $php_self . '&amp;buynow=1" onmouseover="rollovericon(\'unsel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_buy_now'] . '</a></span></div>';
		
		// lowest unique bid events
		if ($ilconfig['enable_uniquebidding'])
		{
			$removeurl = rewrite_url($php_self, 'lub=' . $lub);
			$html .= (isset($ilance->GPC['lub']) AND $ilance->GPC['lub'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_lowest_unique_bid'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype4" /></span><span class="blueonly"><a href="' . $php_self . '&amp;lub=1" onmouseover="rollovericon(\'unsel_auctiontype4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_lowest_unique_bid'] . '</a></span></div>';
		}
		
		/*$removeurl = rewrite_url($php_self, 'penny=' . $penny);
		$html .= (isset($ilance->GPC['penny']) AND $ilance->GPC['penny'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_penny" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_penny\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_penny\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_penny_auctions'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_penny" /></span><span class="blueonly"><a href="' . $php_self . '&amp;penny=1" onmouseover="rollovericon(\'unsel_penny\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_penny\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_penny_auctions'] . '</a></span></div>';*/
				
	}
	else if ($ilance->GPC['mode'] == 'service')
	{
		// reverse auction
		$removeurl = rewrite_url($php_self, 'auction=' . $auction);
		$html .= (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_reverse_auction'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;auction=1" onmouseover="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_reverse_auction'] . '</a></span></div>';
		
		// invite only
		$removeurl = rewrite_url($php_self, 'inviteonly=' . $inviteonly);		
		$html .= (isset($ilance->GPC['inviteonly']) AND $ilance->GPC['inviteonly'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_invite_only'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;inviteonly=1" onmouseover="rollovericon(\'unsel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_invite_only'] . '</a></span></div>';
			
	}
	
	// upcoming scheduled
	$removeurl = rewrite_url($php_self, 'scheduled=' . $scheduled);
	$html .= (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled'] == '1')
		? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_scheduled'] . '</strong></a></span></div>'
		: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype3" /></span><span class="blueonly"><a href="' . $php_self . '&amp;scheduled=1" onmouseover="rollovericon(\'unsel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_scheduled'] . '</a></span></div>';
	unset($php_self,$removeurl);
	return $html;
}

function print_options1($mode = 'product',$denomination)
{
	global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $show, $clear_options, $clear_options_all;
	
	$html = '<div style="padding-top:7px"></div>';

	if ($mode == 'service' OR $mode == 'product')
	{
		$images = (isset($ilance->GPC['images']) AND $ilance->GPC['images'] > 0) ? intval($ilance->GPC['images']) : '';
		$publicboard = (isset($ilance->GPC['publicboard']) AND $ilance->GPC['publicboard'] > 0) ? intval($ilance->GPC['publicboard']) : '';
		$freeshipping = (isset($ilance->GPC['freeshipping']) AND $ilance->GPC['freeshipping'] > 0) ? intval($ilance->GPC['freeshipping']) : '';
		$listedaslots = (isset($ilance->GPC['listedaslots']) AND $ilance->GPC['listedaslots'] > 0) ? intval($ilance->GPC['listedaslots']) : '';
		$escrow = (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'] > 0 AND $ilconfig['escrowsystem_enabled']) ? intval($ilance->GPC['escrow']) : '';
		$budget = (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] > 0) ? intval($ilance->GPC['budget']) : '';
		$donation = (isset($ilance->GPC['donation']) AND $ilance->GPC['donation'] > 0) ? intval($ilance->GPC['donation']) : '';
		$completed = (isset($ilance->GPC['completed']) AND $ilance->GPC['completed'] > 0) ? intval($ilance->GPC['completed']) : '';
		
		$removeurlall = rewrite_url($php_self, 'images=' . $images);
		$removeurlall = rewrite_url($removeurlall, 'publicboard=' . $publicboard);
		$removeurlall = rewrite_url($removeurlall, 'freeshipping=' . $freeshipping);
		$removeurlall = rewrite_url($removeurlall, 'listedaslots=' . $listedaslots);
		$removeurlall = rewrite_url($removeurlall, 'escrow=' . $escrow);
		$removeurlall = rewrite_url($removeurlall, 'budget=' . $budget);
		$removeurlall = rewrite_url($removeurlall, 'donation=' . $donation);
		$removeurlall = rewrite_url($removeurlall, 'completed=' . $completed);
		$clear_options = $removeurlall;
		$clear_options_all = (empty($images) AND empty($publicboard) AND empty($freeshipping) AND empty($listedaslots) AND empty($escrow) AND empty($donation) AND empty($completed))
			? ''
			: $removeurlall;
		$php_self='denomination.php?denomination='.$denomination;

		// make sure our php_self string contains a ?
		if (strrchr($php_self, "?") == false)
		{
			// we'll include our master variable which should rewrite our urls nice and friendly
			$php_self = $php_self . '?mode=' . $ilance->GPC['mode'];
		}
		
		// murugan Changes Jan 13
		// show with message board
		/*$removeurl = rewrite_url($php_self, 'publicboard=' . $publicboard);
		$html .= (isset($ilance->GPC['publicboard']) AND $ilance->GPC['publicboard'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_listings_with_active_public_message_boards'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;publicboard=1" onmouseover="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_listings_with_active_public_message_boards'] . '</a></span></div>';*/
		
		/*if ($ilance->GPC['mode'] == 'product')
		{
			// show with images only
			$removeurl = rewrite_url($php_self, 'images=' . $images);
			$html .= (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_only_with_images'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;images=1" onmouseover="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_only_with_images'] . '</a></span></div>';
			
			// free shipping
			$removeurl = rewrite_url($php_self, 'freeshipping=' . $freeshipping);		
			$html .= (isset($ilance->GPC['freeshipping']) AND $ilance->GPC['freeshipping'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_items_with_free_shipping'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options3" /></span><span class="blueonly"><a href="' . $php_self . '&amp;freeshipping=1" onmouseover="rollovericon(\'unsel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_items_with_free_shipping'] . '</a></span></div>';
			
			// items listed as lots
			$removeurl = rewrite_url($php_self, 'listedaslots=' . $listedaslots);
			$html .= (isset($ilance->GPC['listedaslots']) AND $ilance->GPC['listedaslots'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_items_listed_as_lots'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options4" /></span><span class="blueonly"><a href="' . $php_self . '&amp;listedaslots=1" onmouseover="rollovericon(\'unsel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_items_listed_as_lots'] . '</a></span></div>';
			
			if ($ilconfig['escrowsystem_enabled'])
			{
				// items being sold via escrow
				$removeurl = rewrite_url($php_self, 'escrow=' . $escrow);
				$html .= (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'] == '1')
					? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_items_that_sellers_require_secure_escrow'] . '</strong></a></span></div>'
					: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options5" /></span><span class="blueonly"><a href="' . $php_self . '&amp;escrow=1" onmouseover="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_items_that_sellers_require_secure_escrow'] . '</a></span></div>';
			}
			
			// include nonprofit selling items
			$removeurl = rewrite_url($php_self, 'donation=' . $donation);
			$html .= (isset($ilance->GPC['donation']) AND $ilance->GPC['donation'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options6" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_donation_items'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options6" /></span><span class="blueonly"><a href="' . $php_self . '&amp;donation=1" onmouseover="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_donation_items'] . '</a></span></div>';
				
		}*/
		else if ($ilance->GPC['mode'] == 'service')
		{
			if ($ilconfig['escrowsystem_enabled'])
			{
				// items being sold via escrow
				$removeurl = rewrite_url($php_self, 'escrow=' . $escrow);
				$html .= (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'] == '1')
					? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_projects_that_use_secure_escrow'] . '</strong></a></span></div>'
					: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options5" /></span><span class="blueonly"><a href="' . $php_self . '&amp;escrow=1" onmouseover="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_projects_that_use_secure_escrow'] . '</a></span></div>';
			}
			
			// show with specific budget range
			$removeurl = rewrite_url($php_self, 'budget=' . $budget);
			$html .= (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] == '1')
				? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options6" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_only_show_projects_with_nondisclosed_budgets'] . '</strong></a></span></div>'
				: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options6" /></span><span class="blueonly"><a href="' . $php_self . '&amp;budget=1" onmouseover="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_only_show_projects_with_nondisclosed_budgets'] . '</a></span></div>';
		}
		
		// completed listings
		$removeurl = rewrite_url($php_self, 'completed=' . $completed);
		$html .= (isset($ilance->GPC['completed']) AND $ilance->GPC['completed'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_completed" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_completed_listings'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_completed" /></span><span class="blueonly"><a href="' . $php_self . '&amp;completed=1" onmouseover="rollovericon(\'unsel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_completed_listings'] . '</a></span></div>';
		

	}
	else if ($mode == 'experts')
	{
		$images = (isset($ilance->GPC['images']) AND $ilance->GPC['images'] > 0) ? intval($ilance->GPC['images']) : '';
		$isonline = (isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline'] > 0) ? intval($ilance->GPC['isonline']) : '';
		$business = (isset($ilance->GPC['business']) AND $ilance->GPC['business'] > 0) ? intval($ilance->GPC['business']) : '';
		$individual = (isset($ilance->GPC['individual']) AND $ilance->GPC['individual'] > 0) ? intval($ilance->GPC['individual']) : '';
		
		$removeurlall = rewrite_url($php_self, 'images=' . $images);
		$removeurlall = rewrite_url($removeurlall, 'isonline=' . $isonline);
		$removeurlall = rewrite_url($removeurlall, 'business=' . $business);
		$removeurlall = rewrite_url($removeurlall, 'individual=' . $individual);
		$clear_options = $removeurlall;
		
		$clear_options_all = $removeurlall;	
		if (empty($images) AND empty($isonline))
		{
			$clear_options_all = '';
		}
	
		// make sure our php_self string contains a ?
		if (strrchr($php_self, "?") == false)
		{
			// we'll include our master variable which should rewrite our urls nice and friendly
			$php_self = $php_self . '?mode=' . $ilance->GPC['mode'];
		}
		
		// show only businesses
		$removeurl = rewrite_url($php_self, 'business=' . $business);
		$html .= (isset($ilance->GPC['business']) AND $ilance->GPC['business'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_business" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_only_businesses'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_business" /></span><span class="blueonly"><a href="' . $php_self . '&amp;business=1" onmouseover="rollovericon(\'unsel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_only_businesses'] . '</a></span></div>';
		
		// show only individuals
		$removeurl = rewrite_url($php_self, 'individual=' . $individual);
		$html .= (isset($ilance->GPC['individual']) AND $ilance->GPC['individual'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_individual" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_only_individuals'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_individual" /></span><span class="blueonly"><a href="' . $php_self . '&amp;individual=1" onmouseover="rollovericon(\'unsel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_only_individuals'] . '</a></span></div>';
		
		// showing only experts online right now
		$removeurl = rewrite_url($php_self, 'isonline=' . $isonline);
		$html .= (isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_only_show_members_that_are_online_and_logged_in'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;isonline=1" onmouseover="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_only_show_members_that_are_online_and_logged_in'] . '</a></span></div>';
			
		// show with images only
		$removeurl = rewrite_url($php_self, 'images=' . $images);
		$html .= (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_show_only_profile_logos'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;images=1" onmouseover="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_show_only_profile_logos'] . '</a></span></div>';
	}	
	
	return $html;
}

//amutha bid range left nav function
function print_bid_range_pulldown1($selected = '', $fieldname = 'bidrange', $id = '', $displaytype = 'pulldown',$denomination)
{
        global $ilance, $ilconfig, $phrase, $php_self;
        
	$html = '';
	if ($displaytype == 'pulldown')
	{
		$html .= '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
		$html .= (empty($selected)) ? '<option value="-1" selected="selected">' : '<option value="-1">';
		$html .= $phrase['_any_number_of_bids_upper'] . '</option><option value="1"';
		$html .= (isset($selected) AND $selected == '1') ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_less_than_10_upper'] . '</option><option value="2"';
		$html .= (isset($selected) AND $selected == '2') ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_between_10_and_20_upper'] . '</option><option value="3"';
		$html .= (isset($selected) AND $selected == '3') ? 'selected="selected"' : '';
		$html .= '>' . $phrase['_more_than_20_upper'] . '</option></select>';
	}
	else if ($displaytype == 'links')
	{
		$html .= '<div style="padding-top:4px"></div>';
		
	//strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
if (strrchr($php_self, "?") == false)
	{
		// we'll include our master variable which should rewrite our urls nice and friendly
		$php_self = $php_self . '?mode=' . $ilance->GPC['mode'];
	}
	

$php_self='denomination.php?denomination='.$denomination;


$bidrange = isset($ilance->GPC['bidrange']) AND !empty($ilance->GPC['bidrange']) ? intval($ilance->GPC['bidrange']) : '-1';
		
		// any number of bids
		$html .= ($selected == '-1' OR empty($ilance->GPC['bidrange']))
			? '<div style="padding-bottom:4px" class="gray"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_bidrange" /></span><span class="blueonly"><a href="' .$php_self . '" onmouseover="rollovericon(\'sel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>' . $phrase['_any_number_of_bids_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange" /></span><span class="blueonly"><a href="' . $php_self . '&amp;all=1" onmouseover="rollovericon(\'unsel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_any_number_of_bids_upper'] . '</a></span></div>';
			
		// less than 10 bids
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange1" /></span><span class="blackonly"><a href="' . $php_self . '" onmouseover="rollovericon(\'sel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_less_than_5_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;bidrange=1" onmouseover="rollovericon(\'unsel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_less_than_5_upper'] . '</a></span></div>';
			
		// between 10 and 20 bids
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '2')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange2" /></span><span class="blackonly"><a href="' . $php_self . '" onmouseover="rollovericon(\'sel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_between_5_and_10_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;bidrange=2" onmouseover="rollovericon(\'unsel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_between_5_and_10_upper'] . '</a></span></div>';
		
		// more than 20 bids
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '3')
			? '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange3" /></span><span class="blackonly"><a href="' . $php_self. '" onmouseover="rollovericon(\'sel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $phrase['_more_than_10_upper'] . '</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:left; padding-right:7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange3" /></span><span class="blueonly"><a href="' . $php_self . '&amp;bidrange=3" onmouseover="rollovericon(\'unsel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $phrase['_more_than_10_upper'] . '</a></span></div>';
			
		unset($php_self, $bidrange);
	}

        return $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>