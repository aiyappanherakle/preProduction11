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
* Shipping functions for ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/

/**
* Function to print out a listings pre-defined shipping methods to a specific buyers location.
* 
* This function can be used to show payment methods in a string or used to generate radio boxes based on a buyer shipping selector process
* when multiple shipping services to a buyers location is defined.
*
* @param       integer        listing id
* @param       integer        order quantity (default 1)
* @param       boolean        print radio button logic (default false)
* @param       boolean        return the number of shipping options only (default false)
*
* @return      string         Returns HTML formatted string of ship-to locations available to a specific buyers location
*/
function print_shipping_methods($projectid = 0, $qty = 1, $radiobuttons = false, $countonly = false, $pulldownmenu = false)
{
	global $ilance, $phrase, $ilconfig, $ilpage, $show, $shipperidrow;
	
	$count = $shipperidrow = 0;
	$shipperidrowcount = 1;
	$html = '';

	$countryid = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : 0;
	if ($countryid == 0)
	{
		return false;
	}
	
	if ($pulldownmenu)
	{
		$html .= '<select name="shipperid" style="font-family: verdana">';
	}
	
	$result = $ilance->db->query("
                SELECT p.row, l.location_" . $_SESSION['ilancedata']['user']['slng'] . " AS countrytitle, l.region
                FROM " . DB_PREFIX . "projects_shipping_regions p
                LEFT JOIN " . DB_PREFIX . "locations l ON (p.countryid = l.locationid)
                WHERE p.project_id = '" . intval($projectid) . "'
                    AND p.countryid = '" . $_SESSION['ilancedata']['user']['countryid'] . "'
                ORDER BY p.row ASC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($result) > 0)
        {
		$html .= ($countonly == false AND $radiobuttons AND $pulldownmenu == false)
			? '<div style="padding-top:4px; padding-left:7px"><label for=""><input type="radio" name="shipperid" id="shipperid_' . $count . '" value="" checked="checked" /> ' . $phrase['_i_am_undecided'] . '</label></div>'
			: '';
			
                while ($res = $ilance->db->fetch_array($result, DB_ASSOC))
                {
			$count++;
			if ($countonly == false AND $radiobuttons)
			{
				$html .= fetch_radio_ship_service_row($res['row'], $projectid, $res['countrytitle'], $res['region'], $qty);
			}
			if ($countonly == false AND $radiobuttons == false AND $pulldownmenu)
			{
				$html .= fetch_option_ship_service_row($res['row'], $projectid, $res['countrytitle'], $res['region'], $qty);
			}
			if ($countonly == false AND $radiobuttons == false)
			{
				$shipperidrowcount = $res['row'];
			}
		}
	}
	
	$show['multipleshipservices'] = ($count > 1) ? true : false;
	$show['shipservices'] = ($count > 0) ? true : false;
	if ($count == 1 OR $shipperidrowcount > 0)
	{
		$shipperidrow = $shipperidrowcount;
	}
	
	if ($pulldownmenu)
	{
		$html .= '</select>';
	}
	
	return $countonly ? $count : $html;
}

/**
* Function to print the ajax shipping service rows called via AJAX from the listing page
* 
* This function will fetch the shipping service rows for an ajax related call from an auction listing page
*
* @param       integer        shipping row number
* @param       integer        auction listing id
* @param       string         country title
* $param       string         region title
* @param       integer        quantity
*
* @return      string         Returns HTML formatted string of payment method output or radio button input logic
*/
function fetch_radio_ship_service_row($row = 0, $pid = 0, $countrytitle = '', $region = '', $qty = 1)
{
	global $ilance, $phrase, $ilconfig;
	
	$html = $country = $qtystring = '';
	$currencyid = fetch_auction('currencyid', $pid);
	
	if ($row > 0)
	{
		$result = $ilance->db->query("
			SELECT d.ship_options_$row AS location, d.ship_service_$row AS shipperid, d.ship_fee_$row AS cost, d.freeshipping_$row AS freeshipping, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
			FROM " . DB_PREFIX . "projects_shipping_destinations d
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (d.project_id = s.project_id)
			WHERE d.project_id = '" . intval($pid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			$res = $ilance->db->fetch_array($result, DB_ASSOC);
			
			$country = ($res['location'] == 'worldwide') ? $countrytitle . ' (' . $phrase['_worldwide'] . ')' : "$countrytitle";
			$service = ($res['shipperid'] > 0) ? print_shipping_partner($res['shipperid']) : '';
			
			if ($res['freeshipping'])
			{
				$price = '<strong>' . $phrase['_free'] . '</strong>';
				if ($res['ship_handlingfee'] > 0)
				{
					$price .= '&nbsp;&nbsp;&nbsp;<span style="padding-top:3px" class="smaller gray">+' . $ilance->currency->format($res['ship_handlingfee'], $currencyid) . ' ' . $phrase['_handling_fee'] . '</span>';
				}
			}
			else
			{
				if ($res['ship_method'] == 'flatrate')
				{
					$price = $ilance->currency->format(($res['cost'] + $res['ship_handlingfee']) * $qty, $currencyid);
				}
				else if ($res['ship_method'] == 'calculated')
				{
					$price = '';	
				}
				else
				{
					$price = $phrase['_local_pickup_only'];	
				}
			}
			
			if ($qty > 1 AND $res['freeshipping'] == 0)
			{
				$qtystring = '<div style="padding-top:3px" class="smaller gray">(QTY x ' . $qty . ')</div>';
			}
			
			if (!empty($service))
			{
				$checked = '';
				if (isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] == $res['shipperid'])
				{
					$checked = ' checked="checked"';
				}
				$html .= '<div style="padding-top:4px; padding-left:7px"><label for=""><input type="radio" name="shipperid" id="shipperid_' . $row . '" value="' . $res['shipperid'] . '" ' . $checked . ' /> <span class="black">' . $service . '</span> ' . $phrase['_to'] . ' <span class="black">' . $country . '</span> :<span class="blue">&nbsp;' . $price . '</span></label></div>';
				unset($checked);
			}
		}
	}
	
	return $html;
}

/**
* Function to print an options list for generation of a pulldown menu with buyer shipping choices available to them
* 
* This function will fetch the shipping service rows
*
* @param       integer        shipping row number
* @param       integer        auction listing id
* @param       string         country title
* $param       string         region title
* @param       integer        quantity
*
* @return      string         Returns HTML formatted string of payment method output or radio button input logic
*/
function fetch_option_ship_service_row($row = 0, $pid = 0, $countrytitle = '', $region = '', $qty = 1, $showprice = false)
{
	global $ilance, $phrase, $ilconfig;
	
	$html = $country = $qtystring = '';
	$currencyid = fetch_auction('currencyid', $pid);
	
	if ($row > 0)
	{
		$result = $ilance->db->query("
			SELECT d.ship_options_$row AS location, d.ship_service_$row AS shipperid, d.ship_fee_$row AS cost, d.freeshipping_$row AS freeshipping, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
			FROM " . DB_PREFIX . "projects_shipping_destinations d
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (d.project_id = s.project_id)
			WHERE d.project_id = '" . intval($pid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			$res = $ilance->db->fetch_array($result, DB_ASSOC);
			
			$country = $countrytitle;
			$service = ($res['shipperid'] > 0) ? print_shipping_partner($res['shipperid']) : '';
			
			if ($res['freeshipping'])
			{
				$price = $phrase['_free'];
				if ($res['ship_handlingfee'] > 0)
				{
					$price .= '&nbsp;+' . $ilance->currency->format($res['ship_handlingfee'], $currencyid) . ' ' . $phrase['_handling_fee'];
				}
			}
			else
			{
				if ($res['ship_method'] == 'flatrate')
				{
					$price = $ilance->currency->format(($res['cost'] + $res['ship_handlingfee']) * $qty, $currencyid);
				}
				else if ($res['ship_method'] == 'calculated')
				{
					$price = '';	
				}
				else
				{
					$price = $phrase['_local_pickup_only'];	
				}
			}
			
			if (!empty($service))
			{
				$html .= '<option value="' . $res['shipperid'] . '" />' . (($showprice) ? $price . ' : ' : '') . $service . ' ' . $phrase['_to'] . ' ' . $country . '</option>';
			}
		}
	}
	
	return $html;
}

/**
* Function to print shipping countries pulldown based on a specific auction listing id
* 
* This function 
*
* @param       integer        auction listing id
* @param       boolean        do string output? default false
* @param       boolean        do only regions output? default false
* @param       boolean        do only worldwide? default false
*
* @return      string         Returns HTML formatted string
*/
function print_item_shipping_countries_pulldown($projectid = 0, $string = false, $onlyregions = false, $worldwide = false, $selectedcid = 0)
{
	global $ilance, $phrase, $show;
	
	$html = '';
	if ($string == false)
	{
		//$html = '<select name="showshippingdestinations" id="showshippingdestinations" style="font-family: verdana" onchange="show_listing_shipping_rows()"><option value="">-</option>';
		$html = '<select name="showshippingdestinations" id="showshippingdestinations" style="font-family: verdana"><option value="">-</option>';
	}
	
	$sql = $ilance->db->query("
		SELECT country, countryid, region
		FROM " . DB_PREFIX . "projects_shipping_regions
		WHERE project_id = '" . intval($projectid) . "'
		" . ($onlyregions ? "GROUP BY region" : "") . "
		" . ($string == false ? "GROUP BY country" : "") . "
		ORDER BY country ASC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			if ($string == false)
			{
				$html .= (isset($selectedcid) AND $selectedcid > 0 AND $selectedcid == $res['countryid'])
					? '<option value="' . $res['countryid'] . '" selected="selected">' . handle_input_keywords($res['country']) . '</option>'
					: '<option value="' . $res['countryid'] . '">' . handle_input_keywords($res['country']) . '</option>';
			}
			else
			{
				$html .= ($onlyregions)
					? ucwords(str_replace('_', ' ', $res['region'])) . ', '
					: handle_input_keywords($res['country']) . ', ';
			}
		}
		
		if (!empty($html) AND $string)
		{
			$html = substr($html, 0, -2);
		}
	}
	
	$html .= ($string == false) ? '</select>' : '';
	
	return $html;
}

/**
* Function to print shipping countries string based on a specific auction listing id
* 
* This function 
*
* @param       integer        auction listing id
* @param       boolean        force all countries? default false
*
* @return      string         Returns HTML formatted string of payment method output or radio button input logic
*/
function print_item_shipping_countries_string($projectid = 0, $forceall = false)
{
	global $ilance, $phrase, $show, $ilconfig;
	
	$show['shipsworldwide'] = false;
	
	$html = '';
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "projects_shipping_destinations
		WHERE project_id = '" . intval($projectid) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				if (isset($res['ship_options_' . $i]) AND !empty($res['ship_options_' . $i]))
				{
					switch ($res['ship_options_' . $i])
					{
						case 'domestic':
						{
							$html = print_item_shipping_countries_pulldown($projectid, true, false) . ' ' . $phrase['_only_lower'];
							break;
						}
						case 'worldwide':
						{
							$show['shipsworldwide'] = true;
							if ($forceall == false)
							{
								$html = $phrase['_worldwide'];
							}
							else
							{
								$html = print_item_shipping_countries_pulldown($projectid);
							}
							break;
						}
						case 'custom':
						{
							$html = print_item_shipping_countries_pulldown($projectid, true, true);
							break;
						}
					}
					
				}
			}
		}
	}
	
	return $html;	
}

/**
* Function to print the ajax shipping service rows called via AJAX from the listing page
* 
* This function will fetch the shipping service rows for an ajax related call from an auction listing page
*
* @param       integer        shipping row number
* @param       integer        auction listing id
* @param       string         country title
* $param       string         region title
* @param       integer        quantity
*
* @return      string         Returns HTML formatted string of payment method output or radio button input logic
*/
function fetch_ajax_ship_service_row($row = 0, $pid = 0, $countrytitle = '', $region = '', $qty = 1)
{
	global $ilance, $phrase, $ilconfig;
	
	$html = $country = $qtystring = '';
	$currencyid = fetch_auction('currencyid', $pid);
	
	$delivery = (isset($_SESSION['ilancedata']['user']['country']) AND isset($countrytitle) AND $countrytitle == $_SESSION['ilancedata']['user']['country'])
		? '<div>' . $phrase['_domestic_services_get_to_your_location_fast'] . '</div>'
		: '<div>' . $phrase['_varies_for_items_shipped_from_international_locations'] . '</div>';
	
	if ($row > 0)
	{
		$result = $ilance->db->query("
			SELECT d.ship_options_$row AS location, d.ship_service_$row AS shipperid, d.ship_fee_$row AS cost, d.freeshipping_$row AS freeshipping, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
			FROM " . DB_PREFIX . "projects_shipping_destinations d
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (d.project_id = s.project_id)
			WHERE d.project_id = '" . intval($pid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			$res = $ilance->db->fetch_array($result, DB_ASSOC);
			
			$delivery .= '<div class="smaller gray" style="padding-top:3px">' . $phrase['_seller_ships_within'] . ' <span class="blue" id="ship_shipdays_' . $row . '">' . $res['ship_handlingtime'] . '</span> <span class="blue">' . $phrase['_days_lower'] . '</span> ' . $phrase['_of_cleared_payment'] . '</div>';
			$country = ($res['location'] == 'worldwide') ? $phrase['_worldwide'] . ', ' . $countrytitle : "$countrytitle";
			$service = ($res['shipperid'] > 0) ? print_shipping_partner($res['shipperid']) : '-';
			
			if ($res['freeshipping'])
			{
				$price = '<strong>' . $phrase['_free'] . '</strong>';
				if ($res['ship_handlingfee'] > 0)
				{
					$price .= '&nbsp;&nbsp;&nbsp;<span style="padding-top:3px" class="smaller gray">+' . $ilance->currency->format($res['ship_handlingfee'], $currencyid) . ' ' . $phrase['_handling_fee'] . '</span>';
				}
			}
			else
			{
				if ($res['ship_method'] == 'flatrate')
				{
					$price = $ilance->currency->format(($res['cost'] + $res['ship_handlingfee']) * $qty, $currencyid);
				}
				else if ($res['ship_method'] == 'calculated')
				{
					$price = '<span id="ship_handling_working_' . $row . '" title="Fetching real-time rates..."><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'working.gif" border="0" alt="Fetching real-time rates..." /></span>';	
				}
				else
				{
					$price = $phrase['_local_pickup_only'];	
				}
			}
			
			if ($qty > 1 AND $res['freeshipping'] == 0)
			{
				$qtystring = '<div style="padding-top:3px" class="smaller gray">(QTY x ' . $qty . ')</div>';
			}
			
			$html .= $price . $qtystring . "~~~~$country~~~~$service~~~~$delivery";
		}
	}
	
	return $html;
}

/**
* Function to fetch the lowest possible shipping prices within a haystack of prices
* 
* This function will be used on the search results to display the lowest possible shipping cost to buyers
*
* @param       array          array with multiple shipping costs
*
* @return      string         Returns HTML formatted string of lowest shipping price in the haystack
*/
function fetch_lowest_shipping_cost($prices = array(), $docurrencyformat = false, $pid = 0)
{
	global $ilance;
	
	$currencyid = fetch_auction('currencyid', $pid);
	
	if ($docurrencyformat)
	{
		if (count($prices) > 0)
		{
			return $ilance->currency->format(min($prices), $currencyid);
		}
		else
		{
			return '-';
		}
	}
	else
	{
		if (count($prices) > 0)
		{
			return min($prices);
		}
	}
	
	return false;
}

/**
* Function to fetch and return an array the raw shipping costs by a shipper id for a specific project (including single or multiple qty)
*
* @param       integer        listing id
* @param       integer        shipping service id
* @param       integer        quantity (default 1)
*
* @return      string         Returns php array with $array['total'] and $array['amount'] values
*/
function fetch_ship_cost_by_shipperid($projectid = 0, $shipperid = 0, $qty = 1)
{
	global $ilance, $ilconfig;
	
	$cost = array('amount' => 0, 'total' => 0);
	$fields = '';
	for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
	{
		$fields .= "d.ship_service_$i, d.ship_fee_$i, ";
	}	
	$fields = substr($fields, 0, -2);
	
	$sql = $ilance->db->query("
		SELECT s.ship_handlingfee, $fields
		FROM " . DB_PREFIX . "projects_shipping_destinations d
		LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (d.project_id = s.project_id)
		WHERE d.project_id = '" . intval($projectid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
		{
			if (isset($res['ship_service_' . $i]) AND $shipperid > 0 AND $res['ship_service_' . $i] > 0 AND $res['ship_service_' . $i] == $shipperid)
			{
				$cost = array(
					'amount' => ($res['ship_fee_' . $i] + $res['ship_handlingfee']),
					'total' => (($res['ship_fee_' . $i] + $res['ship_handlingfee']) * $qty),
				);
				break;
			}
		}
	}
	
	return $cost;
}

/**
* Function to to determine if a specific item / listing id can be shipped to a specific country
*
* @param       integer        listing id
* @param       integer        country id
*
* @return      boolean        Returns true or false
*/
function can_item_ship_to_countryid($projectid = 0, $countryid = 0)
{
	global $ilance, $show;
	
	$show['itemcanshiptouser'] = false;
	
	$result = $ilance->db->query("
                SELECT p.row, l.location_" . $_SESSION['ilancedata']['user']['slng'] . " AS countrytitle, l.region
                FROM " . DB_PREFIX . "projects_shipping_regions p
                LEFT JOIN " . DB_PREFIX . "locations l ON (p.countryid = l.locationid)
                WHERE p.project_id = '" . intval($projectid) . "'
                    AND p.countryid = '" . intval($countryid) . "'
                ORDER BY p.row ASC
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($result) > 0)
        {
		$show['itemcanshiptouser'] = true;
                return true;
        }
	
	return false;
}

/**
* Function to fetch and print order id radio combo buttons for a listing payment selection process
*
* @param       integer        listing id
* @param       integer        buyer id
* @param       integer        order id (if applicable)
*
* @return      string         Returns HTML formatted string of radio including html markup
*/
function print_orderid_methods($pid = 0, $buyerid = 0, $orderid = 0)
{
	global $ilance, $show, $phrase, $headinclude, $onsubmit, $orderidradiocount;
	
	$show['multipleorders'] = false;
	$html = '';
	$count = 0;
	$orderidradiocount = 0;
	$currencyid = fetch_auction('currencyid', $pid);
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "buynow_orders
		WHERE project_id = '" . intval($pid) . "'
			AND buyer_id = '" . intval($buyerid) . "'
	");
	if ($ilance->db->num_rows($sql) > 1)
	{
		//$html .= '<div style="padding-top:4px; padding-left:9px"><label for=""><input type="radio" name="orderid" value="" checked="checked" /> <span class="black">I would like to place a new order with the same seller</span></label></div>';
	}
	
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "buynow_orders
		WHERE project_id = '" . intval($pid) . "'
			AND buyer_id = '" . intval($buyerid) . "'
			AND paiddate = '0000-00-00 00:00:00'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$orderidradiocount = 1;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$checked = (($orderid > 0 AND $orderid == $res['orderid']) ? 'checked="checked"' : '');
			$html .= '<div style="padding-top:4px; padding-left:9px"><label for=""><input type="radio" name="orderid" id="orderid_' . $orderidradiocount . '" value="' . $res['orderid'] . '" ' . $checked . ' onclick="toggle_show(\'methods_wrapper\')" /> <span class="black">' . $phrase['_order'] . ' ID <span class="blue">#' . $res['orderid'] . '</span> ' . $phrase['_to'] . ' <span>' . handle_input_keywords($res['ship_location']) . '</span> - ' . $ilance->currency->format($res['amount'], $currencyid) . '</span></label></div>';
			$count++;
			$orderidradiocount++;
		}
	}
	
	if ($count > 1)
	{
		$show['multipleorders'] = true;
	}
	
	return $html;
}

function fetch_listing_shipping_regions($pid = 0)
{
	global $ilance;
	
	$array = array();
	
	$sql = $ilance->db->query("
		SELECT region, row
		FROM " . DB_PREFIX . "projects_shipping_regions
		WHERE project_id = '" . intval($pid) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$array[$res['row']][] = $res['region'];
		}
	}
	
	$array = $ilance->template->remove_duplicate_template_variables($array);
	
	return $array;
}

/**
* Function to fetch region title/name by a country id
* 
* This function 
*
* @param       integer        country id
*
* @return      string         Returns HTML formatted string
*/
function fetch_region_by_countryid($countryid = 0, $doformatting = true)
{
	global $ilance;
	
	$html = '';
	
	$sql = $ilance->db->query("
		SELECT region
		FROM " . DB_PREFIX . "locations
		WHERE locationid = '" . intval($countryid) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		
		$html = $res['region'];
		if ($doformatting)
		{
			$html = str_replace(' ', '_', $html);
			$html = strtolower($html);
		}
	}
	
	return $html;
}

/**
* Function to 
* 
* This function 
*
* @param       string         region
*
* @return      string         Returns HTML formatted string
*/
function fetch_countries_by_region_array($region = '')
{
	global $ilance;
	
	$query = "";
	if ($region != 'worldwide')
	{
		$returnarray = array();
		$fixedregion = str_replace('_', ' ', $region);
		$fixedregion = ucwords($fixedregion);
		$query = "WHERE region = '" . $ilance->db->escape_string($fixedregion) . "'";
	}
	
	$sql = $ilance->db->query("
		SELECT locationid, location_" . $_SESSION['ilancedata']['user']['slng'] . " AS location, cc, region
		FROM " . DB_PREFIX . "locations
		$query
		ORDER BY locationid ASC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$array['countryid'] = $res['locationid'];
			$array['country'] = $res['location'];
			$array['region'] = strtolower(str_replace(' ', '_', $res['region']));
			$array['iso'] = $res['cc'];
			$returnarray[] = $array;
		}
	}
	
	return $returnarray;
}

/**
* Function to print the payment method title for an auction
* 
* This function will print the payment method title
*
* @param       integer        auction listing id
*
* @return      string         Returns HTML formatted string
*/
function print_payment_method_title($projectid = 0)
{
	global $ilance, $phrase, $ilconfig, $ilpage;
	
	$html = '';
	$sql = $ilance->db->query("
		SELECT filter_escrow, filter_gateway, filter_offline, paymethod, paymethodoptions, paymethodoptionsemail
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($projectid) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		if ($res['filter_escrow'])
		{
			$html = 'escrow';
		}
		
		if ($res['filter_gateway'])
		{
			if (is_serialized($res['paymethodoptions']))
			{
				$paymethodoptions = unserialize($res['paymethodoptions']);

				foreach ($paymethodoptions AS $paymethodoption => $value)
				{
					$html = 'gateway_' . str_replace(' ', '_', mb_strtolower($paymethodoption));
				}
			}
			else if (!empty($res['paymethod']))
			{
				$html = 'gateway_' . str_replace(' ', '_', mb_strtolower($res['paymethod']));
			}	
		}
		
		if ($res['filter_offline'])
		{
			if (is_serialized($res['paymethod']))
			{
				$paymethods = unserialize($res['paymethod']);
				
				foreach ($paymethods AS $paymethod)
				{
					$html = 'offline_' . str_replace(' ', '_', mb_strtolower($paymethod));
				}
			}
			else
			{
				$html = 'offline_' . str_replace(' ', '_', mb_strtolower($res['paymethod']));
			}
		}
	}
	
	return $html;	
}

/**
* Function to fetch the email address associated with a selected payment gateway selected by the seller
* 
* This function will print a email address if applicable.
*
* @param       integer        auction listing id
* @param       string         selected payment gateway
*
* @return      string         Returns HTML formatted string
*/
function fetch_payment_method_email($projectid = 0, $selectedgateway = '')
{
	$paymethodoptionsemail = fetch_auction('paymethodoptionsemail', $projectid);
	if (is_serialized($paymethodoptionsemail))
	{
		$options = unserialize($paymethodoptionsemail);
		if (is_array($options))
		{
			foreach ($options AS $gateway => $email)
			{
				if (isset($gateway) AND $gateway == $selectedgateway)
				{
					if (!empty($email))
					{
						return trim($email);
					}
				}
			}
		}
	}
	
	return false;
}

/**
* Function to print a fixed payment method recognized by ILance
* 
* This function print fixed payment method
*
* @param       string         selected payment method
* @param       boolean        show the pay method type in the string?
*
* @return      string         Returns HTML formatted string
*/
function print_fixed_payment_method($selected = '', $showtype = true)
{
	global $phrase, $show;
	
	$show['depositlink'] = true;
	$html = '';
	if (strchr($selected, 'gateway'))
	{
		$show['depositlink'] = false;
		$bits = explode('gateway_', $selected);
		$html = isset($phrase["_$bits[1]"]) ? $phrase["_$bits[1]"] : ucfirst($bits[1]);
		$html .= $showtype ? ' (' . $phrase['_direct_payment'] . ')' : '';
	}
	else if (strchr($selected, 'offline'))
	{
		$show['depositlink'] = false;
		$bits = explode('offline_', $selected);
		$html = isset($phrase["_$bits[1]"]) ? $phrase["_$bits[1]"] : ucfirst($bits[1]);
		$html .= $showtype ? ' (' . $phrase['_offline_payment'] . ')' : '';
	}
	else if (strchr($selected, 'escrow') OR $selected == 'escrow')
	{
		$show['depositlink'] = true;
		$html = SITE_NAME . ' ' . $phrase['_escrow_service'];
		$html .= $showtype ? ' (' . $phrase['_direct_payment'] . ')' : '';
	}
	
	return $html;
}

/**
* Function to print out a listings pre-defined payment methods.
* 
* This function can be used to show payment methods in a string or used to generate checkboxes based on a buyer payment selector process.
*
* @param       integer        listing id
* @param       boolean        print radio button logic (default false)
* @param       boolean        return the number of payment options only (default false)
*
* @return      string         Returns HTML formatted string of payment method output or radio button input logic
*/
function print_payment_methods($projectid = 0, $radiobuttons = false, $countonly = false)
{
	global $ilance, $phrase, $ilconfig, $ilpage;
	
	$count = 0;
	$html = '';
	
	$sql = $ilance->db->query("
		SELECT filter_escrow, filter_gateway, filter_offline, paymethod, paymethodoptions, paymethodoptionsemail
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($projectid) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		
		$html .= ($radiobuttons)
			? '<div style="padding-top:4px; padding-left:10px"><label for=""><input type="radio" name="paymethod" id="paymethod_' . $count . '" value="" checked="checked" /> ' . $phrase['_i_am_undecided'] . '</label></div>'
			: '';
		
		if ($res['filter_escrow'])
		{
			$count++;
			if ($countonly == false)
			{
				if ($radiobuttons)
				{
					//$html .= '<div style="font-weight:bold; margin-bottom:4px">' . $phrase['_payment_methods_i_can_pay_the_seller_directly_online_to_complete'] . ':</div>';
					$checked = '';
					$tch = 'escrow';
					if (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == $tch)
					{
						$checked = ' checked="checked"';
					}
					$html .= '<div style="padding-top:4px; padding-left:10px"><label for=""><input type="radio" name="paymethod" id="paymethod_' . $count . '" value="escrow" ' . $checked . ' /> ' . SITE_NAME . ' ' . $phrase['_escrow_service'] . '</label></div>';
					unset($checked, $tch);
				}
				else
				{
					$html .= SITE_NAME . ' ' . $phrase['_escrow_service'] . ', ';
				}
			}
		}
		if ($res['filter_gateway'])
		{
			if ($countonly == false)
			{
				if ($res['filter_escrow'] == '0' AND $radiobuttons)
				{
					//$html .= '<div style="font-weight:bold; margin-bottom:4px">' . $phrase['_payment_methods_i_can_pay_the_seller_directly_online_to_complete'] . ':</div>';
				}
			}

			if (is_serialized($res['paymethodoptions']))
			{
				$paymethodoptions = unserialize($res['paymethodoptions']);

				foreach ($paymethodoptions AS $paymethodoption => $value)
				{
					$count++;
					if ($countonly == false)
					{
						if ($radiobuttons)
						{
							$tch = 'gateway_' . str_replace(' ', '_', mb_strtolower($paymethodoption));
							$checked = '';
							if (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == $tch)
							{
								$checked = ' checked="checked"';
							}
							$html .= '<div style="padding-top:4px; padding-left:10px"><label for=""><input type="radio" name="paymethod" id="paymethod_' . $count . '" value="gateway_' . str_replace(' ', '_', mb_strtolower($paymethodoption)) . '" ' . $checked . ' /> ' . (isset($phrase["_$paymethodoption"]) ? $phrase["_$paymethodoption"] : '') . '</label></div>';
							unset($checked, $tch);
						}
						else
						{
							$html .= isset($phrase["_$paymethodoption"]) ? $phrase["_$paymethodoption"] . ', ' : '';
						}
					}
				}
			}
			else if (!empty($res['paymethod']))
			{
				$count++;
				if ($countonly == false)
				{
					if ($radiobuttons)
					{
						$tch = 'gateway_' . str_replace(' ', '_', mb_strtolower($res['paymethod']));
						$checked = '';
						if (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == $tch)
						{
							$checked = ' checked="checked"';
						}
						$html .= '<div style="padding-top:4px; padding-left:10px"><label for=""><input type="radio" name="paymethod" id="paymethod_' . $count . '" value="gateway_' . str_replace(' ', '_', mb_strtolower($res['paymethod'])) . '" ' . $checked . ' /> ' . $res['paymethod'] . '</label></div>';
						unset($checked, $tch);
					}
					else
					{
						$html .= $res['paymethod'];
					}
				}
			}	
		}
		
		if ($res['filter_offline'])
		{
			if (is_serialized($res['paymethod']))
			{
				$paymethods = unserialize($res['paymethod']);
				
				if ($countonly == false)
				{
					if ($radiobuttons)
					{
						//$html .= '<div style="font-weight:bold; margin-bottom:4px">' . $phrase['_payment_methods_i_need_to_contact_the_seller_directly_to_complete'] . ':</div>';
					}
				}
				
				if (isset($paymethods) AND is_array($paymethods))
				{
					foreach ($paymethods AS $paymethod)
					{
						$count++;
						if ($countonly == false)
						{
							if ($radiobuttons)
							{
								$checked = '';
								$tch = 'offline_' . str_replace(' ', '_', mb_strtolower($paymethod));
								if (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == $tch)
								{
									$checked = ' checked="checked"';
								}
								$html .= '<div style="padding-top:4px; padding-left:10px"><label for=""><input type="radio" name="paymethod" id="paymethod_' . $count . '" value="offline_' . str_replace(' ', '_', mb_strtolower($paymethod)) . '" ' . $checked . ' /> ' . $paymethod . '</label></div>';
								unset($checked, $tch);
							}
							else
							{
								$html .= $paymethod . ', ';
							}
						}
					}
				}
			}
			else
			{
				$count++;
				if ($countonly == false)
				{
					if ($radiobuttons)
					{
						$checked = '';
						$tch = 'offline_' . str_replace(' ', '_', mb_strtolower($res['paymethod']));
						if (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == $tch)
						{
							$checked = ' checked="checked"';
						}
						$html .= '<div style="padding-top:4px"><label for=""><input type="radio" name="paymethod" id="paymethod_' . $count . '" value="offline_' . str_replace(' ', '_', mb_strtolower($res['paymethod'])) . '" ' . $checked . ' /> ' . $res['paymethod'] . '</label></div>';
						unset($checked, $tch);
					}
					else
					{
						$html .= $res['paymethod'];
					}
				}
			}
		}
	}
	
	if (!empty($html) AND $radiobuttons == false)
	{
		$html = substr($html, 0, -2);
	}
	
	return $countonly ? $count : $html;
}

/**
* Function to mark a listing as un-shipped (by the seller themselves)
*
* @return      nothing
*/
function mark_listing_as_unshipped($pid = 0, $bid = 0, $sellerid = 0, $buyerid = 0, $mode = '')
{
        global $ilance, $phrase, $ilconfig;
        
	if ($mode == 'buynow')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "buynow_orders
			SET sellermarkedasshipped = '0', sellermarkedasshippeddate = '0000-00-00 00:00:00'
			WHERE project_id = '" . intval($pid) . "'
				AND buyer_id = '" . intval($buyerid) . "'
				AND owner_id = '" . intval($sellerid) . "'
				AND orderid = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	
	else if ($mode == 'escrow')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects_escrow
			SET sellermarkedasshipped = '0', sellermarkedasshippeddate = '0000-00-00 00:00:00'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND escrow_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	
	else if ($mode == 'uniquebid')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects_uniquebids
			SET sellermarkedasshipped = '0', sellermarkedasshippeddate = '0000-00-00 00:00:00'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND uid = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}

	else if ($mode == 'bid')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_bids
			SET sellermarkedasshipped = '0', sellermarkedasshippeddate = '0000-00-00 00:00:00'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND bid_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_realtimebids
			SET sellermarkedasshipped = '0', sellermarkedasshippeddate = '0000-00-00 00:00:00'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND bid_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
}

/**
* Function to mark a listing as shipped (by the seller themselves)
*
* @return      nothing
*/
function mark_listing_as_shipped($pid = 0, $bid = 0, $sellerid = 0, $buyerid = 0, $mode = '')
{
        global $ilance, $phrase, $ilconfig;
        
	if ($mode == 'buynow')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "buynow_orders
			SET sellermarkedasshipped = '1',
			sellermarkedasshippeddate = '" . DATETIME24H . "'
			WHERE project_id = '" . intval($pid) . "'
				AND buyer_id = '" . intval($buyerid) . "'
				AND owner_id = '" . intval($sellerid) . "'
				AND orderid = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	
	else if ($mode == 'escrow')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects_escrow
			SET sellermarkedasshipped = '1',
			sellermarkedasshippeddate = '" . DATETIME24H . "'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND escrow_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_bids
			SET sellermarkedasshipped = '1', sellermarkedasshippeddate = '" . DATETIME24H . "'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND bid_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_realtimebids
			SET sellermarkedasshipped = '1', sellermarkedasshippeddate = '" . DATETIME24H . "'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND bid_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	
	else if ($mode == 'uniquebid')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects_uniquebids
			SET sellermarkedasshipped = '1',
			sellermarkedasshippeddate = '" . DATETIME24H . "'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND uid = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	
	else if ($mode == 'bid')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_bids
			SET sellermarkedasshipped = '1', sellermarkedasshippeddate = '" . DATETIME24H . "'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND bid_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_realtimebids
			SET sellermarkedasshipped = '1', sellermarkedasshippeddate = '" . DATETIME24H . "'
			WHERE project_id = '" . intval($pid) . "'
				AND user_id = '" . intval($buyerid) . "'
				AND project_user_id = '" . intval($sellerid) . "'
				AND bid_id = '" . intval($bid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
}

/**
* Function to mark a outside direct pay listing as paid (seller invokes this himself)
*
* @return      nothing
*/
function mark_listing_as_paid($pid = 0, $bid = 0, $winnermarkedaspaidmethod = '')
{
        global $ilance, $phrase, $ilconfig;
        
	$extra = !empty($winnermarkedaspaidmethod) ? "winnermarkedaspaidmethod = '" . $ilance->db->escape_string($winnermarkedaspaidmethod) . "'," : '';
	
        $ilance->db->query("
		UPDATE " . DB_PREFIX . "project_bids
		SET winnermarkedaspaid = '1',
		$extra
		winnermarkedaspaiddate = '" . DATETIME24H . "'
		WHERE project_id = '" . intval($pid) . "'
			AND bid_id = '" . intval($bid) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "project_realtimebids
		SET winnermarkedaspaid = '1',
		$extra
		winnermarkedaspaiddate = '" . DATETIME24H . "'
		WHERE project_id = '" . intval($pid) . "'
			AND bid_id = '" . intval($bid) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
}

/**
* Function to mark a lowest unique bid listing as being paid in full to the seller
*
* @param        integer     project id
* @param        string      string of payment method used
*
* @return	boolean     Returns true or false if the listing could be marked as paid
*/
function mark_lub_listing_as_paid($pid = 0, $winnermarkedaspaidmethod = '')
{
	global $ilance, $phrase, $ilconfig, $ilpage;
	
	$ilance->feedback = construct_object('api.feedback');
	$ilance->accounting = construct_object('api.accounting');
	$ilance->accounting_fees = construct_object('api.accounting_fees');
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
	$ilance->email = construct_dm_object('email', $ilance);
		
	// #### is there a winning lowest unique bid amount placed?
	$uniques = $ilance->db->query("
		SELECT uid, user_id, uniquebid, date, buyershipcost, buyershipperid
		FROM " . DB_PREFIX . "projects_uniquebids
		WHERE project_id = '" . intval($pid) . "'
		    AND status = 'lowestunique'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($uniques) > 0)
	{
		$resunique = $ilance->db->fetch_array($uniques, DB_ASSOC);
		
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects_uniquebids
			SET winnermarkedaspaid = '1',
			winnermarkedaspaiddate = '" . DATETIME24H . "',
			winnermarkedaspaidmethod = '" . $ilance->db->escape_string($winnermarkedaspaidmethod) . "'
			WHERE project_id = '" . intval($pid) . "'
				AND uid = '" . $resunique['uid'] . "'
		", 0, null, __FILE__, __LINE__);
			
		// #### create paid invoice to winning bidder ##################
		$transactionid = construct_transaction_id();
		$invoiceid = $ilance->accounting->insert_transaction(
			0,
			intval($pid),
			0,
			$resunique['user_id'],
			0,
			0,
			0,
			$phrase['_lowest_unique_bid_winning_amount_congratulations'],
			sprintf("%01.2f", $resunique['uniquebid'] + $resunique['buyershipcost']),
			sprintf("%01.2f", $resunique['uniquebid'] + $resunique['buyershipcost']),
			'paid',
			'debit',
			'account',
			DATETIME24H,
			DATEINVOICEDUE,
			DATETIME24H,
			'',
			0,
			0,
			1,
			$transactionid,
			0,
			0
		);
		
		// #### set invoice as being a donation fee type invoice #######
		$charityid = fetch_auction('charityid', intval($pid));
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET isdonationfee = '1',
			charityid = '" . intval($charityid) . "'
			WHERE invoiceid = '" . intval($invoiceid) . "'
		", 0, null, __FILE__, __LINE__);
		
		// #### update donation details in listing table ###############
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects
			SET haswinner = '1',
			winner_user_id = '" . $resunique['user_id'] . "',
			donationinvoiceid = '" . intval($invoiceid) . "'
			WHERE project_id = '" . intval($pid) . "'
		", 0, null, __FILE__, __LINE__);
		
		$currencyid = fetch_auction('currencyid', intval($pid));
		$shipmethod = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . intval($pid) . "'", "ship_method");
		if (empty($shipmethod))
		{
			$shipmethod = 'localpickup';
		}
		
		$shippingservice = (($shipmethod == 'flatrate' OR $shipmethod == 'calculated')
			? print_shipping_partner($resunique['buyershipperid'])
			: $phrase['_local_pickup_only']);
			
		$shippingcost = (($shipmethod == 'flatrate' OR $shipmethod == 'calculated')
			? $ilance->currency->format($resunique['buyershipcost'], $currencyid)
			: $phrase['_none']);
		
		// #### generate final value donation fee to seller (if applicable)
		$ilance->accounting_fees->construct_final_value_donation_fee(intval($pid), ($resunique['uniquebid'] + $resunique['buyershipcost']), 'charge');
			
		// #### email user
		$project_title = $ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . intval($pid) . "'", "project_title");
		
		$ilance->email->mail = fetch_user('email', $resunique['user_id']);
		$ilance->email->slng = fetch_user_slng($resunique['user_id']);                                                                        
		$ilance->email->get('cron_low_unique_bid_winner');		
		$ilance->email->set(array(
			'{{project_title}}' => stripslashes($project_title),
			'{{project_id}}' => intval($pid),
			'{{username}}' => fetch_user('username', $resunique['user_id']),
			'{{invoiceid}}' => $invoiceid,
			'{{lowuniquebid}}' => $ilance->currency->format($resunique['uniquebid'], $currencyid),
			'{{totalbids}}' => $ilance->bid_lowest_unique->fetch_bid_count(intval($pid)),
			'{{windate}}' => $resunique['date'],
			'{{amounttopay}}' => $ilance->currency->format(($resunique['uniquebid'] + $resunique['buyershipcost']), $currencyid),
			'{{txnid}}' => $transactionid,
			'{{paymenturl}}' => HTTPS_SERVER . "invoicepayment.php?cmd=view&id=" . $invoiceid,
			'{{shippingcost}}' => $shippingcost,
			'{{shippingservice}}' => $shippingservice,
		));                                                                        
		$ilance->email->send();
		
		// #### email losing bidders
		$losers = $ilance->db->query("
			SELECT user_id, uniquebid, date
			FROM " . DB_PREFIX . "projects_uniquebids
			WHERE project_id = '" . intval($pid) . "'
			    AND status != 'lowestunique'
			    AND user_id != '" . $resunique['user_id'] . "'
			GROUP BY user_id
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($losers) > 0)
		{
			while ($loser = $ilance->db->fetch_array($losers, DB_ASSOC))
			{
				// #### email loser
				$ilance->email->mail = fetch_user('email', $loser['user_id']);
				$ilance->email->slng = fetch_user_slng($loser['user_id']);                                                                                        
				$ilance->email->get('cron_low_unique_bid_loser');		
				$ilance->email->set(array(
					'{{project_title}}' => stripslashes($project_title),
					'{{project_id}}' => intval($pid),
					'{{username}}' => fetch_user('username', $loser['user_id']),
					'{{invoiceid}}' => $invoiceid,
					'{{lowuniquebid}}' => $ilance->currency->format($resunique['uniquebid'], $currencyid),
					'{{totalbids}}' => $ilance->bid_lowest_unique->fetch_bid_count(intval($pid)),
					'{{windate}}' => $resunique['date'],
					'{{winner}}' => fetch_user('username', $resunique['user_id']),
				));                                                                                        
				$ilance->email->send();
			}
		}
		
		// #### email admin
		$ilance->email->mail = SITE_EMAIL;
		$ilance->email->slng = fetch_site_slng();                                                                        
		$ilance->email->get('cron_low_unique_bid_admin');		
		$ilance->email->set(array(
			'{{project_title}}' => stripslashes($project_title),
			'{{project_id}}' => intval($pid),
			'{{invoiceid}}' => $invoiceid,
			'{{lowuniquebid}}' => $ilance->currency->format($resunique['uniquebid'], $currencyid),
			'{{totalbids}}' => $ilance->bid_lowest_unique->fetch_bid_count(intval($pid)),
			'{{windate}}' => $resunique['date'],
			'{{winner}}' => fetch_user('username', $resunique['user_id']),
			'{{amounttopay}}' => $ilance->currency->format($resunique['uniquebid'] + $resunique['buyershipcost'], $currencyid),
			'{{txnid}}' => $transactionid,
			'{{shippingcost}}' => $shippingcost,
			'{{shippingservice}}' => $shippingservice,
		));                                                                        
		$ilance->email->send();
		
		return true;
	}
	
	return false;
}

/**
* Function to mark a lowest unique bid listing as being unpaid to the seller
*
* @param        integer     project id
*
* @return	boolean     Returns true or false if the listing could be marked as unpaid
*/
function mark_lub_listing_as_unpaid($pid = 0)
{
	global $ilance, $phrase, $ilconfig, $ilpage;
	
	// #### require backend ################################################
	$ilance->feedback = construct_object('api.feedback');
	$ilance->accounting = construct_object('api.accounting');
	$ilance->accounting_fees = construct_object('api.accounting_fees');
	$ilance->bid = construct_object('api.bid');
	$ilance->bid_lowest_unique = construct_object('api.bid_lowest_unique');
	$ilance->email = construct_dm_object('email', $ilance);
		
	// #### is there a winning lowest unique bid amount placed? ############
	$uniques = $ilance->db->query("
		SELECT uid, user_id, uniquebid, date, buyershipcost, buyershipperid
		FROM " . DB_PREFIX . "projects_uniquebids
		WHERE project_id = '" . intval($pid) . "'
		    AND status = 'lowestunique'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($uniques) > 0)
	{
		$resunique = $ilance->db->fetch_array($uniques, DB_ASSOC);
		
		$invoiceid = fetch_auction('donationinvoiceid', intval($pid));
		$charityid = fetch_auction('charityid', intval($pid));
		
		// #### generate final value donation fee reversal #############
		$ilance->accounting_fees->construct_final_value_donation_fee(intval($pid), ($resunique['uniquebid'] + $resunique['buyershipcost']), 'refund');
		
		// #### remove payment details from bids table #################
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects_uniquebids
			SET winnermarkedaspaid = '0',
			winnermarkedaspaiddate = '0000-00-00 00:00:00',
			winnermarkedaspaidmethod = ''
			WHERE project_id = '" . intval($pid) . "'
				AND uid = '" . $resunique['uid'] . "'
		", 0, null, __FILE__, __LINE__);
			
		// #### removed paid invoice to winning bidder #################
		if ($invoiceid > 0)
		{
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "invoices
				WHERE invoiceid = '" . intval($invoiceid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		
		// #### update donation details in listing table ###############
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects
			SET donermarkedaspaid = '0',
			donermarkedaspaiddate = '0000-00-00 00:00:00',
			donationinvoiceid = '0'
			WHERE project_id = '" . intval($pid) . "'
		", 0, null, __FILE__, __LINE__);
		
		return true;
	}
	
	return false;
}

/**
* Function to print address select field options based on the users credit card billing details
*
* @param        integer     user id
*
* @return	string      Returns the HTML representation of the select <option>'s
*/
function print_cc_shipping_address_pulldown($userid = 0)
{
        global $ilance, $myapi, $ilconfig;
        
        $html = '';
        if ($userid > 0)
        {
                $sql = $ilance->db->query("
                        SELECT cc_id, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country
                        FROM " . DB_PREFIX . "creditcards
                        WHERE user_id = '" . intval($userid) . "'
                                AND creditcard_status = 'active'
                                AND authorized = 'yes'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $html = '<option value="card[' . $res['cc_id'] . ']" style="font-family: verdana">';
                        if (!empty($res['card_billing_address2']))
                        {
                                $html .= stripslashes(ucfirst($res['card_billing_address1'])) . ", " .
                                stripslashes(ucfirst($res['card_billing_address2'])) . ", " .
                                stripslashes(ucfirst($res['card_city'])) . ", " .
                                stripslashes(ucfirst($res['card_state'])) . ", " .
                                mb_strtoupper($res['card_postalzip']) . ", " .
                                $ilance->db->fetch_field(DB_PREFIX."locations", "locationid = '".$res['card_country']."'", "location_" . fetch_user_slng(intval($userid)));
                        }
                        else
                        {
                                $html .= stripslashes(ucfirst($res['card_billing_address1'])) . ", " .
                                stripslashes(ucfirst($res['card_city'])) . ", " .
                                stripslashes(ucfirst($res['card_state'])) . ", " .
                                mb_strtoupper($res['card_postalzip']) . ", " .
                                $ilance->db->fetch_field(DB_PREFIX."locations", "locationid = '".$res['card_country']."'", "location_".fetch_user_slng(intval($userid)));
                        }
                        $html .= '</option>';
                }
        }
        
        return $html;
}

/**
* Function to print an address based on the users credit card billing details
*
* @param        integer     user id
*
* @return	string      Returns the HTML representation of the address
*/
function print_cc_shipping_address_text($userid = 0)
{
        global $ilance, $myapi, $phrase, $ilconfig;
        if ($userid > 0)
        {
                $sql = $ilance->db->query("
                            SELECT cc_id, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country
                            FROM " . DB_PREFIX . "creditcards
                            WHERE user_id = '" . intval($userid) . "'
                                AND creditcard_status = 'active'
                                AND authorized = 'yes'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if (!empty($res['card_billing_address2']))
                        {
                                $html = stripslashes(ucfirst($res['card_billing_address1'])) . ", " .
                                        stripslashes(ucfirst($res['card_billing_address2'])) . ", " .
                                        stripslashes(ucfirst($res['card_city'])) . ", " .
                                        stripslashes(ucfirst($res['card_state'])) . ", " .
                                        mb_strtoupper($res['card_postalzip']) . ", " .
                                        $ilance->db->fetch_field(DB_PREFIX."locations", "locationid = '".$res['card_country']."'", "location_" . fetch_user_slng(intval($userid)));
                        }
                        else
                        {
                                $html = stripslashes(ucfirst($res['card_billing_address1'])) . ", " .
                                        stripslashes(ucfirst($res['card_city'])) . ", " .
                                        stripslashes(ucfirst($res['card_state'])) . ", " .
                                        mb_strtoupper($res['card_postalzip']) . ", " .
                                        $ilance->db->fetch_field(DB_PREFIX."locations", "locationid = '".$res['card_country']."'", "location_" . fetch_user_slng(intval($userid)));
                        }
                }
        }
        else
        {
                $html = $phrase['_no_shipping_address_available'];
        }
	
        return $html;
}

/**
* Function to print a shipping address pulldown menu based on the users personal details
*
* @param        integer     user id
*
* @return	string      Returns the HTML representation of the shipping address pulldown menu
*/
function print_shipping_address_pulldown($userid = 0)
{
        global $ilance, $myapi, $phrase, $ilconfig;
        if ($userid > 0)
        {
                $html = '<select name="shipping_address_id" style="font-family: verdana">';
                
                $sql = $ilance->db->query("
                        SELECT address, address2, city, state, zip_code, country
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                                AND status = 'active'
                ", 0, null, __FILE__, __LINE__);                
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $html .= '<option value="profile[' . intval($userid) . ']">';
                        if (!empty($res['address2']))
                        {
                                $html .= stripslashes(ucwords($res['address'])) . ", " .
                                        stripslashes(ucwords($res['address2'])) . ", " .
                                        stripslashes(ucwords($res['city'])) . ", " .
                                        stripslashes(ucwords($res['state'])) . ", " .
                                        mb_strtoupper($res['zip_code']) . ", " .
                                        $ilance->db->fetch_field(DB_PREFIX."locations", "locationid = '".$res['country']."'", "location_" . fetch_user_slng(intval($userid)));
                        }
                        else
                        {
                                $html .= stripslashes(ucwords($res['address'])) . ", " .
                                        stripslashes(ucwords($res['city'])) . ", " .
                                        stripslashes(ucwords($res['state'])) . ", " .
                                        mb_strtoupper($res['zip_code']) . ", " .
                                        $ilance->db->fetch_field(DB_PREFIX."locations", "locationid = '".$res['country']."'", "location_" . fetch_user_slng(intval($userid)));
                        }
                        $html .= '</option>';
                        $html .= print_cc_shipping_address_pulldown(intval($userid));
                }
                $html .= '</select>';
        }
        else
        {
                $html = $phrase['_no_shipping_address_available'];
        }
        
        return $html;
}

/**
* Function to print a shipping address text based on the users personal details
*
* @param        integer     user id
*
* @return	string      Returns the HTML representation of the shipping address
*/
function print_shipping_address_text($userid = 0)
{
        global $ilance, $myapi, $phrase, $ilconfig;
        
        $html = $phrase['_no_shipping_address_available'];
        if ($userid > 0)
        {
                $sql = $ilance->db->query("
                        SELECT address, address2, city, state, zip_code, country
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                            AND status = 'active'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        
                        ($apihook = $ilance->api('print_shipping_address_text_start')) ? eval($apihook) : false;  

			$html = (!empty($res['address2']))
				? stripslashes(ucfirst($res['address'])) . ', ' . stripslashes(ucfirst($res['address2'])) . ', ' . stripslashes(ucfirst($res['city'])) . ', ' . stripslashes(ucfirst($res['state'])) . ', ' . mb_strtoupper($res['zip_code']) . ', ' . $ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $res['country'] . "'", "location_" . fetch_user_slng(intval($userid)))
				: stripslashes(ucfirst($res['address'])) . ', ' . stripslashes(ucfirst($res['city'])) . ', ' . stripslashes(ucfirst($res['state'])) . ', ' . mb_strtoupper($res['zip_code']) . ', ' . $ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $res['country'] . "'", "location_" . fetch_user_slng(intval($userid)));
                        
                        ($apihook = $ilance->api('print_shipping_address_text_end')) ? eval($apihook) : false;
                }
        }
        
        return $html;
}

/**
* Function to process profile questions which is ultimately updated or inserted as new data within the database.
*
* @param       array          answers (keys and values)
* @param       integer        user id
*
* @return      nothing
*/
function process_profile_questions(&$custom, $userid = 0)
{
        global $ilance, $ilconfig;
        
        if (isset($custom) AND is_array($custom))
        {
                foreach ($custom AS $key => $value)
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "profile_answers
                                WHERE questionid = '" . intval($key) . "'
                                AND user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                if (isset($value))
                                {
                                        if (is_array($value))
                                        {
                                                $value = serialize($value);        
                                        }
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "profile_answers
                                                SET answer = '" . $ilance->db->escape_string($value) . "'
                                                WHERE questionid = '" . intval($key) . "'
                                                        AND user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "profile_answers
                                                WHERE questionid = '" . intval($key) . "'
                                                        AND user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                        else
                        {
                                if (!empty($value))
                                {
                                        if (is_array($value))
                                        {
                                                $value = serialize($value);        
                                        }
                                        
                                        $expiry = gmdate('Y-m-d H:i:s', mktime(gmdate('H', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('i', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('s', time() + 3600 * ($ilconfig['globalserverlocale_officialtimezone'] + $ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('m', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])), gmdate('d', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))+$ilconfig['verificationlength'], gmdate('Y', time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))));
                                        
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "profile_answers
                                                (answerid, questionid, user_id, answer, date, visible, isverified, verifyexpiry, invoiceid)
                                                VALUES(
                                                NULL,
                                                '" . intval($key) . "',
                                                '" . intval($userid) . "',
                                                '" . $ilance->db->escape_string($value) . "',
                                                '" . DATETIME24H . "',
                                                '1',
                                                '0',
                                                '" . $expiry . "',
                                                '0')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
        }
}

/**
* Function to fetch the total number of shipping services an item is using for their listing
*
* @param       integer        listing id
*
* @return      integer        Returns number of shipping services count
*/
function fetch_shipping_services_count($pid = 0)
{
	global $ilance;
	
	$count = 0;
	$ship_method = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . intval($pid) . "'", "ship_method");
	
	if ($ship_method == 'flatrate' OR $ship_method == 'calculated')
	{
		$count = 1;
		$sql = $ilance->db->query("
			SELECT MAX(row) AS count
			FROM " . DB_PREFIX . "projects_shipping_regions
			WHERE project_id = '" . intval($pid) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$count = $res['count'];
		}
	}
	
	return $count;
}

function print_shipping_address_text_herakle($userid = 0)
{
        global $ilance, $myapi, $phrase, $ilconfig;
        
        $html = $phrase['_no_shipping_address_available'];
        if ($userid > 0)
        {
                $sql = $ilance->db->query("
                        SELECT address, address2, city, state, zip_code, country, phone, companyname
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                            AND status = 'active'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        
                        ($apihook = $ilance->api('print_shipping_address_text_start')) ? eval($apihook) : false;  

			$html = (!empty($res['address2']))
				? 'Telephone: '. mb_strtoupper($res['phone']) . '<br>Address1: '. stripslashes(ucfirst($res['address'])) . '<br>Address2: ' . stripslashes(ucfirst($res['address2'])) . '<br> City: ' . stripslashes(ucfirst($res['city'])) . '<br> State: ' . stripslashes(ucfirst($res['state'])) . '<br> Zip: ' . mb_strtoupper($res['zip_code']) . '<br> Country: ' . $ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $res['country'] . "'", "location_" . fetch_user_slng(intval($userid)))
				:'Telephone: '. mb_strtoupper($res['phone']) . '<br>Address1: '. stripslashes(ucfirst($res['address'])) . '<br> City: ' . stripslashes(ucfirst($res['city'])) .'<br> State: ' . stripslashes(ucfirst($res['state'])) . '<br> Zip: ' . mb_strtoupper($res['zip_code']) . '<br> Country: ' . $ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $res['country'] . "'", "location_" . fetch_user_slng(intval($userid)));
                        
                        ($apihook = $ilance->api('print_shipping_address_text_end')) ? eval($apihook) : false;
                }
        }
        
        return $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>