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

if (!class_exists('auction'))
{
	exit;
}

/**
* Auction posting class to perform the majority of printing and displaying of filters and other form elements
* for service and product auctions.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class auction_post extends auction
{
        /**
        * Function to print bid filters controlled via radio button and multiple selective options
        * during the post of a new service or product auction.
        *
        * @return      string        HTML representation of the bid filters permitted
        */
        function print_bid_filters()
        {
                global $ilance, $ilconfig, $phrase;
                
                // bid filters: checkboxes
                $filtered_rating0 = $filter_rating1 = $filtered_rating1 = $filtered_rating2 = $filtered_rating3 = $filtered_rating4 = $filtered_rating5 = $filter_country1 = $filter_state1 = $filter_city1 = $filtered_city1 = $filter_zip1 = $filtered_zip1 = $businessnumber = $underage = '';
        
                if (!empty($ilance->GPC['filter_rating']) AND $ilance->GPC['filter_rating'])
                {
                        $filter_rating1 = 'checked="checked"';
                }
                
                if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '1')
                {
                        $filtered_rating1 = 'selected="selected"';
                }
                else if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '2')
                {
                        $filtered_rating2 = 'selected="selected"';
                }
                else if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '3')
                {
                        $filtered_rating3 = 'selected="selected"';
                }
                else if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '4')
                {
                        $filtered_rating4 = 'selected="selected"';
                }
                else if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '5')
                {
                        $filtered_rating5 = 'selected="selected"';
                }
                else
                {
                        $filtered_rating0 = 'selected="selected"';        
                }
        
                if (!empty($ilance->GPC['filter_country']) AND $ilance->GPC['filter_country'])
                {
                        $filter_country1 = 'checked="checked"';
                }
        
                if (!empty($ilance->GPC['filter_state']) AND $ilance->GPC['filter_state'])
                {
                        $filter_state1 = 'checked="checked"';
                }
        
                if (!empty($ilance->GPC['filter_city']) AND $ilance->GPC['filter_city'])
                {
                        $filter_city1 = 'checked="checked"';
                }
                if (isset($ilance->GPC['filtered_city']) AND $ilance->GPC['filtered_city'] != '')
                {
                        $filtered_city1 = stripslashes(strip_tags($ilance->GPC['filtered_city']));
                        $filter_city1 = 'checked="checked"';
                }
        
                if (!empty($ilance->GPC['filter_zip']) AND $ilance->GPC['filter_zip'])
                {
                        $filter_zip1 = 'checked="checked"';
                }
                if (isset($ilance->GPC['filtered_zip']) AND $ilance->GPC['filtered_zip'] != '')
                {
                        $filtered_zip1 = str_replace(' ', '', stripslashes(strip_tags($ilance->GPC['filtered_zip'])));
                        $filter_zip1 = 'checked="checked"';
                }
        
                if (isset($ilance->GPC['filter_underage']) AND $ilance->GPC['filter_underage'] > 0)
                {
                        $underage = 'checked="checked"';
                }
        
                if (isset($ilance->GPC['filter_businessnumber']) AND $ilance->GPC['filter_businessnumber'] > 0)
                {
                        $businessnumber = 'checked="checked"';
                }

                $html = '
                <table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                <tr valign="top">
                    <td width="50%">
                    
                        <table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                        <tr class="alt1" valign="top">
                                <td width="4%" valign="top"><input type="checkbox" name="filter_rating" id="filter_rating" value="1" ' . $filter_rating1 . ' /></td>
                                <td><div style="padding-bottom:3px"><label for="filter_rating">' . $phrase['_requires_bidders_have_at_least_an_overall'] . '&nbsp; </label></div>
                                <div>
                                <select name="filtered_rating" style="font-family: verdana">
                                                <option value="" ' . $filtered_rating0 . ' >-</option>
                                                <option value="1" ' . $filtered_rating1 . ' >' . $phrase['_at_least'] . ' 1.0 / 5.0</option>
                                                <option value="2" ' . $filtered_rating2 . ' >' . $phrase['_at_least'] . ' 2.0 / 5.0</option>
                                                <option value="3" ' . $filtered_rating3 . ' >' . $phrase['_at_least'] . ' 3.0 / 5.0</option>
                                                <option value="4" ' . $filtered_rating4 . ' >' . $phrase['_at_least'] . ' 4.0 / 5.0</option>
                                                <option value="5" ' . $filtered_rating5 . ' >' . $phrase['_at_least'] . ' 5.0 / 5.0</option>
                                </select></div></td>
                        </tr>
                        <tr class="alt1" valign="top">
                                <td valign="top"><input type="checkbox" name="filter_city" id="filter_city" value="1" ' . $filter_city1 . ' /></td>
                                <td nowrap="nowrap"><div style="padding-bottom:3px"><label for="filter_city"> ' . $phrase['_requires_bidders_reside_or_do_business_city'] . '&nbsp; </label></div>
                                <div><input type="text" id="filtered_city" class="input" name="filtered_city" value="' . $filtered_city1 . '" onkeypress="return noenter()" title="" style="width:175px" /></div></td>
                        </tr>';                
                        if ($ilconfig['registrationdisplay_dob'])
                        {
                                $html .='
                                <tr class="alt1" valign="top">
                                        <td><input type="checkbox" name="filter_underage" id="filter_underage" value="1" ' . $underage . ' /></td>
                                        <td nowrap="nowrap"><span><label for="filter_underage"> ' . $phrase['_prevent_under_age_bidders_18_years_and_younger'] . '</label></span></td>
                                </tr>';
                        }
                        else
                        {
                                $html .='
                                <tr>
                                        <td colspan="2"><input type="hidden" name="filter_underage" value="0" /></td>
                                </tr>';       
                        }
			
                        $html .= '
                        <tr valign="top">
                                <td><input type="checkbox" name="filter_businessnumber" id="filter_businessnumber" value="1" ' . $businessnumber . ' /></td>
                                <td nowrap="nowrap"><span><label for="filter_businessnumber"> ' . $phrase['_prevent_bidders_that_have_not_supplied_a_valid_business_or_vat_number'] . '</label></span></td>
                        </tr>
                        </table>
                    
                    </td>
                    <td valign="top"><div style="width:20px"></div></td>
                    <td valign="top" width="50%">
                    
                        <table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                        <tr class="alt1" valign="top">
                                <td valign="top"><input type="checkbox" name="filter_country" id="filter_country" value="1" ' . $filter_country1 . ' /></td>
                                <td valign="top"><div style="padding-bottom:3px"><label for="filter_country"> ' . $phrase['_requires_bidders_reside_or_do_business_country'] . '&nbsp; </label></div>
                                <div>{country_js_pulldown}</div></td>
                        </tr>
                        <tr class="alt1" valign="top">
                                <td valign="top"><input type="checkbox" name="filter_state" id="filter_state" value="1" ' . $filter_state1 . ' /></td>
                                <td><div style="padding-bottom:3px"><label for="filter_state"> ' . $phrase['_requires_bidders_reside_or_do_business_state'] . '&nbsp; </label></div>
                                <div style="padding-bottom:3px">{state_js_pulldown}</div></td>
                        </tr>
                        <tr valign="top">
                                <td valign="top"><input type="checkbox" name="filter_zip" id="filter_zip" value="1" ' . $filter_zip1 . ' /></td>
                                <td nowrap><div style="padding-bottom:3px"><label for="filter_zip"> ' . $phrase['_requires_bidders_reside_or_do_business_zip'] . '&nbsp; </label></div>
                                <div><input type="text" id="filtered_zip" class="input" name="filtered_zip" value="' . $filtered_zip1 . '" onkeypress="return noenter()" title="" style="width:100px" /> <span class="gray">(' . $phrase['_no_spaces_or_dashes'] . ')</span></div></td>
                        </tr>
                        </table>
                        
                    </td>
                </tr>
                </table>';
                
                return $html;
        }
        
        /**
        * Function to print profile bid filters controlled via radio button and multiple selective options
        * during the post of a new service or product auction such as ranges (from / to values) and pulldown
        * menu options (including multiple choice selection values).
        *
        * This function is called in the advanced search menu as well as when a user is posting a new listing.
        *
        * @param       integer       category id
        * @param       string        display mode (input, preview, output)
        * @param       string        category type (service)
        * @param       integer       project id (for updating listing)
        * 
        * @return      string        HTML representation of the bid filters permitted
        */
        function print_profile_bid_filters($cid = 0, $displaymode = '', $catype = 'service', $projectid = 0)
        {
                global $ilance, $ilconfig, $phrase, $show;
                
                $show['profile_filters'] = false;
                
                $html = '';
                if ($cid == 0)
                {
	                $sql = $ilance->db->query("
	                        SELECT questionid, description, question, isfilter, filtertype, filtercategory, inputtype, multiplechoice
	                        FROM " . DB_PREFIX . "profile_questions
	                        WHERE isfilter = '1'
                                        AND filtercategory = '-1'
	                        	AND (inputtype = 'int' OR inputtype = 'multiplechoice' OR inputtype = 'pulldown')
	                ");                	
                }
                else 
                {
	                $sql = $ilance->db->query("
	                        SELECT questionid, description, question, isfilter, filtertype, filtercategory, inputtype, multiplechoice
	                        FROM " . DB_PREFIX . "profile_questions
	                        WHERE isfilter = '1'
                                        AND (filtercategory = '" . intval($cid) . "' OR filtercategory = '-1')
                                        AND (inputtype = 'int' OR inputtype = 'multiplechoice' OR inputtype = 'pulldown')
	                ");
                }
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $show['profile_filters'] = true;
                        
                        $html = '
                        <table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                        <tr>';
                        
                        $i = $q = $m = $n = $g = 0;  
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $d = $i % 2;
                                
                                // #### INPUT MODE #############################
                                if ($displaymode == 'input')
                                {
                                        switch ($res['inputtype'])
                                        {
                                        	case 'int':
                                                {
                                                        $html .= '<td width="25%" valign="top"><div><strong>' . $res['question'] . '</strong></div><div class="gray">' . $phrase['_select_range_pattern'] . '</div><div style="padding-top:4px"><strong>' . $phrase['_between_upper'] . '</strong> &nbsp;<input class="input" name="pa[range][' . $res['questionid'] . '][from]" value="" style="width:40px" /> &nbsp;&nbsp;<strong>' . $phrase['_and'] . '</strong> &nbsp;<input name="pa[range][' . $res['questionid'] . '][to]" value="" class="input" style="width:40px" /></div></td>';
                                                        $q++;
                                                        break;
                                                }
                                                case 'multiplechoice':
                                        	case 'pulldown':
                                                {
                                                        $questions = $res['multiplechoice'];
                                                        $choices = explode('|', $questions);
                                                        
                                                        $html_choice = array();
                                                        $k = 0;
                                                        foreach ($choices as $choice)
                                                        {
                                                               $html_choice["$k"] = stripslashes($choice);
                                                               $k++;
                                                        }
                                        
                                                        $html .= '<td width="25%" valign="top"><strong>' . $res['question'] . '</strong><div class="gray" style="padding-bottom:6px">' . $phrase['_select_multiple_choices'] . '</div>';
                                                        
                                                        $j = 0;
                                                        $km = $k + $n;
                                                        while ($n < $km)
                                                        {
                                                                $html .= '<div><label for="pulldown_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '_' . $j . '"><input type="checkbox" name="pa[choice_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '][' . $res['questionid'] . '][custom]" value="' . $html_choice["$j"] . '" id="pulldown_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '_' . $j . '" /> ' . $html_choice["$j"] . '</label></div>';
                                                                $n++;
                                                                $j++;
                                                        }
                                                        
                                                        $html .= '</td>';

                                                        break;
                                                }
                                        }    
                                }
                                
                                // #### UPDATE INPUT MODE ######################
                                else if ($displaymode == 'update')
                                {
                                        switch ($res['inputtype'])
                                        {
                                        	case 'int':
                                                {
                                                        $from = $to = '';
                                                        $fromto = $ilance->db->fetch_field(DB_PREFIX . "profile_filter_auction_answers", "questionid = '" . $res['questionid'] . "'", "answer");
                                                        if (!empty($fromto))
                                                        {
                                                                $fromto = explode('|', $fromto);
                                                                $from = $fromto[0];
                                                                $to = $fromto[1];
                                                        }
                                                        
                                                        $html .= '<td width="25%" valign="top"><div><strong>' . $res['question'] . '</strong></div><div class="gray">' . $phrase['_select_range_pattern'] . '</div><div style="padding-top:4px"><strong>' . $phrase['_between_upper'] . '</strong> &nbsp;<input class="input" name="pa[range][' . $res['questionid'] . '][from]" value="' . $from . '" style="width:40px" /> &nbsp;&nbsp;<strong>' . $phrase['_and'] . '</strong> &nbsp;<input name="pa[range][' . $res['questionid'] . '][to]" value="' . $to . '" class="input" style="width:40px" /></div></td>';
                                                        $q++;
                                                        break;
                                                }
                                                case 'multiplechoice':
                                        	case 'pulldown':
                                                {
                                                        $questions = $res['multiplechoice'];
                                                        $choices = explode('|', $questions);
                                                        
                                                        $existing = $ilance->db->fetch_field(DB_PREFIX . "profile_filter_auction_answers", "questionid = '" . $res['questionid'] . "'", "answer");
                                                        if (!empty($existing))
                                                        {
                                                                $existing = explode('|', $existing);        
                                                        }
                                                        
                                                        $html_choice = array();
                                                        $k = 0;
                                                        foreach ($choices as $choice)
                                                        {
                                                               $html_choice["$k"] = stripslashes($choice);
                                                               $k++;
                                                        }
                                        
                                                        $html .= '<td width="25%" valign="top"><strong>' . $res['question'] . '</strong><div class="gray" style="padding-bottom:6px">' . $phrase['_select_multiple_choices'] . '</div>';
                                                        
                                                        $j = 0;
                                                        $km = $k + $n;
                                                        while ($n < $km)
                                                        {
                                                                $checked = '';
                                                                if (!empty($existing) AND is_array($existing) AND in_array($html_choice["$j"], $existing))
                                                                {
                                                                        $checked = 'checked="checked"';
                                                                }
                                                                
                                                                $html .= '<div><label for="pulldown_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '_' . $j . '"><input type="checkbox" name="pa[choice_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '][' . $res['questionid'] . '][custom]" value="' . $html_choice["$j"] . '" id="pulldown_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '_' . $j . '" ' . $checked . ' /> ' . $html_choice["$j"] . '</label></div>';
                                                                $n++;
                                                                $j++;
                                                        }
                                                        
                                                        $html .= '</td>';

                                                        break;
                                                }
                                        }    
                                }
                                
                                // check if the current column is dividable by two
                                if ($d == 4 && $i != 0)
                                {
                                        $html .= '</tr><tr>';
                                }
                                $i++;
                        }
                        
                        $html .= '
                        </tr>
                        </table>';
                }
                else
                {
                        $html = '<div>' . $phrase['_there_are_currently_no_filters_that_exist_within_the_selected_category'] . '</div>';
                }
                
                return $html;
        }
        
        /**
        * Function to print the available bid amount types for the auction poster to select based on the admin
        * defined bid types for this particular category.
        *
        * @param       integer       category id
        * @param       string        category type
        * 
        * @return      string        HTML representation of the bid amount type form elements
        */
        function print_bid_amount_type($cid = 0, $cattype = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                
                $bidtype1 = '';
                $bidtype2 = 'checked="checked"';
                if (!empty($ilance->GPC['filter_bidtype']) AND $ilance->GPC['filter_bidtype'])
                {
                        $bidtype1 = 'checked="checked"';
                        $bidtype2 = '';
                }

                $bidamounttype = isset($ilance->GPC['filtered_bidtype']) ? $ilance->GPC['filtered_bidtype'] : '';
                
                // will also provide $show['bidamountgroups'] to true or false
                $bidamounttype_pulldown = $this->construct_bidamounttype_pulldown($bidamounttype, $disable = 0, $dojs = 1, $cid, $cattype);
                
                $html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
                
                if ($show['bidamounttypes'])
                {
                        $html .= '
                        <tr class="alt1">
                                <td width="1%">
                                        <input type="radio" id="biddingtype" name="filter_bidtype" ' . $bidtype1 . ' value="1" />
                                </td>
                                <td align="left"><label for="biddingtype">' . $bidamounttype_pulldown . '</label></td>
                        </tr>';    
                }

                $html .= '
                <tr>
                        <td><input type="radio" id="biddingtype0" name="filter_bidtype" ' . $bidtype2 . ' value="0" /></td>
                        <td align="left"><label for="biddingtype0">' . $phrase['_i_will_accept_various_bidding_types_no_restriction'] . '</label></td>
                </tr>
                </table>';
                
                return $html;
        }
        
        /**
        * Function to print the budget logic selectable options.  For example, the poster could select
        * "I do not wish to disclose my budget" or he/she can select the appropriate budget range to select.
        * Additionally, admins can assign "insertion fees" to any budget group.  Insertion fees will also be
        * shown (their value) if they have been assigned to this category (based on the level of budget).  EX:
        * - Small Project ($100-$500) - Insertion Fee: $3.00
        * - Medium Project ($500-$1000) - Insertion Fee: $5.00
        * etc.
        *
        * @param       integer       category id
        * @param       string        category type (service)
        * @param       bool          do javascript (default true)
        * @param       bool          show insertion fees (default false)
        * 
        * @return      string        HTML representation of the bid amount type form elements
        */
        function print_budget_logic_type($cid = 0, $cattype = 'service', $dojs = 1, $showinsertionfees = false)
        {
                global $ilance, $ilconfig, $phrase, $show;
                
                $budget1 = '';
                $budget2 = 'checked="checked"';
                if (!empty($ilance->GPC['filter_budget']) AND $ilance->GPC['filter_budget'])
                {
                        $budget1 = 'checked="checked"';
                        $budget2 = '';
                }
                $selected = isset($ilance->GPC['filtered_budgetid']) ? intval($ilance->GPC['filtered_budgetid']) : '';
                
                // budget pulldown also sets $show['budgetgroups'] to true or false for logic below
                $show['selectedbudgetlogic'] = 0;
                $budget_pulldown  = $this->construct_budget_pulldown($cid, $selected, 'filtered_budgetid', $dojs, $showselect = 0, $showinsertionfees);
                $hidden = '';

                $html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';                
                if ($show['budgetgroups'])
                {
                        $html .= '
                        <tr class="alt1">
                                <td width="1%">
                                        <input type="radio" id="showbudget" name="filter_budget" ' . $budget1 . ' value="1" />
                                </td>
                                <td align="left"><label for="showbudget">' . $budget_pulldown . '</label></td>
                        </tr>';
                }
                else
                {
                        $hidden = '<input type="hidden" name="filtered_budgetid" value="0" />';
                }
                
                $nondisclosefeeamount = $phrase['_free'];
                $amount = $ilance->categories->nondisclosefeeamount($cid);
                if (!empty($amount) AND $amount > 0)
                {
                        $nondisclosefeeamount = $ilance->currency->format($amount);
                }
                
                if (empty($selected))
                {
                        $show['selectedbudgetlogic'] = $amount;
                }
                
                $html .= '
                <tr>
                        <td width="1%"><input type="radio" id="showbudget0" name="filter_budget" ' . $budget2 . ' value="0" />' . $hidden . '</td>
                        <td align="left"><label for="showbudget0"><strong>' . $phrase['_i_prefer_not_to_disclose_my_budget'] . '</strong> &nbsp;<span class="smaller gray">(' . $nondisclosefeeamount . ')</span></label></td>
                </tr>
                </table>';
                
                return $html;
        }
        
        /**
        * Function to print the budget links
        *
        * @param       integer       category id
        * @param       string        category type (service/product)
        * @param       integer       selected budget id
        *
        * @return      string        HTML representation of the budget as a link
        */
        function print_budget_logic_type_links($cid = 0, $cattype = 'service', $selected = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                
                $html = $budgetgroup = '';
                $url = PHP_SELF;
                
                if ($cid == 0)
                {
                        $show['budgetgroups'] = false;
                        return;
                }
                
                $query = $ilance->db->query("
                        SELECT budgetgroup
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($query) > 0)
                {
                        $res = $ilance->db->fetch_array($query, DB_ASSOC);
                        $budgetgroup = $res['budgetgroup'];
                        
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "budget
                                WHERE budgetgroup = '" . $ilance->db->escape_string($budgetgroup) . "'
                                ORDER BY budgetfrom ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $show['budgetgroups'] = true;
                                $counter = 0;
                                
                                // give user choice to select "any"
                                if (isset($selected) AND $selected == '0')
                                {
                                        $html .= '<div style="padding-bottom:3px"><strong>' . $phrase['_any_budget'] . '</strong></div>';
                                }
                                else
                                {
                                        $html .= '<div style="padding-bottom:3px"><a href="' . $url . '&amp;budget=0">' . $phrase['_any_budget'] . '</a></div>';
                                }
                                
                                if (isset($selected) AND $selected == '-1')
                                {
                                        $html .= '<div style="padding-bottom:3px"><strong>' . $phrase['_non_disclosed'] . '</strong></div>';
                                }
                                else
                                {
                                        $html .= '<div style="padding-bottom:3px"><a href="' . $url . '&amp;budget=-1">' . $phrase['_non_disclosed'] . '</a></div>';
                                }
                                unset($counter);
                                
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $counter = 0;
                                        if (isset($selected) AND $selected == $res['budgetid'])
                                        {
                                                if ($res['budgetto'] == '-1')
                                                {
                                                        $html .= '<div style="padding-bottom:3px"><strong>' . $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_or_more'] . '</strong></div>';    
                                                }
                                                else
                                                {
                                                        $html .= '<div style="padding-bottom:3px"><strong>' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . '</strong></div>';
                                                }
                                        }
                                        else 
                                        {
                                                if ($res['budgetto'] == '-1')
                                                {
                                                        $html .= '<div style="padding-bottom:3px"><a href="' . $url . '&amp;budget=' . $res['budgetid'] . '">' . $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_or_more'] . '</a></div>';    
                                                }
                                                else
                                                {
                                                        $html .= '<div style="padding-bottom:3px"><a href="' . $url . '&amp;budget=' . $res['budgetid'] . '">' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . '</a></div>';
                                                }
                                        }
                                }
                                unset($counter);
                        }
                }
                
                return $html;
        }
        
        /**
        * Function to print the budget javascript details for the yahoo slider widget
        *
        * @param       integer       category id
        * @param       string        category type (service/product)
        * @param       integer       selected budget id
        *
        * @return      string        array of budgets for yahoo slider widget
        */
        function print_budget_logic_type_js($cid = 0, $cattype = 'service', $selected = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                
                $htmla = "new Array(";
                $htmlb = "new Array(";
                $budgetgroup = '';
                
                if ($cid == 0 OR $cattype != 'service')
                {
                        $show['budgetgroups'] = false;
                        return;
                }
                
                $query = $ilance->db->query("
                        SELECT budgetgroup
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($query) > 0)
                {
                        $res = $ilance->db->fetch_array($query);
                        
                        $show['budgetgroups'] = true;
                                
                        $htmla .= "'" . $phrase['_any_budget'] . "',";
                        $htmlb .= "'0',";
                        
                        $htmla .= "'" . $phrase['_non_disclosed_budget'] . "',";
                        $htmlb .= "'-1',";
                        
                        $budgetgroup = $res['budgetgroup'];
                        
                        $sql2 = $ilance->db->query("
                                SELECT budgetid, budgetgroup, title, fieldname, budgetfrom, budgetto, insertiongroup
                                FROM " . DB_PREFIX . "budget
                                WHERE budgetgroup = '" . $ilance->db->escape_string($budgetgroup) . "'
                                ORDER BY budgetfrom ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql2) > 0)
                        {
                                while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
                                {
                                        if ($res2['budgetto'] == '-1')
                                        {
                                                $htmla .= "'" . $ilance->currency->format($res2['budgetfrom'], '', false, true) . " " . $phrase['_or_more'] . "',";
                                        }
                                        else
                                        {
                                                $htmla .= "'" . $ilance->currency->format($res2['budgetfrom'], '', false, true) . " " . $phrase['_to'] . " " . $ilance->currency->format($res2['budgetto'], '', false, true) . "',";
                                        }
                                        $htmlb .= "'" . $res2['budgetid'] . "',";
                                }
                        }
                        
                        $htmla = mb_substr($htmla, 0, -1);
                        $htmlb = mb_substr($htmlb, 0, -1);
                }
                
                $htmla .= ");";
                $htmlb .= ");";
                
                return array($htmla, $htmlb);
        }
        
        /**
        * Function to print the escrow filter option so that the auction poster can enable or disable
        * escrow to secure any funds for mentioned services or products during the transaction/delivery
        * process.
        *
        * @param       integer       category id
        * @param       string        category type (service)
        * @param       string        fee type (service, servicebuyer, serviceprovider, productmerchant, productbuyer)
        * 
        * @return      string        HTML representation of the filter form elements
        */
        function print_escrow_filter($cid = 0, $cattype = '', $feetype = '', $disabled = false)
        {
                global $ilance, $ilconfig, $ilpage, $phrase, $onload, $show;
                
		// #### using escrow ###########################################
		$escrowfee = '<strong>' . $phrase['_free'] . '</strong>';
                $escrow1 = $escrow3 = $escrow2 = '';
                if (!empty($ilance->GPC['filter_escrow']) AND $ilance->GPC['filter_escrow'] == '1')
                {
                        $escrow1 = 'checked="checked"';
                }
		
		// #### using direct payment gateway ###########################
                if (!empty($ilance->GPC['filter_gateway']) AND $ilance->GPC['filter_gateway'] == '1' AND $cattype == 'product')
                {
                        // using direct payment gateway
                        $escrow3 = 'checked="checked"';
                        $onload .= 'toggle_show(\'gatewaymethods\'); ';
                }
		
		// #### using offline payment method ###########################
                if (!empty($ilance->GPC['filter_offline']) AND $ilance->GPC['filter_offline'] == '1' AND $cattype == 'product')
                {
                        // default show offline outside marketplace payment options
			$escrow2 = 'checked="checked"';
                        $onload .= 'toggle_show(\'paymentmethods\'); ';
                }
		
                if ($cattype == 'service' AND $ilconfig['escrowsystem_enabled'] AND $ilconfig['escrowsystem_escrowcommissionfees'])
                {
			if ($ilconfig['escrowsystem_servicebuyerfixedprice'] != '0')
			{
				$escrowfee = '<span class="smaller gray">(' . $ilance->currency->format($ilconfig['escrowsystem_servicebuyerfixedprice']) . ' ' . $phrase['_final_value_commission_fee'] . ')</span>';
			}
			else if ($ilconfig['escrowsystem_servicebuyerpercentrate'] != '0.0')
			{
				$escrowfee = '<span class="smaller gray">(' . $ilconfig['escrowsystem_servicebuyerpercentrate'] . '% ' . $phrase['_final_value_commission_fee'] . ')</span>';
			}
                }
                else if ($cattype == 'product' AND $ilconfig['escrowsystem_enabled'] AND $ilconfig['escrowsystem_escrowcommissionfees'])
                {
			if ($ilconfig['escrowsystem_merchantfixedprice'] != '0')
			{
				$escrowfee = '<span class="smaller gray">(' . $ilance->currency->format($ilconfig['escrowsystem_merchantfixedprice']) . ' ' . $phrase['_final_value_commission_fee'] . ')</span>';
			}
			else if ($ilconfig['escrowsystem_merchantpercentrate'] != '0.0')
			{
				$escrowfee = '<span class="smaller gray">(' . $ilconfig['escrowsystem_merchantpercentrate'] . '% ' . $phrase['_final_value_commission_fee'] . ')</span>';
			}
                }

		// #### rebuild seller selected ipn gateway payment emails #####
                $paymethodoptionsemail = (isset($ilance->GPC['paymethodoptionsemail']) AND !empty($ilance->GPC['paymethodoptionsemail']) AND is_serialized($ilance->GPC['paymethodoptionsemail']))
			? unserialize($ilance->GPC['paymethodoptionsemail'])
			: array();
			
		$paymethodoptions = (isset($ilance->GPC['paymethodoptions']) AND !empty($ilance->GPC['paymethodoptions']) AND is_serialized($ilance->GPC['paymethodoptions']))
			? unserialize($ilance->GPC['paymethodoptions'])
			: array();
			
                $show['nodirectpaymentgateways'] = false;

		$gatewaypulldowns = '';
                $sql = $ilance->db->query("
                        SELECT groupname
                        FROM " . DB_PREFIX . "payment_groups
                        WHERE moduletype = 'ipn'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $num = 0;
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                if (isset($ilconfig[$res['groupname'] . '_active']) AND $ilconfig[$res['groupname'] . '_active'])
                                {
                                        if (isset($ilconfig[$res['groupname'] . '_directpayment']) AND $ilconfig[$res['groupname'] . '_directpayment'])
                                        {
						if (isset($ilance->GPC['cmd']) AND ($ilance->GPC['cmd'] == 'new-rfp') OR ($ilance->GPC['cmd'] == 'new-item' OR $ilance->GPC['cmd'] == 'selling' AND $ilance->GPC['mode'] == 'bulk'))
						{
							$gatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodoptions_' . $res['groupname'] . '"><input type="checkbox" name="paymethodoptions[' . $res['groupname'] . ']" id="paymethodoptions_' . $res['groupname'] . '" value="1" onclick="toggle_tr(\'cb_paymethodoptionsemail_' . $res['groupname'] . '\')" /> ' . ucfirst($res['groupname']) . '</label> <span style="display:none" id="cb_paymethodoptionsemail_' . $res['groupname'] . '">&nbsp;&nbsp;&nbsp;<span class="smaller blue">' . $phrase['_and_my_email_for_accepting_payments_through_this_gateway_is'] . '</span> &nbsp;&nbsp;<input type="text" name="paymethodoptionsemail[' . $res['groupname'] . ']" id="paymethodoptionsemail_' . $res['groupname'] . '" class="input" size="35" value="' . (isset($paymethodoptionsemail[$res['groupname']]) ? $paymethodoptionsemail[$res['groupname']] : '') . '" /></span></div>';
						}
						else
						{
							if (isset($ilance->GPC['paymethodoptions']) AND is_serialized($ilance->GPC['paymethodoptions']))
							{
								$paymethodopts = unserialize($ilance->GPC['paymethodoptions']);
								if (isset($paymethodopts["$res[groupname]"]) AND $paymethodopts["$res[groupname]"])
								{
									$onload .= 'toggle_show(\'cb_paymethodoptionsemail_' . $res['groupname'] . '\'); ';
									$gatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodoptions_' . $res['groupname'] . '"><input type="checkbox" name="paymethodoptions[' . $res['groupname'] . ']" id="paymethodoptions_' . $res['groupname'] . '" value="1" checked="checked" onclick="toggle_tr(\'cb_paymethodoptionsemail_' . $res['groupname'] . '\')" /> ' . ucfirst($res['groupname']) . '</label> <span style="display:none" id="cb_paymethodoptionsemail_' . $res['groupname'] . '">&nbsp;&nbsp;&nbsp;<span class="smaller blue">' . $phrase['_and_my_email_for_accepting_payments_through_this_gateway_is'] . '</span> &nbsp;&nbsp;<input type="text" name="paymethodoptionsemail[' . $res['groupname'] . ']" id="paymethodoptionsemail_' . $res['groupname'] . '" class="input" size="35" value="' . (isset($paymethodoptionsemail[$res['groupname']]) ? $paymethodoptionsemail[$res['groupname']] : '') . '" /></span></div>';
								}
								else
								{
									$gatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodoptions_' . $res['groupname'] . '"><input type="checkbox" name="paymethodoptions[' . $res['groupname'] . ']" id="paymethodoptions_' . $res['groupname'] . '" value="1" onclick="toggle_tr(\'cb_paymethodoptionsemail_' . $res['groupname'] . '\')" /> ' . ucfirst($res['groupname']) . '</label> <span style="display:none" id="cb_paymethodoptionsemail_' . $res['groupname'] . '">&nbsp;&nbsp;&nbsp;<span class="smaller blue">' . $phrase['_and_my_email_for_accepting_payments_through_this_gateway_is'] . '</span> &nbsp;&nbsp;<input type="text" name="paymethodoptionsemail[' . $res['groupname'] . ']" id="paymethodoptionsemail_' . $res['groupname'] . '" class="input" size="35" value="' . (isset($paymethodoptionsemail[$res['groupname']]) ? $paymethodoptionsemail[$res['groupname']] : '') . '" /></span></div>';
								}
							}
						}
                                                $num++;
                                        }
                                }
                        }
                        
                        if ($num == 0)
                        {
                                $show['nodirectpaymentgateways'] = true;
                        }
                }

                $html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
                if ($ilconfig['escrowsystem_enabled'])
                {
                        $html .= '<tr id="enableescrowrow" class="alt1"><td width="1%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="checkbox" id="enableescrow1" name="filter_escrow" ' . $escrow1 . ' value="1" /></td><td align="left"><label for="enableescrow1">' . $phrase['_enable_secure_escrow_trading_for_this_project'] . ' ' . $escrowfee . '</label></td></tr>';
                }
		
                if ($show['nodirectpaymentgateways'] == false AND $cattype == 'product')
                {
                        $html .= '
                        <tr class="alt1">
                                <td width="1%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="checkbox" id="enableescrow3" name="filter_gateway" ' . $escrow3 . ' value="1" onclick="toggle_tr(\'gatewaymethods\');" /></td>
                                <td align="left" valign="top"><label for="enableescrow3">' . $phrase['_i_would_like_winning_bidders_or_buyers_to_pay_immediately'] . '</label><div id="gatewaymethods" style="display:none"><div style="padding-top:12px">' . $gatewaypulldowns . '</div></div></td>
                        </tr>';
                }
                
                $html .= '<tr><td valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="checkbox" id="enableescrow2" name="filter_offline" ' . $escrow2 . ' value="1" onclick="toggle_tr(\'paymentmethods\');';
		
                $html .= '" /></td><td align="left" valign="top"><label for="enableescrow2">' . (($cattype == 'product')
			? $phrase['_i_prefer_not_to_use_secure_escrow_trading_for_this_project_payments_made_outside_marketplace']
			: $phrase['_payments_to_awarded_bidders_will_be_conducted_offline']) . '</label><div id="paymentmethods" style="display:none"><div style="padding-top:12px">' . $this->print_payment_method('paymethod', 'paymethod', true) . '</div></div></td>
                </tr>
                </table>
                <div style="padding-bottom:7px"></div>';
                
                ($apihook = $ilance->api('print_escrow_filter_end')) ? eval($apihook) : false;
                
                return $html;
        }
        
        /**
        * Function to print the payment method options for a select box
        *
        * @param       integer       selected option
        * @param       string        checkbox fieldname
        * @param       string        show checkboxes for output? (default false)
        * 
        * @return      string        HTML representation of the pulldown <option>
        */
	function print_payment_method_options($selected = '', $cbfieldname = '', $checkboxes = false)
        {
                global $ilance, $phrase;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT id, title
                        FROM " . DB_PREFIX . "payment_methods
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($checkboxes == false)
				{
					$bit = (isset($selected) AND isset($phrase["$res[title]"]) AND $selected == $phrase["$res[title]"]) ? 'selected="selected"' : '';
					$html .= '<option value="' . $phrase["$res[title]"] . '" ' . $bit . '>' . $phrase["$res[title]"] . '</option>';
				}
				else
				{
					$bit = (isset($selected) AND isset($phrase["$res[title]"]) AND $selected == $phrase["$res[title]"])
						? 'checked="checked"'
						: '';
						
					//if (isset($ilance->GPC['cmd']) AND ($ilance->GPC['cmd'] == 'new-item' OR $ilance->GPC['cmd'] == 'selling' AND $ilance->GPC['mode'] == 'bulk'))
					if (isset($ilance->GPC['cmd']) AND ($ilance->GPC['cmd'] == 'new-rfp') OR ($ilance->GPC['cmd'] == 'new-item' OR $ilance->GPC['cmd'] == 'selling' AND $ilance->GPC['mode'] == 'bulk'))
					{
						$html .= '<div style="padding-top:4px"><label for="cb_' . str_replace(' ', '', $phrase["$res[title]"]) . '"><input type="checkbox" id="cb_' . str_replace(' ', '', $phrase["$res[title]"]) . '" name="' . $cbfieldname . '[]" value="' . $phrase["$res[title]"] . '" ' . $bit . ' /> ' . $phrase["$res[title]"] . '</label></div>';
					}
					else
					{
						if (isset($ilance->GPC['paymethod']) AND is_serialized($ilance->GPC['paymethod']))
						{
							$paymethodopts = unserialize($ilance->GPC['paymethod']);
							if (isset($paymethodopts) AND is_array($paymethodopts) AND isset($phrase["$res[title]"]) AND in_array($phrase["$res[title]"], $paymethodopts))
							{
								$html .= '<div style="padding-top:4px"><label for="cb_' . str_replace(' ', '', $phrase["$res[title]"]) . '"><input type="checkbox" id="cb_' . str_replace(' ', '', $phrase["$res[title]"]) . '" name="' . $cbfieldname . '[]" value="' . $phrase["$res[title]"] . '" checked="checked" /> ' . $phrase["$res[title]"] . '</label></div>';
							}
							else
							{
								$html .= '<div style="padding-top:4px"><label for="cb_' . str_replace(' ', '', $phrase["$res[title]"]) . '"><input type="checkbox" id="cb_' . str_replace(' ', '', $phrase["$res[title]"]) . '" name="' . $cbfieldname . '[]" value="' . $phrase["$res[title]"] . '" /> ' . $phrase["$res[title]"] . '</label></div>';
							}
						}	
					}
				}
			}
                }
                
                return $html;        
        }
        
        /**
        * Function to print the payment method input field to allow the poster to enter delivery instructions.
        *
        * @return      string        HTML representation of the payment method form elements
        */
        function print_payment_method($fieldname = 'paymethod', $id = 'paymethod', $checkboxes = false)
        {
                global $ilance, $ilconfig, $phrase;
                
                $selected = '';
		if ($checkboxes == false)
		{
			if (isset($ilance->GPC["$fieldname"]) AND !empty($ilance->GPC["$fieldname"]))
			{
				$selected = $ilance->GPC["$fieldname"];
			}
			
			$html = '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
			$html .= $this->print_payment_method_options($selected);
			$html .= '</select>';
		}
		else
		{
			$html = $this->print_payment_method_options($selected, $fieldname, $checkboxes);	
		}
                
                return $html;
        }
        
        /**
        * Function to print the auction event type form filtering options.
        *
        * @return      string        HTML representation of the payment method form elements
        */
        function print_event_type_filter($cattype = '', $fieldname = 'project_details', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase, $onload, $js, $retailprice, $invitelist, $memberinvitelist;
                
                $event1 = $event3 = $event4 = $event5 = '';
                if (!empty($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] == 'public')
                {
                        $event1 = 'checked="checked"';
                }
                else if (!empty($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] == 'invite_only')
                {
                        $event3 = 'checked="checked"';
                        $onload .= 'duration_switch(3); ';
                        
                        // at this point, the auction poster could be adding new members
                        // to the currently selected invitation list so lets find out
                        $invitemessage = isset($ilance->GPC['invitemessage']) ? handle_input_keywords($ilance->GPC['invitemessage']) : '';
                }
                else if (!empty($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] == 'realtime')
                {
                        $event4 = 'checked="checked"';
                        $onload .= 'duration_switch(2); toggle_show(\'scheduledate\'); ';
                }
                else if (!empty($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] == 'unique' AND $cattype == 'product' AND $ilconfig['enable_uniquebidding'] AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$event5 = 'checked="checked"';
			$onload .= 'duration_switch(1); toggle_show(\'uniquebid\'); toggle_hide(\'showsellingformat\'); ';
			
			if ($ilconfig['escrowsystem_enabled'])
			{
				$onload .= 'toggle_hide(\'enableescrowrow\'); ';
			}
			if ($ilconfig['enablenonprofits'])
			{
				$onload .= 'toggle_hide(\'donations\'); ';
			}
		}
                else
                {
                        // defaults
                        $event1 = 'checked="checked"';
                        $event3 = $event4 = $event5 = '';
                }
                
                // populate realtime bidding scheduled date pulldown data
                
                $ilance->GPC['year'] = isset($ilance->GPC['year']) ? intval($ilance->GPC['year']) : '';
                $ilance->GPC['month'] = isset($ilance->GPC['month']) ? intval($ilance->GPC['month']) : '';
                $ilance->GPC['day'] = isset($ilance->GPC['day']) ? intval($ilance->GPC['day']) : '';
                $ilance->GPC['hour'] = isset($ilance->GPC['hour']) ? intval($ilance->GPC['hour']) : '';
                $ilance->GPC['min'] = isset($ilance->GPC['min']) ? intval($ilance->GPC['min']) : '';
                $ilance->GPC['sec'] = isset($ilance->GPC['sec']) ? intval($ilance->GPC['sec']) : '';
                
                $jsuniquebid = ($ilconfig['enable_uniquebidding'] AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
			? 'toggle_free(\'uniquebid\');'
			: '';
                
		$html = '
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
		<tr class="alt1">
			<td width="3%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="public" value="public" ' . $event1 . ' onclick="duration_switch(1); toggle_paid(\'showsellingformat\'); toggle_free(\'uniquebid\'); toggle_show(\'enableescrowrow\'); toggle_show(\'donations\');" /></td>
			<td width="97%"><label for="public"><strong>' . $phrase['_public_event'] . '</strong> : <span class="gray">' . $phrase['_publically_available_auction'] . '</span></label></td>
		</tr>';
                
                if ($cattype == 'service')
                {
                        $html .= '
			<tr class="alt1">
				<td valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="invite_only" value="invite_only" ' . $event3 . ' onclick="duration_switch(3); toggle_paid(\'showsellingformat\'); toggle_free(\'uniquebid\'); toggle_show(\'enableescrowrow\'); toggle_show(\'donations\');" /></td>
				<td width="100%"><label for="invite_only"><strong>' . $phrase['_invitation_event'] . '</strong> : <!-- <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invite.gif" border="0" alt="" />--><span class="gray">' . $phrase['_invite_vendors_to_place_bids_on_your_auction'] . '</span></label></td>
			</tr>';
                }
                
                if ($ilconfig['enable_uniquebidding'] AND $cattype == 'product')
                {
                        $html .= '<tr class="alt1">';        
                }
                else
                {
                        $html .= '<tr>';
                }
                
                $html .= '
			<td valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="realtime" value="realtime" ' . $event4 . ' onclick="duration_switch(2); toggle_tr(\'scheduledate\'); toggle_paid(\'showsellingformat\'); toggle_free(\'uniquebid\'); toggle_show(\'enableescrowrow\'); toggle_show(\'donations\');" /></td>
			<td><label for="realtime"><strong>' . $phrase['_invitation_event_realtime'] . '</strong> : <!-- <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'realtime.gif" border="0" alt="" />--><span class="gray">' . $phrase['_invite_vendors_to_place_bids_on_your'] . '</span></label>

				<div id="scheduledate" style="display:none">
                                <div style="padding-top:7px"></div>
                                
                                <div>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding:0px 2px 15px 0px;" dir="' . $ilconfig['template_textdirection'] . '">
                                <tr>
                                <td>
                                <div class="grayborder" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_gray_1x1000.gif) repeat-x;"><div class="n"><div class="e"><div class="w"></div></div></div><div>
                                <table border="0" cellpadding="0" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
                                <tr>
                                        <td style="padding-left:5px;" valign="top"></td>
                                        <td><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer.gif" width="5" height="1" /></td>
                                        <td style="padding-right:5px;">
                                        
                                        <div style="padding-bottom:6px"><strong>' . $phrase['_schedule_a_start_date_and_time'] . '</strong></div>
                                        
                                        <table style="width:100%" border="0" cellspacing="3" cellpadding="2" dir="' . $ilconfig['template_textdirection'] . '">
                                        <tr>
                                                <td width="100%">
        
                                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
                                                        <tr>
                                                                <td align="center">' . $this->year($ilance->GPC['year']) . '&nbsp;</td>
                                                                <td align="center">' . $this->month($ilance->GPC['month']) . '&nbsp;</td>
                                                                <td align="center">' . $this->day($ilance->GPC['day']) . '</td>
                                                                <td align="center">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                                                <td align="center">' . $this->hour($ilance->GPC['hour']) . '&nbsp;</td>
                                                                <td align="center">' . $this->min($ilance->GPC['min']) . '&nbsp;</td>
                                                                <td align="center">' . $this->sec($ilance->GPC['sec']) . '</td>
                                                        </tr>
                                                        <tr>
                                                                <td align="left" class="smaller">' . $phrase['_year'] . '</td>
                                                                <td align="left" class="smaller">' . $phrase['_month'] . '</td>
                                                                <td align="left" class="smaller">' . $phrase['_day'] . '</td>
                                                                <td align="left" class="smaller">&nbsp;</td>
                                                                <td align="left" class="smaller">' . $phrase['_hour'] . '</td>
                                                                <td align="left" class="smaller">' . $phrase['_min'] . '</td>
                                                                <td align="left" class="smaller">' . $phrase['_sec'] . '</td>
                                                        </tr>
                                                        </table>
        
                                                </td>
                                        </tr>
                                        </table>
                                        
                                        </td>
                                </tr>
                                </table>
                                </div><div class="s"><div class="e"><div class="w"></div></div></div></div>
                                </td>
                                </tr>
                                </table>
                                </div>
                                
				
				</div>

			</td>
		</tr>';
                
                if ($cattype == 'product')
                {
                        $retailprice = '';
                        if (isset($ilance->GPC['retailprice']))
                        {
                                $retailprice = sprintf("%01.2f", $ilance->GPC['retailprice']);
                        }
                        
                        $uniquebidcount = '0';
                        if (isset($ilance->GPC['uniquebidcount']))
                        {
                                $uniquebidcount = intval($ilance->GPC['uniquebidcount']);
                        }
                        
                        // ## UNIQUE BIDDING ###################################
                        $cb_uniquebid = '';
                        if ($ilconfig['enable_uniquebidding'])
                        {
                                // are we logged in as admin?
                                if ($_SESSION['ilancedata']['user']['isadmin'] == '0')
                                {
                                        $cb_uniquebid = 'disabled="disabled"';    
                                }
                                $html .= '
                                <tr> 
                                        <td width="1%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="unique" value="unique" ' . $event5 . ' ' . $cb_uniquebid . ' onclick="duration_switch(1); toggle_tr(\'uniquebid\'); toggle_tr(\'showsellingformat\'); toggle_hide(\'enableescrowrow\'); toggle_hide(\'donations\'); " /></td>
                                        <td width="99%"> <label for="unique"><strong>' . $phrase['_lowest_unique_bid_event'] . '</strong> : <span class="gray">' . $phrase['_bidders_try_to_place_the_lowest_unique_bid_for_your_item_that_is_unmatched_by_any_other_bidder'] . '</span></label> 
                                                <div id="uniquebid" style="display:none">
                                                
                                                <div style="padding-top:7px"></div>
                                                <div>
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding:0px 2px 15px 0px;" dir="' . $ilconfig['template_textdirection'] . '">
                                                <tr>
                                                <td>
                                                <div class="grayborder" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_gray_1x1000.gif) repeat-x;"><div class="n"><div class="e"><div class="w"></div></div></div><div>
                                                
                                                <table style="width:100%" border="0" cellspacing="3" cellpadding="6" dir="' . $ilconfig['template_textdirection'] . '">
                                                <tr>
                                                        <td>
                                                        
                                                        <div><strong>' . $phrase['_retail_price'] . '</strong></div>
                                                        <div class="gray">' . $phrase['_when_conducting_auction_via_unique_bidding_there_is_no_starting_price'] . '</div>
                                                        <div style="padding-top:3px"><span id="retailprice_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span> <input type="text" name="retailprice" value="' . $retailprice . '" size="8" style="font-family: verdana" /> <span id="retailprice_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span></div>
                                                        <hr size="1" width="100%" style="color:#cfcfcf; margin-top:6px; margin-bottom:6px" />
                                                        <div style="padding-top:7px"><strong>' . $phrase['_bids_until_lowest_unique_bid_event_ends'] . '</strong></div>
                                                        <div class="gray">' . $phrase['_if_you_would_like_to_end_the_auction_based_on_a_number_of_bids'] . '</div>
                                                        <div style="padding-top:3px"><input type="text" name="uniquebidcount" value="' . $uniquebidcount . '" size="5" /></div>
                                                        <hr size="1" width="100%" style="color:#cfcfcf; margin-top:6px; margin-bottom:6px" />
                                                        <div><!--<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nonprofits.gif" border="0" alt="" /> --><strong>Benefiting Nonprofit</strong></div>
                                                        <div class="gray">' . $phrase['_due_to_the_nature_of_this_listing_style_100_of_the_lowest_unique_winning_bidders_amount'] . '</div>
                                                        <div style="padding-top:3px">' . print_charities_pulldown(0) . '</div>
                                                        
                                                        </td>
                                                </tr>
                                                </table>
                                                
                                                </div><div class="s"><div class="e"><div class="w"></div></div></div></div>
                                                </td>
                                                </tr>
                                                </table>
                                                </div>
                                                
                                                </div>
                                        </td>
                                </tr>';
                        }
                }
                
                $html .= '</table>';
                
                ($apihook = $ilance->api('print_event_type_filter')) ? eval($apihook) : false;
                
                return $html;
        }
        
        /**
        * Function to print a duration pulldown menu.
        *
        * @param       string        selected duration value (optional)
        *
        * @return      string        HTML representation of the duration pulldown menu
        */
        function duration($fieldname = 'duration', $disabled = false)
        {
                global $ilance;
                
                $duration = isset($ilance->GPC['duration']) ? intval($ilance->GPC['duration']) : '';
                
                $html = '<select ' . ($disabled ? 'disabled="disabled"' : '') . ' name="' . $fieldname . '" style="font-family: verdana">';
                for ($i = 1; $i <= 30; $i++)
                {
                        if ($i < 10)
                        {
                                $b = $i;
                                $i = "0$i";
                        }
                        else
                        {
                                $b = $i;
                        }
                        if (isset($duration) AND $duration == $i)
                        {
                                $html .= '<option value="' . $i . '" selected="selected">' . $b . '</option>';
                        }
                        else 
                        {
                                $html .= '<option value="' . $i . '">' . $b . '</option>';
                        }
                }
                
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print a duration logic menu.
        *
        * @param       string        selected duration value (optional)
        *
        * @return      string        HTML representation of the duration logic form elements
        */
        function print_duration_logic($fieldname = 'duration_unit', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase, $headinclude;
                
                $duration1 = 'checked="checked"';
                $duration2 = $duration3 = '';
                
                $preheadinclude = 'document.ilform.' . $fieldname . '[1].checked=true';
                
                if (isset($ilance->GPC['duration_unit']) AND $ilance->GPC['duration_unit'] == 'D')
                {
                        $duration1 = 'checked="checked"';
                }
                else if (isset($ilance->GPC['duration_unit']) AND $ilance->GPC['duration_unit'] == 'H')
                {
                        $duration2 = 'checked="checked"';
                }
                else if (isset($ilance->GPC['duration_unit']) AND $ilance->GPC['duration_unit'] == 'M')
                {
                        $duration3 = 'checked="checked"';
                }
                
                // specific javascript includes for realtime duration features
                $headinclude .= '
<script type="text/javascript">
<!--
function duration_switch(val)
{
        if (val == \'1\')
        {
                toggle_free(\'scheduledate\')
        }        
        if (val == \'2\')
        {
                toggle_free(\'scheduledate\')
        }        
        if (val == \'3\')
        {
                toggle_free(\'scheduledate\')
        }
}
//-->
</script>';
        
		$html = '
		<table width="1%" border="0" align="left" cellpadding="0" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
		<tr>
			<td width="6%"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="rb_days" type="radio" name="' . $fieldname . '" value="D" ' . $duration1 . ' /></td>
			<td width="17%"><label for="rb_days"><strong>' . $phrase['_days'].'</strong></label>&nbsp;&nbsp;</td>
			<td width="2%"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="rb_hours" type="radio" name="' . $fieldname . '" value="H" ' . $duration2 . ' /></td>
			<td width="19%"><label for="rb_hours"><strong>' . $phrase['_hours'] . '</strong></label>&nbsp;&nbsp;</td>
			<td width="6%"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="rb_mins" type="radio" name="' . $fieldname . '" value="M" ' . $duration3 . ' /></td>
			<td width="50%"><label for="rb_mins"><strong>' . $phrase['_mins'] . '</strong></label></td>
		</tr>
		</table>';                
                
                return $html;
        }
        
        /**
        * Function to print the invitation boxes and special javascript to let users add more than one row for
        * multiple email addresses
        *
        * @param       string        
        *
        * @return      string        HTML representation of the bid privacy radio options
        */
        function print_invitation_controls($cattype = 'service')
        {
                global $ilance, $ilconfig, $phrase, $headinclude;
                
                $invitemessage = $sendinvites = '';
                
                // special javascript includes for handling new email rows for invitation control
                $headinclude .= '
<script type="text/javascript" language="Javascript">
<!--
function emailcheck(str)
{
        var at = "@"
        var dot = "."
        var lat = str.value.indexOf(at)
        var lstr = str.value.length
        var ldot = str.value.indexOf(dot)
        
        if (str.value.indexOf(at) == -1)
        {
                alert("' . $phrase['_invalid_email'] . '");
                str.value = \'\';
                return false;
        }
        if (str.value.indexOf(at) == -1 || str.value.indexOf(at) == 0 || str.value.indexOf(at) == lstr)
        {
                alert("' . $phrase['_invalid_email'] . '");
                str.value = \'\';
                return false;
        }
        if (str.value.indexOf(dot) == -1 || str.value.indexOf(dot) == 0 || str.value.indexOf(dot) == lstr)
        {
                alert("' . $phrase['_invalid_email'] . '");
                str.value = \'\';
                return false
        }
        if (str.value.indexOf(at,(lat+1)) != -1)
        {
                alert("' . $phrase['_invalid_email'] . '");
                str.value = \'\';
                return false;
        }
        if (str.value.substring(lat-1,lat) == dot || str.value.substring(lat+1, lat+2) == dot)
        {
                alert("' . $phrase['_invalid_email'] . '");
                str.value = \'\';
                return false;
        }
        if (str.value.indexOf(dot,(lat+2)) == -1)
        {
                alert("' . $phrase['_invalid_email'] . '");
                str.value = \'\';
                return false;
        }
        if (str.value.indexOf(" ") != -1)
        {
                alert("' . $phrase['_invalid_email'] . '");
                str.value = \'\';
                return false;
        }        
        return true					
}
function rem_input_field()
{
        var i = fetch_js_object(\'invite_emails\');
        if (i.rows.length > 1)
        {
                invite_emails.removeChild(invite_emails.lastChild);
        }
}
function add_input_field() 
{
        var tbody = fetch_js_object(\'invite_emails\');
        var ctr = tbody.getElementsByTagName(\'input\').length + 1;
        var input;
        ctr = ctr / 2;
        if (ctr > 5) 
        {
                alert_js(phrase[\'_the_maximum_number_of_people_you_are_sending_this_auction_event_to_has_been_reached\']);
        }
        else
        {
                if (document.all)
                {
                        input = document.createElement(\'<input name="invitelist[email][]" />\');
                        input2 = document.createElement(\'<input name="invitelist[name][]" />\');
                }
                else
                {
                        input = document.createElement("input");
                        input2 = document.createElement("input");
                        
                        input.name = "invitelist[email][]";
                        input2.name = "invitelist[name][]";					
                }
                
                input.id = "inviteemails_" + ctr;
                input.type = "text";
                input.value = "";
                input.className = "input";
                input.size = "25";
                
                input2.id = "invitenames_" + ctr;
                input2.type = "text";
                input2.value = "";
                input2.className = "input";
                
                var tdText1 = document.createTextNode(\'' . $phrase['_email'] . ':\');
                var tdText2 = document.createTextNode(\'' . $phrase['_first_name'] . ':\');
                
                var cell = document.createElement("td");
                    cell.style.height = "30px";
                    cell.appendChild(tdText1);
                    cell.appendChild(document.createElement("BR"));
                    cell.appendChild(input);
                
                var cell3 = document.createElement("td");
                    cell3.style.height = "0px";
                    cell3.appendChild(document.createElement("NBSP"));
                
                var cell2 = document.createElement("td");
                    cell2.style.height = "30px";
                    cell2.appendChild(tdText2);
                    cell2.appendChild(document.createElement("BR"));
                    cell2.appendChild(input2);
                
                var row = document.createElement("tr");
                    row.appendChild(cell);
                    row.appendChild(cell3);
                    row.appendChild(cell2);
                    tbody.appendChild(row);
		    
                window.document.ilform.count.value = ctr;
        }
}
//-->
</script>';
        
                // invitation message
                $invitemessage = ((isset($ilance->GPC['invitemessage']) AND !empty($ilance->GPC['invitemessage']))
			? strip_vulgar_words($ilance->GPC['invitemessage'])
			: '');
			
                $invitelist_row = '<tr><td height="30">' . $phrase['_email'] . '<br /><input onblur="javascript: emailcheck(this)" size="25" name="invitelist[email][]" type="text" class="input" id="inviteemails_1" /></td><td>&nbsp;&nbsp;</td><td height="30">' . $phrase['_first_name'] . '<br /><input name="invitelist[name][]" type="text" class="input" id="invitenames_1" /></td></tr>';
                
                // re-populate invitation list for reverse auctions only         
                if (!empty($ilance->GPC['invitelist']) AND is_array($ilance->GPC['invitelist']))
                {
                        $count = 1;
                        foreach ($ilance->GPC['invitelist']['email'] AS $key => $emailaddress)
                        {
                                if (!empty($emailaddress) AND is_valid_email($emailaddress))
                                {
                                        $invitelist_row .= '<tr id="inviterow_' . $count . '"><td height="30"><input size="25" name="invitelist[email][]" value="' . $emailaddress . '" type="text" class="input" id="inviteemails_' . $count . '" /></td><td>&nbsp;&nbsp;</td><td height="30"><input name="invitelist[name][]" value="' . $ilance->GPC['invitelist']['name']["$key"] . '" type="text" class="input" id="invitenames_' . $count . '" /></td></tr>';
                                        $count++;
                                }
                        }
                        if (empty($invitelist_row))
                        {
                                $invitelist_row = '<tr id="inviterow_' . $count . '"><td height="30"><input onblur="javascript: emailcheck(this)" size="25" name="invitelist[email][]" type="text" class="input" id="inviteemails_1" /></td><td>&nbsp;&nbsp;</td><td height="30"><input name="invitelist[name][]" type="text" class="input" id="invitenames_1" /></td></tr>';       
                        }
                }
                
                // additionally, we'll display any invited users previously selected from the search results page and/or single member invtations
                $inviteduserlist = '';
                if (!empty($_SESSION['ilancedata']['tmp']['invitations']) AND is_serialized($_SESSION['ilancedata']['tmp']['invitations']) AND $ilconfig['globalauctionsettings_serviceauctionsenabled'] AND $cattype == 'service')
                {
                        $invitedusers = unserialize($_SESSION['ilancedata']['tmp']['invitations']);
                        $invitedcount = count($invitedusers);
                        if ($invitedcount > 0)
                        {
                                foreach ($invitedusers AS $userid)
                                {
                                        $inviteduserlist .= '<span class="blue" style="font-size:13px"><label><input type="checkbox" name="invitedmember[]" value="' . $userid . '" checked="checked" /> <strong>' . fetch_user('username', $userid) . '</strong></label></span>, ';
                                }
				
                                if (!empty($inviteduserlist))
                                {
                                        $inviteduserlist = '<hr size="1" width="100%" style="color:#cccccc" /><div class="black"><strong>' . $phrase['_users_from_the_marketplace_you_are_invited_will_appear_below'] . '</strong></div><div style="padding-top:1px" class="smaller gray">' . $phrase['_use_the_checkboxes_to_confirm_or_remove_invited_experts'] . '</div><div style="padding-top:6px"><div style="padding-right:12px; padding-top:9px; padding-bottom:6px">' . mb_substr($inviteduserlist, 0, -2) . '</div></div>';
                                }
                        }
                }
                
		$html = '
		<table border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '"><tbody id="invite_emails">' . $invitelist_row . '</tbody></table>
                <div style="padding-bottom:7px; padding-top:12px" class="smaller gray">
			<span class="smaller blue"><a href="javascript:void(0)" onclick="add_input_field();" id="add">' . $phrase['_add_new_email_contact'] . '</a></span>&nbsp;&nbsp;&nbsp;<span class="smaller gray">|</span>&nbsp;&nbsp;&nbsp;<span class="blue"><a href="javascript:void(0)" onclick="rem_input_field();" id="rem">' . $phrase['_remove_last_contact'] . '</a></span></div>
			<div style="padding-top:6px"></div>' . $inviteduserlist . '
			
			<input name="count" type="hidden" id="count" value="1" />
			<hr size="1" width="100%" style="color:#cccccc" />
			
			<div style="padding-top:3px; padding-bottom:3px"><strong>' . $phrase['_enter_invitation_message_to_bidders'] . '</strong></div>
			<div>
			<table cellpadding="0" cellspacing="0" border="0" dir="' . $ilconfig['template_textdirection'] . '">
			<tr>
				<td align="right" height="25">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
					<tr>
						<td align="left" class="smaller">' . $phrase['_plain_text_only_bbcode_is_currently_not_in_use_for_this_field'] . '</td>
						<td>
								<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'invitemessage\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . $phrase['_decrease_size'] . '" border="0" /></a></div>
								<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'invitemessage\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . $phrase['_increase_size'] . '" border="0" /></a></div>
						</td>
						<td style="padding-right:15px"></td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td><textarea name="invitemessage" id="invitemessage" style="width:380px; height:50px; padding:3px;" wrap="physical" class="wysiwyg">' . $invitemessage . '</textarea></td>
			</tr>
			</table>
		</div>';
                
                return $html;        
        }
        
        /**
        * Function to print the auction's keywords / tags used for search engine optimization and other functions
        * like displaying ads relevant to the auctions or categories
        *
        * @param       string        selected duration value (optional)
        *
        * @return      string        HTML representation of the keywords input
        */
        function print_keywords_input($fieldname = 'keywords', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                
                $keywords = '';
                if (isset($ilance->GPC['keywords']) AND !empty($ilance->GPC['keywords']))
                {
                        //$keywords = strip_vulgar_words($ilance->GPC['keywords']);
                        $keywords = $ilance->GPC['keywords'];
                }
                
                $html = '<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" class="input" name="' . $fieldname . '" maxsize="75" value="' . $keywords . '" title="" style="width:580px" />';
                
                return $html;
        }
        
        /**
        * Function to print the auction's title input 
        *
        * @param       string        form fieldname
        *
        * @return      string        HTML representation of the keywords input
        */
        function print_title_input($fieldname = 'project_title', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                
                $project_title = '';
                if (isset($ilance->GPC['project_title']) AND !empty($ilance->GPC['project_title']))
                {
                        //$project_title = strip_vulgar_words($ilance->GPC['project_title']);
                        $project_title = $ilance->GPC['project_title'];
                }

                $html = '<input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="' . $fieldname . '" type="text" class="input" name="' . $fieldname . '" maxsize="75" value="' . $project_title . '" style="width:580px" /> <img name="' . $fieldname . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="" />';
                
                return $html;
        }
        
        /**
        * Function to print the auction's title input 
        *
        * @param       string        form fieldname
        *
        * @return      string        HTML representation of the keywords input
        */
        function print_video_description_input($fieldname = 'description_videourl', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                
                $description_videourl = '';
                if (isset($ilance->GPC['description_videourl']) AND !empty($ilance->GPC['description_videourl']))
                {
                        $description_videourl = $ilance->GPC['description_videourl'];
                }

                $html = '<input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="' . $fieldname . '" type="text" class="input" name="' . $fieldname . '" maxsize="75" value="' . $description_videourl . '" style="width:580px" />';
                
                return $html;
        }
        
        /**
        * Function to print the auction's extend features after the listing was posted (update mode)
        *
        * @param       string        form fieldname
        *
        * @return      string        HTML representation of the keywords input
        */
        function print_extend_auction($fieldname = 'extend')
        {
                global $ilance, $ilconfig, $phrase, $headinclude;
                
                $html = '
		<div style="padding-top:6px"></div>
                <table cellpadding="0" cellspacing="3" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                <tr> 
                        <td width="1%" valign="top"><input type="radio" name="' . $fieldname . '" id="extend1" value="0" checked="checked" /></td>
                        <td><label for="extend1"><strong>' . $phrase['_keep_current_duration_as_is'] . '</strong></label></td>
                        <td width="1%" valign="top"><input type="radio" name="' . $fieldname . '" id="extend3" value="3" /></td>
                        <td><label for="extend3">' . $phrase['_extend_for_3_days'] . '</label></td>
                </tr>
                <tr> 
                        <td width="1%" valign="top"><input type="radio" name="' . $fieldname . '" id="extend2" value="1" /></td>
                        <td><label for="extend2">' . $phrase['_extend_for_1_day'] . '</label></td>
                        <td width="1%" valign="top"><input type="radio" name="' . $fieldname . '" id="extend4" value="7" /></td>
                        <td><label for="extend4">' . $phrase['_extend_for_7_days'] . '</label></td>
                </tr> 
                </table>';
                
                return $html;
        }
        
        /**
        * Function to print the auction's additional information box
        *
        * @param       string        form fieldname
        *
        * @return      string        HTML representation of the additional info textarea
        */
        function print_additional_info_input($fieldname = 'additional_info', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                
                $additional_info = (isset($ilance->GPC['additional_info']) AND !empty($ilance->GPC['additional_info'])) ? strip_vulgar_words($ilance->GPC['additional_info']) : '';
                
                $html = '
                <div class="ilance_wysiwyg">
                <table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
                <tr>
                <td class="wysiwyg_wrapper" align="right" height="25">

                                        <table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                                        <tr>
                                                <td width="100%" align="left" class="smaller">' . $phrase['_plain_text_only_bbcode_is_currently_not_in_use_for_this_field'] . '</td>
                                                <td>
                                                                <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $fieldname . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . $phrase['_decrease_size'] . '" border="0" /></a></div>
                                                                <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $fieldname . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . $phrase['_increase_size'] . '" border="0" /></a></div>
                                                </td>
                                                <td style="padding-right:15px"></td>
                                        </tr>
                                        </table>
                </td>
                </tr>
                        <tr>
                                <td><textarea ' . ($disabled ? 'disabled="disabled"' : '') . ' name="' . $fieldname . '" id="' . $fieldname . '" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $additional_info . '</textarea></td>
                        </tr>
                </table>
                </div>';
                
                return $html;
        }
        
        /**
        * Function to print a the bid privacy form options (open, sealed, blind or full)
        *
        * @param       string        selected duration value (optional)
        *
        * @return      string        HTML representation of the bid privacy radio options
        */
        function print_bid_privacy($fieldname = 'bid_details', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                
                $privacy1 = 'checked="checked"';
		$privacy2 = $privacy3 = $privacy4 = '';
		if (!empty($ilance->GPC['bid_details']) AND $ilance->GPC['bid_details'] == 'open')
		{
			$privacy1 = 'checked="checked"';
		}
		else if (!empty($ilance->GPC['bid_details']) AND $ilance->GPC['bid_details'] == 'sealed')
		{
			$privacy2 = 'checked="checked"';
		}
		else if (!empty($ilance->GPC['bid_details']) AND $ilance->GPC['bid_details'] == 'blind')
		{
			$privacy3 = 'checked="checked"';
		}
		else if (!empty($ilance->GPC['bid_details']) AND $ilance->GPC['bid_details'] == 'full')
		{
			$privacy4 = 'checked="checked"';
		}
                
                // subscription permission checkup for setting filter privacy
                $disabled1 = $disabled2 = $disabled3 = false;
                if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'cansealbids') == 'no')
                {
                        $disabled1 = true;        
                }
                if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'canblindbids') == 'no')
                {
                        $disabled2 = true;        
                }
                if ($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'canfullprivacybids') == 'no')
                {
                        $disabled3 = true;        
                }

		$html = '
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                <tr class="alt1">
                      <td width="3%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="open" value="open" ' . $privacy1 . ' ' . ($disabled ? 'disabled="disabled"' : '') . ' /></td>
                      <td width="97%"><label for="open"><strong>' . $phrase['_no_bid_privacy_enabled'] . '</strong> : <span class="gray">' . $phrase['_bidder_names_are_shown_bid_amounts_are_shown_and_listing_available_to_search_engines'] . '</span></label></td>
                </tr>
                <tr class="alt1">
                      <td valign="top"><input ' . (($disabled OR $disabled1) ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="sealed" value="sealed" ' . $privacy2 . ' ' . ($disabled ? 'disabled="disabled"' : '') . ' /></td>
                      <td><label for="sealed"><strong>' . $phrase['_sealed_bidding'] . '</strong> : <span class="gray">' . $phrase['_bidder_names_are_shown_bid_amounts_are_hidden_optional'] . '</span></label></td>
                </tr>
                <tr class="alt1">
                      <td valign="top"><input ' . (($disabled OR $disabled2) ? 'disabled="disabled"' : '') . ' type="radio" id="blind" name="' . $fieldname . '" value="blind" ' . $privacy3 . ' ' . ($disabled ? 'disabled="disabled"' : '') . ' /></td>
                      <td><label for="blind"><strong>' . $phrase['_blind_bidding'] . '</strong> : <span class="gray">' . $phrase['_bid_amounts_are_shown_bidder_names_are_hidden_optional'] . '</span></label></td>
                </tr>
                <tr>
                      <td valign="top"><input ' . (($disabled OR $disabled3) ? 'disabled="disabled"' : '') . ' type="radio" id="full" name="' . $fieldname . '" value="full" ' . $privacy4 . ' ' . ($disabled ? 'disabled="disabled"' : '') . ' /></td>
                      <td><label for="full"><strong>' . $phrase['_full_privacy_sealed_blind_bidding'] . '</strong> : <span class="gray">' . $phrase['_full_privacy_bidder_names_are_hidden_bid_amounts_are_sealed'] . '</span></label></td>
                </tr>
                </table>';
                
                return $html;
        }
        
        /**
        * Function to print the public message board visibility or hidden options for the auction poster.
        *
        * @return      string        HTML representation of the public board form elements
        */
        function print_public_board($fieldname = 'filter_publicboard', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                
                $publicboard1 = '';
                $publicboard2 = 'checked="checked"';
                if (!empty($ilance->GPC['filter_publicboard']) AND $ilance->GPC['filter_publicboard'])
                {
                        $publicboard1 = 'checked="checked"';
                        $publicboard2 = '';
                }
                
                $html = '
                <table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                <tr class="alt1">
                        <td width="1%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="' . $fieldname . '1" value="1" '.$publicboard1.' /></td>
                        <td width="100%"><label for="' . $fieldname . '1"><strong>' . $phrase['_public_message_board_enabled'] . '</strong> : <span class="gray">' . $phrase['_select_this_option_if_you_will_allow_a_public_message_board_environment_on_your_auction_listing_page'] . '</span></label></td>
                </tr>
                <tr>
                        <td valign="top"><input type="radio" ' . ($disabled ? 'disabled="disabled"' : '') . ' name="' . $fieldname . '" id="' . $fieldname . '0" value="0" '.$publicboard2.' /></td>
                        <td><label for="' . $fieldname . '0"><strong>' . $phrase['_public_message_board_disabled'] . '</strong> : <span class="gray">' . $phrase['_if_you_do_not_want_a_public_message_board_on_your_auction_listing_page_select_this_option'] . '</span></label></td>
                </tr>
                </table>';
                
                return $html;
        }
        
        /**
        * Function to print the available auction enhancement upsell options like bold, highlight background, featured, etc.
        *
        * @param       string        category type (service or product)
        * 
        * @return      string        HTML representation of the listing enhancement form options
        */
        function print_listing_enhancements($cattype = 'service', $extra = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                
                $sumfees = 0;
		$boldprice_fee = $ilance->currency->format(0);
		$highlite_fee = $ilance->currency->format(0);
		$featured_fee = $ilance->currency->format(0);
                $autorelist_fee = $ilance->currency->format(0);
		$showbold = $showhlite = $showfeatured = $showautorelist = 1;
                
                ($apihook = $ilance->api('print_listing_enhancements_start')) ? eval($apihook) : false;
                
                if ($ilconfig["{$cattype}upsell_boldactive"])
                {
                        if ($ilconfig["{$cattype}upsell_boldfees"])
                        {
                                $boldprice_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_boldfee"]);
                        }
                }
		else
		{
			$showbold = 0;
		}

		if ($ilconfig["{$cattype}upsell_highlightfees"])
                {
			$highlite_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_highlightfee"]);
		}
		else
		{
			$showhlite = 0;
		}

		if ($ilconfig["{$cattype}upsell_featuredfees"])
                {
			$featured_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_featuredfee"]);
		}
		else
		{
			$showfeatured = 0;
		}
                
                if ($ilconfig["{$cattype}upsell_autorelistfees"])
                {
			$autorelist_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_autorelistfee"]);
		}
		else
		{
			$showautorelist = 0;
		}
                
		$cb_bold = $cb_highlight = $cb_featured = $cb_autorelist = '';
                
                ($apihook = $ilance->api('print_listing_enhancements_middle')) ? eval($apihook) : false;
                
		if (isset($ilance->GPC['enhancements']))
		{
			foreach ($ilance->GPC['enhancements'] AS $enhancement => $value)
			{
                                // #### BOLD ###################################
				if (isset($enhancement) AND $enhancement == 'bold')
				{
					if ($ilconfig["{$cattype}upsell_boldfees"])
                                        {
						$boldprice_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_boldfee"]);
						$sumfees += $ilconfig["{$cattype}upsell_boldfee"];
					}
                                        if (isset($show['disableselectedenhancements']) AND $show['disableselectedenhancements'])
                                        {
                                                $cb_bold = 'checked="checked" disabled="disabled"';
                                        }
                                        else
                                        {
                                                $cb_bold = 'checked="checked"';        
                                        }
				}
                                
                                // #### BACKGROUND HIGHLIGHT ###################
				if (isset($enhancement) AND $enhancement == 'highlite')
				{
					if ($ilconfig["{$cattype}upsell_highlightfees"])
                                        {
						$highlite_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_highlightfee"]);
						$sumfees += $ilconfig["{$cattype}upsell_highlightfee"];
					}
                                        if (isset($show['disableselectedenhancements']) AND $show['disableselectedenhancements'])
                                        {
                                                $cb_highlight = 'checked="checked" disabled="disabled"';
                                        }
                                        else
                                        {
                                                $cb_highlight = 'checked="checked"';        
                                        }
				}
                                
                                // #### FEATURED ###############################
				if (isset($enhancement) AND $enhancement == 'featured')
				{
					if ($ilconfig["{$cattype}upsell_featuredfees"])
                                        {
						$featured_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_featuredfee"]);
						$sumfees += $ilconfig["{$cattype}upsell_featuredfee"];
					}
                                        if (isset($show['disableselectedenhancements']) AND $show['disableselectedenhancements'])
                                        {
                                                $cb_featured = 'checked="checked" disabled="disabled"';
                                        }
                                        else
                                        {
                                                $cb_featured = 'checked="checked"';
                                        }
				}
                                
                                // #### AUTO-RELIST ############################
				if (isset($enhancement) AND $enhancement == 'autorelist')
				{
					if ($ilconfig["{$cattype}upsell_autorelistfees"])
                                        {
						$autorelist_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_autorelistfee"]);
						$sumfees += $ilconfig["{$cattype}upsell_autorelistfee"];
					}
                                        if (isset($show['disableselectedenhancements']) AND $show['disableselectedenhancements'])
                                        {
                                                $cb_autorelist = 'checked="checked" disabled="disabled"';
                                        }
                                        else
                                        {
                                                $cb_autorelist = 'checked="checked"';        
                                        }
				}
                                
                                ($apihook = $ilance->api('print_listing_enhancements_foreach_end')) ? eval($apihook) : false;
			}
		}

                $jsonclick = 'single';
                if (!empty($extra) AND $extra == 'bulk')
                {
                        $jsonclick = 'bulk';
                }

		// #### listing enhancements html display ######################
		$html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
                
		if ($showbold)
		{
			$html .= '
			<tr class="alt1">
				<td width="3%" valign="top" align="center"><input type="checkbox" name="enhancements[bold]" id="bold" value="1" ' . $cb_bold . ' onclick="return livefeecalculator(\'' . $ilconfig["{$cattype}upsell_boldfee"] . '\', \'' . $jsonclick . '\', \'bold\')" /></td>
				<td width="97%" ><label for="bold">' . $phrase['_bold_title_via_search_results'] . ' &nbsp;&nbsp;<span class="smaller gray">(' . $boldprice_fee . ')</span></label></td>
			</tr>';
		}
		if ($showhlite)
		{
			$html .= '
			<tr class="alt1">
				<td valign="top" align="center"><input type="checkbox" name="enhancements[highlight]" id="highlight" value="1" ' . $cb_highlight . ' onclick="return livefeecalculator(\'' . $ilconfig["{$cattype}upsell_highlightfee"] . '\', \'' . $jsonclick . '\', \'highlight\')" /></td>
				<td><label for="highlight">' . $phrase['_highlight_listing_via_search_results'] . ' &nbsp;&nbsp;<span class="smaller gray">(' . $highlite_fee . ')</span></label></td>
			</tr>';
		}
		if ($showfeatured)
		{
			$html .= '
			<tr class="alt1">
				<td valign="top" align="center"><input type="checkbox" id="featured" name="enhancements[featured]" value="1" ' . $cb_featured . ' onclick="return livefeecalculator(\'' . $ilconfig["{$cattype}upsell_featuredfee"] . '\', \'' . $jsonclick . '\', \'featured\')" /></td>
				<td><label for="featured">' . $phrase['_featured_item_presence'] . ' (' . $ilconfig['serviceupsell_featuredlength'] . ' ' . $phrase['_days_lower'] . ') &nbsp;&nbsp;<span class="smaller gray">(' . $featured_fee . ')</span></label></td>
			</tr>';
		}
                
                if ($showautorelist)
		{
			$html .= '
			<tr>
				<td valign="top" align="center"><input type="checkbox" id="autorelist" name="enhancements[autorelist]" value="1" ' . $cb_autorelist . ' onclick="return livefeecalculator(\'' . $ilconfig["{$cattype}upsell_autorelistfee"] . '\', \'' . $jsonclick . '\', \'autorelist\')" /></td>
				<td><label for="autorelist">' . $phrase['_automatic_relist_if_listing_receives_no_bids'] . ' (' . $ilconfig['productupsell_autorelistmaxdays'] . ' ' . $phrase['_days_lower'] . ') &nbsp;&nbsp;<span class="smaller gray">(' . $autorelist_fee . ')</span></label></td>
			</tr>';
		}
                
                ($apihook = $ilance->api('print_listing_enhancements_end')) ? eval($apihook) : false;
                
		$html .= '</table>';
                
                // will output all fees the user selected for the enhancements
                $show['selectedenhancements'] = 0;
                if (isset($sumfees) AND $sumfees > 0)
                {
			$totalfees = number_format($sumfees, 2);
                        $show['selectedenhancements'] = $totalfees;
			$totalfees_preview = $ilance->currency->format($sumfees);
		}
                
                return $html;
        }
        
        /**
        * Function to print any applicable insertion fees during the posting of an auction.  This function takes into consideration if the viewing user is exempt from insertion fees.
        *
        * @param       integer       category id
        * @param       string        category type (service/product)
        * 
        * @return      string        HTML representation of the insertion fee table
        */
        function print_insertion_fees($cid = 0, $cattype = '')
        {
                global $ilance, $myapi, $phrase, $show, $ilconfig;
                
                $htmlinsertionfees = '';
                $show['insertionfees'] = 1;
                
                // #### PRODUCT ################################################
                if ($cattype == 'product')
                {
                        $sqlinsertions = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "insertion_fees
                                WHERE groupname = '" . $ilance->categories->insertiongroup($cid) . "'
                                        AND state = '" . $ilance->db->escape_string($cattype) . "'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlinsertions) > 0)
                        {
                                while ($rows = $ilance->db->fetch_array($sqlinsertions, DB_ASSOC))
                                {
                                        $from = $ilance->currency->format($rows['insertion_from']);
                                        $to =  ' &ndash; ' . $ilance->currency->format($rows['insertion_to']);
                                        $amount = $ilance->currency->format($rows['amount']);
                                        $show['insertionfeeamount'] = $rows['amount'];
                                        if ($rows['insertion_to'] == '-1')
                                        {
                                                $to = $phrase['_or_more'];
                                        }
                                        
                                        $htmlinsertionfees .= '<tr class="alt1"><td valign="top">' . $from . ' ' . $to . '</td><td valign="top"><b>' . $amount . '</b></td></tr>';
                                }
                                
                                $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">' . $phrase['_depending_on_start_price_or_reserve_price_amount_the_greater'] . '</span></td></tr>';
                        }
                        else 
                        {
                                $show['insertionfees'] = $show['insertionfeeamount'] = 0;
                                $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">' . $phrase['_no_insertion_fees_within_this_category'] . '</span></td></tr>';
                        }
                        
                        // check for subscription insertion fee exemption
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'insexempt') == 'yes')
                        {
                                $htmlinsertionfees = '<tr><td valign="top" colspan="2"><span class="gray">' . $phrase['_you_are_exempt_from_insertion_fees'] . '</span></td></tr>';
                        }
                        
                        // product listing fees output display
                        $listingfees = '
                        <div class="block-wrapper">

                        <div class="block">
                        
                                        <div class="block-top">
                                                        <div class="block-right">
                                                                        <div class="block-left"></div>
                                                        </div>
                                        </div>
                                        
                                        <div class="block-header">' . $phrase['_insertion_listing_fees'] . '</div>
			
                                        <div class="block-content" style="padding:0px">
                                        <table border="0" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                                        </tr>
                                        <tr class="alt2">
                                                <td valign="top"><strong>' . $phrase['_start_price_or_reserve_amount'] . '</strong></td>
                                                <td valign="top"><strong>' . $phrase['_insertion_fee_amount'] . '</strong></td>
                                        </tr>
                                        ' . $htmlinsertionfees . '
                                        </table></div>
			
                                                <div class="block-footer">
                                                                <div class="block-right">
                                                                                <div class="block-left"></div>
                                                                </div>
                                                </div>
                                                
                                </div>
                        </div>';
                }
                
                // #### SERVICE ################################################
                else if ($cattype == 'service')
                {
                        $sqlinsertions = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "insertion_fees
                                WHERE groupname = '" . $ilance->db->escape_string($ilance->categories->insertiongroup($cid)) . "'
                                    AND state = '" . $ilance->db->escape_string($cattype) . "'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlinsertions) > 0)
                        {
                                while ($rows = $ilance->db->fetch_array($sqlinsertions, DB_ASSOC))
                                {
                                        $amount = $ilance->currency->format($rows['amount']);
                                        $show['insertionfeeamount'] = $rows['amount'];
                                        if ($rows['insertion_to'] == '-1')
                                        {
                                                $to = $phrase['_or_more'];
                                        }
                                        $htmlinsertionfees .= '<tr class="alt1"><td valign="top">' . stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cattype, $cid)) . '</td><td valign="top"><strong>' . $amount . '</strong></td></tr>';
                                }
                                
                                $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">' . $phrase['_you_may_be_required_to_pay_this_fee_in_full_before_public_visibility'] . '</span></td></tr>';
                        }
                        else 
                        {
                                $show['insertionfees'] = $show['insertionfeeamount'] = 0;
                                $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">' . $phrase['_no_insertion_fees_within_this_category'] . '</span></td></tr>';	
                        }
                        
                        // check for subscription insertion fee exemption
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'insexempt') == 'yes')
                        {
                                $htmlinsertionfees = '<tr><td valign="top" colspan="2"><span class="gray">' . $phrase['_you_are_exempt_from_insertion_fees'] . '</span></td></tr>';
                        }
                        
                        $listingfees = '
                        <div class="block-wrapper">

                        <div class="block2">
                        
                                        <div class="block2-top">
                                                        <div class="block2-right">
                                                                        <div class="block2-left"></div>
                                                        </div>
                                        </div>
                                        
                                        <div class="block2-header">' . $phrase['_insertion_listing_fees'] . '</div>
			
                                        <div class="block2-content" style="padding:0px">
                                        <table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                        </tr>
                        <tr class="alt2">
                                <td valign="top"><strong>' . $phrase['_category'] . '</strong></td>
                                <td valign="top"><strong>' . $phrase['_insertion_fee_amount'] . '</strong></td>
                        </tr>
                        ' . $htmlinsertionfees . '
                        </table></div>
			
                                                <div class="block2-footer">
                                                                <div class="block2-right">
                                                                                <div class="block2-left"></div>
                                                                </div>
                                                </div>
                                                
                                </div>
                        </div>';
                }
                
                return $listingfees;
        }
        
        /**
        * Function to print any applicable service budget insertion fees during the posting of an auction.
        *
        * @param       integer       category id
        * 
        * @return      string        HTML representation of the insertion fee table
        */
        function print_budget_insertion_fees($cid = 0)
        {
                global $ilance, $myapi, $phrase, $show, $ilconfig;
                
                $htmlinsertionfees = '';
                $show['budgetinsertionfees'] = 1;
                
                $sqlinsertions = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "budget
                        WHERE budgetgroup = '" . $ilance->db->escape_string($ilance->categories->budgetgroup($cid)) . "'
                        ORDER BY budgetfrom ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlinsertions) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sqlinsertions))
                        {
                                if ($res['budgetto'] == '-1')
                                {
                                        $thefee = $this->calculate_insertion_fee_in_budget_group($res['insertiongroup']);
                                        if ($thefee == 0)
                                        {
                                                $thefee = $phrase['_free'];
                                        }
                                        else
                                        {
                                                $thefee = $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup']));
                                        }
                                        $htmlinsertionfees .= '<tr class="alt1"><td valign="top">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' ' . $phrase['_or_more'] . ')</td><td valign="top"><strong>' . $thefee . '</strong></td></tr>';
                                }
                                else
                                {
                                        $thefee = $this->calculate_insertion_fee_in_budget_group($res['insertiongroup']);
                                        if ($thefee == 0)
                                        {
                                                $thefee = $phrase['_free'];
                                        }
                                        else
                                        {
                                                $thefee = $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup']));
                                        }
                                        $htmlinsertionfees .= '<tr class="alt1"><td valign="top">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . ')</td><td valign="top"><strong>' . $thefee . '</strong></td></tr>';
                                }                                        
                        }
                        
                        $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">' . $phrase['_you_may_be_required_to_pay_this_fee_in_full_before_public_visibility'] . '</span></td></tr>';
                }
                else 
                {
                        $show['budgetinsertionfees'] = 0;
                        $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">' . $phrase['_no_insertion_fees_within_this_category'] . '</span></td></tr>';	
                }
                
                $listingfees = '
                <div class="block-wrapper">

                <div class="block2">
                
                                <div class="block2-top">
                                                <div class="block2-right">
                                                                <div class="block2-left"></div>
                                                </div>
                                </div>
                                
                                <div class="block2-header">' . $phrase['_budget'] . ' ' . $phrase['_insertion_listing_fees'] . '</div>
			
                                <div class="block2-content" style="padding:0px">
                                <table border="0" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                                <tr class="alt2">
                                        <td valign="top"><strong>' . $phrase['_budget'] . '</strong></td>
                                        <td valign="top" nowrap="nowrap"><strong>' . $phrase['_insertion_fee_amount'] . '</strong></td>
                                </tr>
                                ' . $htmlinsertionfees . '
                                </table></div>
			
                                                        <div class="block2-footer">
                                                                        <div class="block2-right">
                                                                                        <div class="block2-left"></div>
                                                                        </div>
                                                        </div>
                                                        
                                        </div>
                                </div>';
                
                return $listingfees;
        }
        
        /**
        * Function to print final value fees formatted in a html table.  This function takes into consideration if the viewing user is exempt from final value fees.
        *
        * @param       integer        category id number
        * @param       string         category type (service/product)
        * @param       string         bid amount type
        *
        * @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
        */
		// Murugan Changes On Nov 12 For Subscription Based FVF 
       // function print_final_value_fees($cid = 0, $cattype = 'service', $bidamounttype = '')
		function print_final_value_fees($userid = 0, $cattype = 'service', $bidamounttype = '')
        {
                global $ilance, $myapi, $phrase, $show, $ilconfig;
                
                $ilance->subscription = construct_object('api.subscription');
                
                $htmlfinalvaluefees = '';
				// Murugan Changes On Nov 12 For Subscription Based FVF 
                $userid = intval($userid);
                // first check if admin uses fixed fees in this category
                if ($ilance->categories->usefixedfees($cid) AND isset($bidamounttype) AND !empty($bidamounttype))
                {
                        // admin charges a fixed fee within this category to service providers
                        // let's determine if the bid amount type logic is configured
                        if ($bidamounttype != 'entire' AND $bidamounttype != 'item' AND $bidamounttype != 'lot')
                        {
                                // bid amount type passes accepted commission types
                                // let's output our final value fee table
                                if ($cattype == 'service')
                                {
                                        $htmlfinalvaluefees .= '<tr><td class="alt1">' . $phrase['_no_awarded_provider'] . '</td><td class="alt1"><strong>' . $phrase['_no_fee'] . '</strong></td></tr>';
                                }
                                else
                                {
                                        $htmlfinalvaluefees .= '<tr><td class="alt1">' . $phrase['_no_winning_bid'] . '</td><td class="alt1"><strong>' . $phrase['_no_fee'] . '</strong></td></tr>';
                                }
                                
                                $htmlfinalvaluefees .= '<tr><td valign="top" nowrap="nowrap" class="alt1">' . $ilance->currency->format(0.01) . ' ' . $phrase['_or_more'] . '</td><td valign="top" class="alt1">' . $ilance->currency->format($ilance->categories->fixedfeeamount($cid)) . ' (' . $phrase['_fixed'] . ')</td></tr>';
                        }
                        else
                        {
                                $htmlfinalvaluefees .= '<tr><td valign="top" colspan="2" class="alt1"><span class="gray">' . $phrase['_no_final_value_fees_within_this_category'] . '</span></td></tr>';	    
                        }
                }
                else
                {
                        $show['finalvaluefees'] = 1;
                        // Murugan Changes On Nov 12 For Subscription Based FVF 
                        /*$sqlfinalvalues = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "finalvalue
                                WHERE groupname = '" . $ilance->db->escape_string($ilance->categories->finalvaluegroup($cid)) . "'
					AND state = '" . $ilance->db->escape_string($cattype) . "'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);*/
						 $sqlfinalvalues = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "finalvalue
                                WHERE lower(groupname) = '" . strtolower($ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], $accessname='fvffees')) . "'
                                    AND state = '" . $ilance->db->escape_string($cattype) . "'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlfinalvalues) > 0)
                        {
                                $tier = 1;
                                while ($rows = $ilance->db->fetch_array($sqlfinalvalues))
                                {
                                        $from = $ilance->currency->format($rows['finalvalue_from']);
                                        $to =  ' &ndash; ' . $ilance->currency->format($rows['finalvalue_to']);
                                        if ($rows['amountfixed'] > 0)
                                        {
                                                $amountraw = $rows['amountfixed'];
                                                $amount = '<strong>' . $ilance->currency->format($rows['amountfixed']) . '</strong> ' . $phrase['_fixed_price'];
                                        }
                                        else 
                                        {
                                                $amountraw = $rows['amountpercent'];
                                                if ($tier == 1)
                                                {
                                                        $amount = '<strong>' . $rows['amountpercent'] . '%</strong> ' . $phrase['_of_the_closing_value'];
                                                }
                                                else 
                                                {
                                                        $amount = '<strong>' . $rows['amountpercent'] . '%</strong> ' . $phrase['_of_the_remaining_balance_plus_tier_above'];
                                                }
                                        }
                                        
                                        if ($rows['finalvalue_to'] == '-1')
                                        {
                                                $to = $phrase['_or_more'];
                                        }
                                        
                                        $htmlfinalvaluefees .= '<tr><td valign="top" nowrap="nowrap" class="alt1">' . $from . ' ' . $to . '</td><td valign="top" class="alt1">' . $amount . '</td></tr>';
                                        $tier++;
                                }
                                
                                if ($cattype == 'service')
                                {
                                        $htmlfinalvaluefees .= '<tr><td><span class="gray">' . $phrase['_no_awarded_provider'] . '</span></td><td><span class="gray"><strong>' . $phrase['_no_fee'] . '</strong></span></td></tr>';
                                }
                                else 
                                {
                                        $htmlfinalvaluefees .= '<tr><td><span class="gray">' . $phrase['_no_winning_bid'] . '</span></td><td><span class="gray"><strong>' . $phrase['_no_fee'] . '</strong></span></td></tr>';	
                                }
                        }
                        else 
                        {
                                $show['finalvaluefees'] = 0;
                                $htmlfinalvaluefees .= '<tr><td valign="top" colspan="2"><span class="gray">' . $phrase['_no_final_value_fees_within_this_category'] . '</span></td></tr>';	
                        }
                }
                
                // check for subscription fvf exemption
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->subscription->check_access($_SESSION['ilancedata']['user']['userid'], 'fvfexempt') == 'yes')
                {
                        $htmlfinalvaluefees = '<tr><td valign="top" colspan="2" class="alt1"><span class="gray">' . $phrase['_you_are_exempt_from_final_value_fees'] . '</span></td></tr>';
                }
                
                $listingfees = '
                <div class="block-wrapper">

                        <div class="block">
                        
                        <div class="block-top">
                                        <div class="block-right">
                                                        <div class="block-left"></div>
                                        </div>
                        </div>
                        
                        <div class="block-header">' . $phrase['_final_value_fees'] . '</div>
                        
                        <div class="block-content" style="padding:0px">
                                                
                        <table border="0" width="100%" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" dir="' . $ilconfig['template_textdirection'] . '">';
            
                        if ($cattype == 'service')
                        {
                                $listingfees .= '
                                <tr>
                                        <td valign="top" class="alt2"><strong>' . $phrase['_awarded_price'] . '</strong></td>
                                        <td valign="top" class="alt2"><strong>' . $phrase['_final_value_fee'] . '</strong></td>
                                </tr>
                                ' . $htmlfinalvaluefees;
                        }
                        else 
                        {
                                $listingfees .= '
                                <tr>
                                        <td valign="top" class="alt2"><strong>' . $phrase['_closing_price'] . '</strong></td>
                                        <td valign="top" class="alt2"><strong>' . $phrase['_final_value_fee'] . '</strong></td>
                                </tr>
                                ' . $htmlfinalvaluefees;	
                        }
                        
                        $listingfees .= '
                        </table>
                        
                        </div>
                                        
                        <div class="block-footer">
                                        <div class="block-right">
                                                        <div class="block-left"></div>
                                        </div>
                        </div>
                                        
                        </div>
                </div>';
                
                return $listingfees;
        }
        
        /**
        * Function to process the custom auction questions
        *
        * @param       array         custom array
        * @param       integer       project id
        * @param       string        category mode (service or product)
        *
        * @return      null
        */
        function process_custom_questions($custom = array(), $projectid = 0, $mode = '')
        {
                global $ilance;
                
                if (isset($custom) AND !empty($custom))
                {
                        foreach ($custom AS $questionid => $answerarray)
                        {
                                foreach ($answerarray AS $formname => $answer)
                                {
                                        if ($mode == 'service')
                                        {
                                                $sql2 = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "project_answers
                                                        WHERE questionid = '" . intval($questionid) . "'
                                                            AND project_id = '" . intval($projectid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        if ($mode == 'product')
                                        {
                                                $sql2 = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "product_answers
                                                        WHERE questionid = '" . intval($questionid) . "'
                                                            AND project_id = '" . intval($projectid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }                        
                                        if ($ilance->db->num_rows($sql2) > 0 AND !empty($answer))
                                        {
                                                if (is_array($answer))
                                                {
                                                        // multiple choice
                                                        $answer = serialize($answer);
                                                }
                                                if ($mode == 'service')
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_answers
                                                                SET answer = '" . $ilance->db->escape_string($answer) . "'
                                                                WHERE questionid = '" . intval($questionid) . "'
                                                                    AND project_id = '" . intval($projectid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                if ($mode == 'product')
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "product_answers
                                                                SET answer = '" . $ilance->db->escape_string($answer) . "'
                                                                WHERE questionid = '" . intval($questionid) . "'
                                                                    AND project_id = '" . intval($projectid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);   
                                                }                            
                                        }
                                        else
                                        {
                                                if (!empty($answer))
                                                {
                                                        if (is_array($answer))
                                                        {
                                                                // multiple choice
                                                                $answer = serialize($answer);
                                                        }
                                                        if ($mode == 'service')
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "project_answers
                                                                        (answerid, questionid, project_id, answer, date, visible)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($questionid) . "',
                                                                        '" . intval($projectid) . "',
                                                                        '" . $ilance->db->escape_string($answer) . "',
                                                                        '" . DATETIME24H . "',
                                                                        '1')
                                                                ", 0, null, __FILE__, __LINE__);    
                                                        }
                                                        if ($mode == 'product')
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "product_answers
                                                                        (answerid, questionid, project_id, answer, date, visible)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($questionid) . "',
                                                                        '" . intval($projectid) . "',
                                                                        '" . $ilance->db->escape_string($answer) . "',
                                                                        '" . DATETIME24H . "',
                                                                        '1')
                                                                ", 0, null, __FILE__, __LINE__);    
                                                        }
                                                }
                                        }
                                }
                        }
                }
        }
		
		function process_custom_questions1($custom = array(), $projectid = 0, $mode = '')
        {
		
                global $ilance;
                
				
                if (isset($custom) AND !empty($custom))
                {
                        foreach ($custom AS $questionid => $answerarray)
                        {
                                foreach ($answerarray AS $formdefault => $answer)
                                {
                                        if ($mode == 'service')
                                        {
                                                $sql2 = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "project_answers
                                                        WHERE questionid = '" . intval($questionid) . "'
                                                            AND project_id = '" . intval($projectid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        if ($mode == 'product')
                                        {
                                                $sql2 = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "product_answers
                                                        WHERE questionid = '" . intval($questionid) . "'
                                                            AND project_id = '" . intval($projectid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }                        
                                        if ($ilance->db->num_rows($sql2) > 0 AND !empty($answer))
                                        {
                                                
												
												if (is_array($answer))
                                                {
                                                        // multiple choice
                                                        $answer = serialize($answer);
                                                }
                                                if ($mode == 'service')
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_answers
                                                                SET answer = '" . $ilance->db->escape_string($answer) . "'
                                                                WHERE questionid = '" . intval($questionid) . "'
                                                                    AND project_id = '" . intval($projectid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                if ($mode == 'product')
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "product_answers
                                                                SET answer = '" . $ilance->db->escape_string($answer) . "'
                                                                WHERE questionid = '" . intval($questionid) . "'
                                                                    AND project_id = '" . intval($projectid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);   
                                                }                            
                                        }
                                        else
                                        {
                                                if (!empty($answer))
                                                {
                                                        if (is_array($answer))
                                                        {
                                                                // multiple choice
																
                                                                $answer = serialize($answer);
                                                        }
                                                        if ($mode == 'service')
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "project_answers
                                                                        (answerid, questionid, project_id, answer, date, visible)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($questionid) . "',
                                                                        '" . intval($projectid) . "',
                                                                        '" . $ilance->db->escape_string($answer) . "',
                                                                        '" . DATETIME24H . "',
                                                                        '1')
                                                                ", 0, null, __FILE__, __LINE__);    
                                                        }
                                                        if ($mode == 'product')
                                                        {
                                                              
																$ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "product_answers
                                                                        (answerid, questionid, project_id, answer, date, visible)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($questionid) . "',
                                                                        '" . intval($projectid) . "',
                                                                        '" . $ilance->db->escape_string($answer) . "',
                                                                        '" . DATETIME24H . "',
                                                                        '1')
                                                                ", 0, null, __FILE__, __LINE__);    
                                                        }
                                                }
                                        }
                                }
                        }
                }
        }
        
        /**
        * Function to process the custom auction profile filter questions
        *
        * @param       array         custom array
        * @param       integer       project id
        * @param       string        category mode (service or product)
        *
        * @return      null
        */
        function process_custom_profile_questions($custom = array(), $projectid = 0, $userid = 0, $mode = '')
        {
                global $ilance;
                
                if (isset($custom) AND !empty($custom))
                {
                        foreach ($custom as $questionid => $answer)
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "profile_filter_auction_answers
                                        WHERE questionid = '".intval($questionid)."'
                                            AND project_id = '".intval($projectid)."'
                                            AND user_id = '".intval($userid)."'
                                ", 0, null, __FILE__, __LINE__);                    
                                if ($ilance->db->num_rows($sql2) > 0 AND !empty($answer))
                                {
                                        if (is_array($answer))
                                        {
                                                // range choices
                                                $answer = serialize($answer);
                                        }
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "profile_filter_auction_answers
                                                SET answer = '".$ilance->db->escape_string($answer)."'
                                                WHERE questionid = '".intval($questionid)."'
                                                    AND project_id = '".intval($projectid)."'
                                                    AND user_id = '".intval($userid)."'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);                            
                                }
                                else
                                {
                                        if (!empty($answer))
                                        {
                                                if (is_array($answer))
                                                {
                                                        // range choices
                                                        $answer = serialize($answer);
                                                }
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "profile_filter_auction_answers
                                                        (answerid, questionid, project_id, user_id, answer, date, visible)
                                                        VALUES(
                                                        NULL,
                                                        '".intval($questionid)."',
                                                        '".intval($projectid)."',
                                                        '".intval($userid)."',
                                                        '".$ilance->db->escape_string($answer)."',
                                                        '".DATETIME24H."',
                                                        '1')
                                                ", 0, null, __FILE__, __LINE__);    
                                        }
                                }
                        }
                }
        }
        
        /**
        * Function to obtain the email invitation email list line by line using \n as line seperator.
        *
        * @param       integer      auction id
        *
        * @return      string       line by line email list
        */
        function fetch_email_invites($projectid = 0)
        {
                global $ilance, $myapi;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT email, name
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE project_id = '".intval($projectid)."'
                            AND email != ''
                            AND buyer_user_id = '".$_SESSION['ilancedata']['user']['userid']."'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= $res['email'] . "\n";
                        }
                }
                
                return $html;
        }
        
        /**
        * Function to obtain the username invitation list line by line using \n as line seperator.
        *
        * @param       integer      auction id
        *
        * @return      string       line by line username list
        */
        function fetch_member_invites($projectid = 0)
        {
                global $ilance, $myapi;
                
                $html = '';
                
                $sql = $ilance->db->query("
                        SELECT seller_user_id
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE project_id = '".intval($projectid)."'
                            AND buyer_user_id = '".$_SESSION['ilancedata']['user']['userid']."'
                            AND seller_user_id != '-1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $html .= fetch_user('username', $res['seller_user_id']) . "\n";
                        }
                }
                
                return $html;
        }
        
        /**
        * Function to print a year pulldown menu.
        *
        * @param       string        selected year value (optional)
        *
        * @return      string        HTML representation of the year pulldown menu
        */
        function year($year = '')
        {
                global $ilconfig;
                
                $html = '<select name="year" style="font-family: verdana">';
                $html .= '<option value="'.gmdate('Y',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])).'">'.gmdate('Y',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])).'</option>';
                if (gmdate('m') == '11')
                {
                        $html .= '<option value="'.(gmdate('Y',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))+1).'">'.(gmdate('Y',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst']))+1).'</option>';
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print a month pulldown menu.
        *
        * @param       string        selected month value (optional)
        *
        * @return      string        HTML representation of the month pulldown menu
        */
        function month($month = '')
        {
                global $ilconfig;
                
                $html = '<select name="month" style="font-family: verdana">';
                
                for ($i = 1; $i <= 12; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }
                        if (isset($month) AND $month == $i)
                        {
                                $html .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
                        }
                        else
                        {
                                $html .= '<option value="'.$i.'"';
                                if (empty($month) AND $i == gmdate('m',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>'.$i.'</option>';				
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print a day pulldown menu.
        *
        * @param       string        selected day value (optional)
        *
        * @return      string        HTML representation of the day pulldown menu
        */
        function day($day = '')
        {
                global $ilconfig;
                
                $html = '<select name="day" style="font-family: verdana">';
                for ($i = 1; $i <= 31; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }
                            
                        if (isset($day) AND $day == $i)
                        {
                                $html .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
                        }
                        else 
                        {
                                $html .= '<option value="'.$i.'"';
                                if (empty($day) AND $i == gmdate('d',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>'.$i.'</option>';
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print an hour pulldown menu.
        *
        * @param       string        selected hour value (optional)
        *
        * @return      string        HTML representation of the hour pulldown menu
        */
        function hour($hour = '')
        {
                global $ilconfig;
                
                $html = '<select name="hour" style="font-family: verdana">';                                
                for ($i = 0; $i <= 23; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }                                    
                        if (isset($hour) AND $i == $hour)
                        {
                                $html .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
                        }
                        else 
                        {
                                $html .= '<option value="'.$i.'"';
                                if (empty($hour) AND $i == gmdate('H',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>'.$i.'</option>';
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print a minute pulldown menu.
        *
        * @param       string        selected minute value (optional)
        *
        * @return      string        HTML representation of the minute pulldown menu
        */
        function min($min = '')
        {
                global $ilconfig;
                
                $html = '<select name="min" style="font-family: verdana">';
                for ($i = 0; $i <= 59; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }                                    
                        if (isset($min) AND $i == $min)
                        {
                                $html .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
                        }
                        else 
                        {
                                $html .= '<option value="'.$i.'"';
                                if (empty($min) AND $i == gmdate('i',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>'.$i.'</option>';
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print a seconds pulldown menu.
        *
        * @param       string        selected seconds value (optional)
        *
        * @return      string        HTML representation of the seconds pulldown menu
        */
        function sec($sec = '')
        {
                global $ilconfig;
                
                $html = '<select name="sec" style="font-family: verdana">';
                for ($i = 0; $i <= 59; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }
                        if (isset($sec) AND $i == $sec)
                        {
                                $html .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
                        }
                        else 
                        {
                                $html .= '<option value="'.$i.'"';
                                if (empty($sec) AND $i == gmdate('s',time()+3600*($ilconfig['globalserverlocale_officialtimezone']+$ilconfig['globalserverlocale_officialtimezonedst'])))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>'.$i.'</option>';
                        }
                }
                $html .= '</select>';
                
                return $html;
        }
        
        /**
        * Function to print the selling format logic for a product auction.
        *
        * @return      string        HTML representation of the selling format selection menus
        */
        function print_selling_format_logic($disabled = false)
        {
                global $ilance, $ilconfig, $phrase, $tab, $headinclude, $onload;
		
                $headinclude .= $ilconfig['enablefixedpricetab'] ? '
<script type="text/javascript">
<!--
function update_price_fixed()
{
        price = fetch_js_object("buynow_price").value;
        document.ilform.buynow_price_fixed.value = price;
}
function update_buynow_price_fixed()
{
        setTimeout("update_price_fixed()", 500);
}
function update_price()
{
        price = fetch_js_object("buynow_price_fixed").value;
        document.ilform.buynow_price.value = price;
}
function update_buynow_price()
{
        setTimeout("update_price()", 500);
}
function update_qty_fixed()
{
        qty = fetch_js_object("buynow_qty").value;
        document.ilform.buynow_qty_fixed.value = qty;
}
function update_buynow_qty_fixed()
{
        setTimeout("update_qty_fixed()", 500);
}
function update_qty()
{
        qty = fetch_js_object("buynow_qty_fixed").value;
        document.ilform.buynow_qty.value = qty;
}
function update_buynow_qty()
{
        setTimeout("update_qty()", 500);
}
//-->
</script>'
: '
<script type="text/javascript">
<!--
function update_price_fixed()
{
        return(true);
}
function update_buynow_price_fixed()
{
        return(true);
}
function update_price()
{
        return(true);
}
function update_buynow_price()
{
        return(true);
}
function update_qty_fixed()
{
        return(true);
}
function update_buynow_qty_fixed()
{
        return(true);
}
function update_qty()
{
        return(true);
}
function update_buynow_qty()
{
        return(true);
}
//-->
</script>';
		$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1, 1, $ilance->GPC['cid']);
		
                $cb_auctiontype1 = 'checked="checked"';
		$cb_auctiontype2 = '';
                $tab = 0;
		if (isset($ilance->GPC['filtered_auctiontype']) AND $ilance->GPC['filtered_auctiontype'] == 'regular' AND $ilconfig['enableauctiontab'])
		{
			$cb_auctiontype1 = 'checked="checked"';
			$cb_auctiontype2 = '';
                        $tab = 0;
		}
		else if (isset($ilance->GPC['filtered_auctiontype']) AND $ilance->GPC['filtered_auctiontype'] == 'fixed' AND $ilconfig['enablefixedpricetab'])
		{
			$cb_auctiontype1 = '';
			$cb_auctiontype2 = 'checked="checked"';
                        $tab = 1;
		}
		
                // #### REGULAR AUCTION ########################################
		// starting bid price
		$startprice = '';
		if (!empty($ilance->GPC['startprice']))
		{
			$startprice = sprintf("%01.2f", $ilance->GPC['startprice']);
		}
		
		// reserve price
                $show['usereserveprice'] = $ilance->categories->usereserveprice($ilance->GPC['cid']);
		$reserve_price = '';
                $reserve = 0;
		if (!empty($ilance->GPC['reserve_price']) AND $ilance->GPC['reserve_price'] > 0 AND $show['usereserveprice'])
		{
			$reserve_price = sprintf("%01.2f", $ilance->GPC['reserve_price']);
                        $reserve = 1;
		}
                
                // reserve price fee
                $reservefee = 0;
                $reservefeeformatted = $phrase['_free'];;
                if ($ilconfig['productupsell_reservepricecost'] > 0)
                {
                        $reservefee = $ilconfig['productupsell_reservepricecost'];
                        $reservefeeformatted = $ilance->currency->format($reservefee);
                }
                
		// buynow price
		$buynow_price = '';
                $buynow_price_fixed = '';
		if (!empty($ilance->GPC['buynow_price']) AND $ilance->GPC['buynow_price'] > 0)
		{
			$buynow_price = sprintf("%01.2f", $ilance->GPC['buynow_price']);
                        $buynow_price_fixed = sprintf("%01.2f", $ilance->GPC['buynow_price']);
		}
		
		// buynow qty
		$buynow_qty = 1;
                $buynow_qty_fixed = 1;
		if (!empty($ilance->GPC['buynow_qty']))
		{
			$buynow_qty = intval($ilance->GPC['buynow_qty']);
                        $buynow_qty_fixed = intval($ilance->GPC['buynow_qty']);
		}
		
                // #### FIXED PRICED ONLY ######################################
                // buynow price
		if (!empty($ilance->GPC['buynow_price_fixed']) AND $ilance->GPC['buynow_price_fixed'] > 0)
		{
			$buynow_price_fixed = sprintf("%01.2f", $ilance->GPC['buynow_price_fixed']);
		}
		
		// buynow qty
		if (!empty($ilance->GPC['buynow_qty_fixed']))
		{
			$buynow_qty_fixed = intval($ilance->GPC['buynow_qty_fixed']);
		}
                
                // buy now fees
                $buynowfee = 0;
                $buynowfeeformatted = $phrase['_free'];
                if ($ilconfig['productupsell_buynowcost'] > 0)
                {
                        $buynowfee = $ilconfig['productupsell_buynowcost'];
                        $buynowfeeformatted = $ilance->currency->format($buynowfee);
                }
                
		// determine what the admin has set for selling logic formatting
		if ($ilconfig['enableauctiontab'] == 0 AND $ilconfig['enablefixedpricetab'] == 0)
		{
			// some guy in the admin disabled everything! re-enable it!
			$ilconfig['enableauctiontab'] = 1;
			$ilconfig['enablefixedpricetab'] = 1;
		}
		else if ($ilconfig['enableauctiontab'] == 0 AND $ilconfig['enablefixedpricetab'])
		{
			$onload .= 'fetch_js_object(\'filtered_auctiontype\').checked = true; ';
		}
		
		$html = '<div class="tab-pane" id="sellingformat">';
		
		if (isset($ilconfig['enableauctiontab']) AND $ilconfig['enableauctiontab'])
		{
			$html .= '
<div class="tab-page">
<h2 class="tab" id="0"><a href="javascript:void(0)" onclick="javascript:document.ilform.filtered_auctiontype[0].checked=true">' . $phrase['_auction'] . '</a></h2>
<div><strong><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="filtered_auctiontype" id="filtered_auctiontype" value="regular" ' . $cb_auctiontype1 . ' /> <label for="filtered_auctiontype">' . $phrase['_auction'] . '</label></strong> : <span class="gray">' . $phrase['_this_format_allow_bidding_to_take_place_including_the_ablility'] . '</span></div>
<table border="0" cellpadding="2" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td width="50%" valign="top">
	
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="10" dir="' . $ilconfig['template_textdirection'] . '">
		<tr class="alt1">
			<td width="1%" nowrap="nowrap">' . $phrase['_starting_price'] . '</td>
			<td width="1%">&nbsp;&nbsp;<span id="startprice_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span></td> 
			<td width="1%" nowrap="nowrap"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="startprice" name="startprice" value="' . $startprice . '" onkeypress="return noenter()" onclick="fetch_js_object(\'filtered_auctiontype\').checked = true" style="width:60px" class="input" /> <span id="startprice_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_the_starting_price_is_the_amount_you_set_the_starting_bid_amount_in_your_auction_event_bidders_will_need_to_begin_the_bid_amounts\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
		</tr>
		<tr>
			<td align="right" width="1%">' . $phrase['_qty'] . '</td>
			<td width="1%" align="right"><strong>x</strong></td>
			<td width="1%" nowrap="nowrap" colspan="2"><input type="hidden" name="buynow_qty" value="1" />1<!--<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="buynow_qty" name="buynow_qty" value="' . $buynow_qty . '" onkeypress="return update_buynow_qty_fixed()" onclick="fetch_js_object(\'filtered_auctiontype\').checked = true" style="width:25px; font-family: verdana" />--></td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="10" dir="' . $ilconfig['template_textdirection'] . '">
		<tr class="alt1">
			<td width="1%" nowrap="nowrap">' . $phrase['_buy_now_price'] . ' &nbsp;&nbsp;<span class="smaller gray">(' . $buynowfeeformatted . ')</span></td> 
			<td width="1%">&nbsp;&nbsp;<span id="buynowprice_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span></td>
			<td width="1%" nowrap="nowrap" align="left"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="buynow_price" name="buynow_price" onkeypress="return update_buynow_price_fixed()" value="' . $buynow_price . '" onclick="fetch_js_object(\'filtered_auctiontype\').checked = true" style="width:60px" class="input" /> <span id="buynowprice_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_buy_now_price_optional_if_you_would_like_to_offer_buyers_the_chance\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
		</tr>';
		
		if ($show['usereserveprice'])
		{
			$html .= '
			<tr>
				<td align="right" width="1%" nowrap="nowrap">' . $phrase['_reserve_price'] . ' &nbsp;&nbsp;<span class="smaller gray">(' . $reservefeeformatted . ')</span></td>
				<td align="right">&nbsp;&nbsp;<span id="reserveprice_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span></td> 
				<td nowrap="nowrap"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="reserve_price" name="reserve_price" value="' . $reserve_price . '" onkeypress="return noenter()" style="width:60px" onclick="fetch_js_object(\'filtered_auctiontype\').checked = true" class="input" /> <span id="reserveprice_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_a_reserve_price_is_a_hidden_amount_you_can_set_which_no_bidder_can_see\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
			</tr>';
		}
		else
		{
			$html .= '
			<tr>
				<td align="right" width="1%" nowrap="nowrap"></td>
				<td align="right"></td> 
				<td nowrap="nowrap"><input type="hidden" id="reserve_price" name="reserve_price" value="" /></td>
			</tr>';        
		}
		
		$html .= '</table>       
	</td>
</tr>
</table>                                
</div>';
		}
	
		if (isset($ilconfig['enablefixedpricetab']) AND $ilconfig['enablefixedpricetab'])
		{
			$html .= '
<div class="tab-page">
<h2 class="tab" id="1"><a href="javascript:void(0)" onclick="javascript:document.ilform.filtered_auctiontype[1].checked=true">' . $phrase['_fixed_price'] . '</a></h2>
<table border="0" cellpadding="2" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td valign="top">
		<div><strong><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="filtered_auctiontype" id="filtered_auctiontype0" value="fixed" ' . $cb_auctiontype2 . ' /> <label for="filtered_auctiontype0">' . $phrase['_fixed_price'] . '</label></strong> : <span class="gray">' . $phrase['_this_format_does_not_allow_bidding_to_take_place'] . '</span></div>
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="10" dir="' . $ilconfig['template_textdirection'] . '">
		<tr class="alt1">
			<td width="1%" nowrap="nowrap">' . $phrase['_buy_now_price'] . ' &nbsp;&nbsp;<span class="smaller gray">(' . $buynowfeeformatted . ')</span></td> 
			<td width="1%">&nbsp;&nbsp;<span id="buynowpricefixed_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span></td>
			<td width="1%" nowrap="nowrap" align="left"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="buynow_price_fixed" name="buynow_price_fixed" onkeypress="return update_buynow_price()" value="' . $buynow_price_fixed . '" onclick="fetch_js_object(\'filtered_auctiontype0\').checked = true" style="width:60px; font-family: verdana" /> <span id="buynowpricefixed_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_buy_now_price_refers_to_a_fixed_cost_for_items_you_are_selling_for_example\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
		</tr>
		<tr>
			<td align="right" width="1%">' . $phrase['_qty'] . '</td>
			<td width="1%" align="right"><strong>x</strong></td>
			<td width="1%" nowrap="nowrap" colspan="2"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="buynow_qty_fixed" name="buynow_qty_fixed" value="' . $buynow_qty_fixed . '" onkeypress="return update_buynow_qty()" onclick="fetch_js_object(\'filtered_auctiontype0\').checked = true" style="width:25px; font-family: verdana" /></td>
		</tr>
		</table>
	</td>
</tr>
</table>
</div>';
		}
                        
                ($apihook = $ilance->api('print_selling_format_logic_tab_end')) ? eval($apihook) : false;
                        
                $html .= '</div>';
                
                ($apihook = $ilance->api('print_selling_format_logic_end')) ? eval($apihook) : false;
                
                return $html;
        }
        
        /**
        * Function to print the shipping logic within a product auction.
        *
        * @param       string        
        * @param       string
        *
        * @return      nothing
        */
        function print_shipping_logic($disabled = false)
        {
                global $ilance, $ilconfig, $phrase, $onload, $project_id, $cid, $ilpage, $attachment_style, $currencysymbol, $headinclude;
		
		// #### require shipping backend ###############################################
		require_once(DIR_CORE . 'functions_shipping.php');
		
		// #### updating listing #######################################
		$shippercount = 1;
		
		if (empty($ilance->GPC['project_id']) AND !empty($ilance->GPC['id']))
		{
			$ilance->GPC['project_id'] = intval($ilance->GPC['id']);
		}
		    		
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'product-management' AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
		{
			$shippercount = fetch_shipping_services_count($ilance->GPC['project_id']);
			if (isset($ilance->GPC['ship_method']) AND !empty($ilance->GPC['ship_method']))
			{
				switch ($ilance->GPC['ship_method'])
				{
					case 'flatrate':
					{
						$onload .= 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex = 0]; toggle_show(\'showshipping\'); toggle_show(\'ship_method_service_options\'); toggle_hide(\'ship_method_calculated_options\'); toggle_show(\'handlingfeeheader\'); toggle_show(\'handlingfeerow\'); ';
						for ($i = 1; $i <= $shippercount; $i++)
						{
							// #### domestic ######
							if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'domestic' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 1]; ';
								$onload .= 'toggle_hide(\'ship_options_custom_regionnav_' . $i . '\'); ';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true, ' . $ilance->GPC['ship_service_' . $i] . '); ';
							}
							
							// #### worldwide ######
							else if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'worldwide' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 2]; ';
								$onload .= 'toggle_hide(\'ship_options_custom_regionnav_' . $i . '\'); ';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true, ' . $ilance->GPC['ship_service_' . $i] . '); ';
							}
							
							// #### custom location
							else if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'custom' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 3]; ';
								$onload .= 'toggle_show(\'ship_options_custom_regionnav_' . $i . '\'); ';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true, ' . $ilance->GPC['ship_service_' . $i] . '); ';
								
								$regions = fetch_listing_shipping_regions($ilance->GPC['project_id']);
								if (is_array($regions) AND count($regions) > 0)
								{
									foreach ($regions AS $row => $regionarray)
									{
										if ($row == $i)
										{
											foreach ($regionarray AS $region)
											{
												switch ($region)
												{
													case 'north_america':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_1\').checked = true; ';
														break;	
													}
													case 'south_america':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_4\').checked = true; ';
														break;	
													}
													case 'oceania':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_6\').checked = true; ';
														break;	
													}
													case 'europe':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_2\').checked = true; ';
														break;	
													}
													case 'asia':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_5\').checked = true; ';
														break;	
													}
													case 'antartica':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_7\').checked = true; ';
														break;	
													}
													case 'africa':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_3\').checked = true; ';
														break;	
													}
												}
											}
										}
									}
								}
								unset($regions);
							}
							if (isset($ilance->GPC['ship_fee_' . $i]) AND $ilance->GPC['ship_fee_' . $i] > 0)
							{
								$onload .= 'fetch_js_object(\'ship_fee_' . $i . '\').value = \'' . $ilance->GPC['ship_fee_' . $i] . '\'; ';
							}
							if (isset($ilance->GPC['freeshipping_' . $i]) AND $ilance->GPC['freeshipping_' . $i] == '1')
							{
								$onload .= 'fetch_js_object(\'freeshipping_' . $i . '\').checked = true; ';
							}
							$onload .= 'toggle_show(\'ship_method_service_options_' . $i . '\'); fetch_js_object(\'ship_fee_' . $i . '\').disabled = false; fetch_js_object(\'freeshipping_' . $i . '\').disabled = false; fetch_js_object(\'ship_service_' . $i . '_css_cost\').className = \'black\'; fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').className = \'black\'; fetch_js_object(\'ship_service_' . $i . '_css_freeshipping\').className = \'black\'; fetch_js_object(\'ship_service_' . $i . '_css_freeshippinganswer\').className = \'black\'; ';
						}
						break;
					}
					case 'calculated':
					{
						$onload .= 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex = 1]; toggle_show(\'showshipping\'); toggle_show(\'ship_method_calculated_options\'); toggle_show(\'handlingfeeheader\'); toggle_show(\'handlingfeerow\'); ';
						for ($i = 1; $i <= $shippercount; $i++)
						{
							// #### domestic #######
							if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'domestic' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 1]; ';
								$onload .= 'toggle_hide(\'ship_options_custom_regionnav_' . $i . '\'); ';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true, ' . $ilance->GPC['ship_service_' . $i] . '); ';
							}
							
							// #### worldwide ######
							else if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'worldwide' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 2]; ';
								$onload .= 'toggle_hide(\'ship_options_custom_regionnav_' . $i . '\'); ';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true, ' . $ilance->GPC['ship_service_' . $i] . '); ';
							}
							
							// #### custom locations
							else if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'custom' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 3]; ';
								$onload .= 'toggle_show(\'ship_options_custom_regionnav_' . $i . '\'); ';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true, ' . $ilance->GPC['ship_service_' . $i] . '); ';
								
								$regions = fetch_listing_shipping_regions($ilance->GPC['project_id']);
								if (is_array($regions) AND count($regions) > 0)
								{
									foreach ($regions AS $row => $regionarray)
									{
										if ($row == $i)
										{
											foreach ($regionarray AS $region)
											{
												switch ($region)
												{
													case 'north_america':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_1\').checked = true; ';
														break;	
													}
													case 'south_america':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_4\').checked = true; ';
														break;	
													}
													case 'oceania':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_6\').checked = true; ';
														break;	
													}
													case 'europe':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_2\').checked = true; ';
														break;	
													}
													case 'asia':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_5\').checked = true; ';
														break;	
													}
													case 'antartica':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_7\').checked = true; ';
														break;	
													}
													case 'africa':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_3\').checked = true; ';
														break;	
													}
												}
											}
										}
									}
								}
								unset($regions);
							}
							if (isset($ilance->GPC['ship_fee_' . $i]) AND $ilance->GPC['ship_fee_' . $i] > 0)
							{
								$onload .= 'fetch_js_object(\'ship_fee_' . $i . '\').value = \'' . $ilance->GPC['ship_fee_' . $i] . '\'; ';
							}
							if (isset($ilance->GPC['freeshipping_' . $i]) AND $ilance->GPC['freeshipping_' . $i] == '1')
							{
								$onload .= 'fetch_js_object(\'freeshipping_' . $i . '\').checked = true; ';
							}
							$onload .= 'toggle_show(\'ship_method_service_options_' . $i . '\'); fetch_js_object(\'ship_fee_' . $i . '\').disabled = true; fetch_js_object(\'freeshipping_' . $i . '\').disabled = true; fetch_js_object(\'ship_service_' . $i . '_css_cost\').className = \'litegray\'; fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').className = \'litegray\'; fetch_js_object(\'ship_service_' . $i . '_css_freeshipping\').className = \'litegray\'; fetch_js_object(\'ship_service_' . $i . '_css_freeshippinganswer\').className = \'litegray\'; ';
						}
						break;
					}
					case 'localpickup':
					{
						$onload .= 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex = 1]; toggle_hide(\'showshipping\'); toggle_hide(\'handlingfeeheader\'); toggle_hide(\'handlingfeerow\'); ';
						break;
					}
				}
			}
		}
		
		$headinclude .= '
<script type="text/javascript">
<!--
function add_additional_service()
{
	var num = fetch_js_object(\'shippercount\').value;
	num++;	
	fetch_js_object(\'ship_method_service_options_\' + num).style.display = \'\';
	fetch_js_object(\'ship_options_\' + num).disabled = false;
	fetch_js_object(\'ship_service_\' + num).disabled = false;
	fetch_js_object(\'ship_fee_\' + num).disabled = false;
	fetch_js_object(\'freeshipping_\' + num).disabled = false;	
	fetch_js_object(\'shippercount\').value = num++;
}
function remove_service(num)
{
	fetch_js_object(\'ship_options_\' + num).disabled = true;
	fetch_js_object(\'ship_service_\' + num).disabled = true;
	fetch_js_object(\'ship_fee_\' + num).disabled = true;
	fetch_js_object(\'freeshipping_\' + num).disabled = true;
	fetch_js_object(\'ship_method_service_options_\' + num).style.display = \'none\';	
	var num2 = fetch_js_object(\'shippercount\').value;
	num2--;
	fetch_js_object(\'shippercount\').value = num2;
}
function do_calculate_shipping()
{
	fetch_js_object(\'modal_calculatebutton\').disabled = true;
	fetch_js_object(\'shiprate\').innerHTML = \'<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'working.gif" border="0" alt="" />\';	
	var modal_shipperid = fetch_js_object(\'modal_shipperid\').value;
	var modal_country_from = fetch_js_object(\'modal_country_from\').value;
	var modal_zipcode_from = fetch_js_object(\'modal_zipcode_from\').value;
	var modal_country_to = fetch_js_object(\'modal_country_to\').value;
	var modal_zipcode_to = fetch_js_object(\'modal_zipcode_to\').value;
	var modal_ship_weightlbs = fetch_js_object(\'modal_ship_weightlbs\').value;
	var modal_ship_weightoz = fetch_js_object(\'modal_ship_weightoz\').value;
	var weight = modal_ship_weightlbs + "." + modal_ship_weightoz;
	var ajaxRequest;	
	try
	{
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}	
		catch (e)
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				return false;
			}
		}
	}	
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			fetch_js_object(\'shiprate\').innerHTML = ajaxRequest.responseText;
			fetch_js_object(\'modal_calculatebutton\').disabled = false;
		}
	}	
	var querystring = "&modal_shipperid=" + modal_shipperid + "&modal_country_from=" + modal_country_from + "&modal_zipcode_from=" + modal_zipcode_from + "&modal_country_to=" + modal_country_to + "&modal_zipcode_to=" + modal_zipcode_to + "&weight=" + weight + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	ajaxRequest.open(\'GET\', ILBASE + \'' . $ilpage['ajax'] . '?do=shipcalculator\' + querystring, true);
	ajaxRequest.send(null);
}
//-->
</script>';
		
		$html = '<div>
<select id="ship_method" name="ship_method" style="font-family: verdana" onchange="javascript:
if (fetch_js_object(\'ship_method\').value == \'flatrate\')
{
        toggle_show(\'showshipping\');
        toggle_show(\'ship_method_service_options\');
        toggle_hide(\'ship_method_calculated_options\');
	toggle_show(\'handlingfeeheader\');
	toggle_show(\'handlingfeerow\');
';
for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
{
	$html .= '
        fetch_js_object(\'ship_fee_' . $i . '\').disabled = false;
        fetch_js_object(\'freeshipping_' . $i . '\').disabled = false;
        fetch_js_object(\'ship_service_' . $i . '_css_cost\').className = \'black\';
        fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').className = \'black\';
        fetch_js_object(\'ship_service_' . $i . '_css_freeshipping\').className = \'black\';
        fetch_js_object(\'ship_service_' . $i . '_css_freeshippinganswer\').className = \'black\';';
}
$html .= '}
else if (fetch_js_object(\'ship_method\').value == \'calculated\')
{
        toggle_show(\'showshipping\');
        toggle_show(\'ship_method_calculated_options\');
	toggle_show(\'handlingfeeheader\');
	toggle_show(\'handlingfeerow\');
';
for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
{
	$html .= '
        fetch_js_object(\'ship_fee_' . $i . '\').disabled = true;
        fetch_js_object(\'freeshipping_' . $i . '\').disabled = true;
        fetch_js_object(\'ship_service_' . $i . '_css_cost\').className = \'litegray\';
        fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').className = \'litegray\';
        fetch_js_object(\'ship_service_' . $i . '_css_freeshipping\').className = \'litegray\';
        fetch_js_object(\'ship_service_' . $i . '_css_freeshippinganswer\').className = \'litegray\';';
}
$html .= '
}
else if (fetch_js_object(\'ship_method\').value == \'localpickup\')
{
        toggle_hide(\'showshipping\');
	toggle_hide(\'handlingfeeheader\');
	toggle_hide(\'handlingfeerow\');
}" ' . ($disabled ? 'disabled="disabled"' : '') . '>
<option value="flatrate">' . $phrase['_flat_rate_same_cost_to_all_buyers'] . '</option>
<!--<option value="calculated">' . $phrase['_auto_calculated_cost_varies_by_buyer_location'] . '</option>-->
<option value="localpickup">' . $phrase['_no_shipping_local_pickup'] . '</option>
</select>
</div>                    
	<div id="showshipping">		
		<div id="ship_method_calculated_options" style="display:none">
			<hr size="1" width="100%" style="color:#ccc; margin-bottom:7px; margin-top:7px" />
		    
			<table width="600" border="0" cellspacing="0" cellpadding="0">
			<tr valign="top">
				<td>
					<div>' . $phrase['_package_type'] . '</div><div style="padding-top:4px"><select id="ship_packagetype" name="ship_packagetype" style="font-family: verdana" ' . ($disabled ? 'disabled="disabled"' : '') . '>
					<option value="" selected="selected">-</option>
					<option value="largepackage">' . $phrase['_large_package'] . '</option>
					<option value="largeenvelope">' . $phrase['_large_envelope'] . '</option>
					<option value="package">' . $phrase['_package_or_thick_or_envelope'] . '</option>
					<option value="letter">' . $phrase['_letter'] . '</option>
					</select>
					</div>
				</td>
				<td>
					<div>' . $phrase['_dimentions'] . ' <span class="gray">&nbsp;&nbsp;(' . $phrase['_length_x_width_x_height'] . ')</span></div>
					<div style="padding-top:3px"><input type="text" id="ship_length" name="ship_length" value="" style="font-family: verdana" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;' . $phrase['_inches_shortform'] . '&nbsp;&nbsp;&nbsp;X&nbsp;<input type="text" id="ship_width" name="ship_width" value="" style="font-family: verdana" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;' . $phrase['_inches_shortform'] . '&nbsp;&nbsp;&nbsp;X&nbsp;<input type="text" id="ship_height" name="ship_height" value="" style="font-family: verdana" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;' . $phrase['_inches_shortform'] . '</div>
				</td>
			</tr>
			</table>

			<div style="margin-top:12px"></div>
		    
			<div>' . $phrase['_package_weight'] . '</div>
			<div style="padding-top:3px"><input type="text" id="ship_weightlbs" name="ship_weightlbs" value="0" style="font-family: verdana" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;&nbsp;' . $phrase['_lbs'] . '&nbsp;&nbsp;&nbsp;<input type="text" id="ship_weightoz" name="ship_weightoz" value="0" style="font-family: verdana" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;&nbsp;' . $phrase['_oz'] . '</div>
		</div>
		<hr size="1" width="100%" style="color:#ccc; margin-bottom:7px; margin-top:7px" />		
		<div id="ship_method_service_options"><input type="hidden" value="' . $shippercount . '" id="shippercount" name="shippercount" />';	    
		for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
		{
			$html .= '
				<div id="ship_method_service_options_' . $i . '"' . (($i > 1) ? ' style="display:none"' : '') . '>				
					<table cellpadding="9" cellspacing="0" border="0" width="100%">
					<tr class="alt2">
						<td width="20%"><div class="smaller">' . $phrase['_ship_to'] . '</div></td>
						<td width="20%"><div class="smaller">' . $phrase['_services'] . ' ' . (($ilconfig['shippingapi']) ? '&nbsp;&nbsp;<span class="smaller blue"><a href="javascript:void(0)" onclick="javascript:jQuery(\'#shippingcalculator_modal\').jqm({modal: false}).jqmShow(); print_shipping_services(\'modal_shipperid_container\', \'modal_shipperid\', true, true)" style="text-decoration:underline">' . $phrase['_research_rates'] . '</a></span>' : '') . '</div></td>
						<td width="20%"><div class="smaller"><div id="ship_service_' . $i . '_css_cost"><div class="smaller">' . $phrase['_cost'] . '</div></div></td>
						<td width="20%"><div class="smaller"><div id="ship_service_' . $i . '_css_freeshipping"><div class="smaller">' . $phrase['_free_shipping'] . '?</div></div></td>
						<td width="20%" align="right"><div class="smaller">' . $phrase['_actions'] . '</div></td>
					</tr>
					<tr>
						<td valign="top">
<div>
<select id="ship_options_' . $i . '" name="ship_options_' . $i . '" style="font-family: verdana" onchange="javascript:
if (fetch_js_object(\'ship_options_' . $i . '\').value == \'\')
{
        toggle_hide(\'ship_options_custom_regionnav_' . $i . '\');
        print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, false);
}
else if (fetch_js_object(\'ship_options_' . $i . '\').value == \'worldwide\')
{
        toggle_hide(\'ship_options_custom_regionnav_' . $i . '\');
        print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true);
}
else if (fetch_js_object(\'ship_options_' . $i . '\').value == \'custom\')
{
        toggle_show(\'ship_options_custom_regionnav_' . $i . '\');
        print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', false, true);
}
else if (fetch_js_object(\'ship_options_' . $i . '\').value == \'domestic\')
{
        toggle_hide(\'ship_options_custom_regionnav_' . $i . '\');
        print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, false);
}" ' . ($disabled ? 'disabled="disabled"' : '') . '>
<option value="" selected="selected">-</option>
<option value="domestic">' . handle_input_keywords($ilconfig['registrationdisplay_defaultcountry']) . ' ' . $phrase['_only_lower'] . '</option>
<option value="worldwide">' . $phrase['_worldwide'] . '</option>
<option value="custom">' . $phrase['_choose_locations'] . '</option>
</select>
</div>
						</td>
						<td valign="top">
							<div>
								<div id="ship_service_' . $i . '_container">
									<select id="ship_service_' . $i . '" name="ship_service_' . $i . '" style="font-family: verdana" ' . ($disabled ? 'disabled="disabled"' : '') . '>
									<option value="0" selected="selected">-</option>
									</select>
								</div>
							</div>
						</td>
						<td valign="top" nowrap="nowrap"><div><div><span id="ship_service_' . $i . '_css_costsymbol">$</span> <input type="text" id="ship_fee_' . $i . '" name="ship_fee_' . $i . '" value="" style="font-family: verdana" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="ship_service_' . $i . '_css_costsymbol_right"></span></div></div></td>
						<td valign="top"><div><label for=""><input id="freeshipping_' . $i . '" type="checkbox" name="freeshipping_' . $i . '" value="1" ' . ($disabled ? 'disabled="disabled"' : '') . ' />&nbsp;<span id="ship_service_' . $i . '_css_freeshippinganswer">' . $phrase['_yes'] . '</span></label></div></td>
						<td valign="top" nowrap="nowrap" align="right">' . (($i > 1) ? '<div class="smaller blue"><a href="javascript:void(0)" onclick="remove_service(\'' . $i . '\')" style="text-decoration:underline">' . $phrase['_remove_service'] . '</a></div>' : '') . '</td>
					</tr>
					</table>
					<div style="margin-left:7px">
					<div id="ship_options_custom_regionnav_' . $i . '" style="display:none">					
						<table cellpadding="0" cellspacing="0" border="0" width="500">
						<tr valign="top">
						    <td><label for=""><input type="checkbox" id="ship_options_custom_region_' . $i . '_1" name="ship_options_custom_region_' . $i . '[]" value="north_america" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="ship_options_custom_region_' . $i . '_1_label">' . $phrase['_north_america'] . '</span></label><span id="ship_options_custom_region_' . $i . '_1_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">' . $phrase['_exclude'] . '..</a></span></td>
						    <td><label for=""><input type="checkbox" id="ship_options_custom_region_' . $i . '_2" name="ship_options_custom_region_' . $i . '[]" value="europe" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="ship_options_custom_region_' . $i . '_2_label">' . $phrase['_europe'] . '</span></label> <span id="ship_options_custom_region_' . $i . '_2_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">' . $phrase['_exclude'] . '..</a></span></td>
						    <td><label for=""><input type="checkbox" id="ship_options_custom_region_' . $i . '_3" name="ship_options_custom_region_' . $i . '[]" value="africa" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="ship_options_custom_region_' . $i . '_3_label">' . $phrase['_africa'] . '</span></label> <span id="ship_options_custom_region_' . $i . '_3_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">' . $phrase['_exclude'] . '..</a></span></td>
						</tr>
						<tr valign="top">
						    <td><label for=""><input type="checkbox" id="ship_options_custom_region_' . $i . '_4" name="ship_options_custom_region_' . $i . '[]" value="south_america" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="ship_options_custom_region_' . $i . '_4_label">' . $phrase['_south_america'] . '</span></label> <span id="ship_options_custom_region_' . $i . '_4_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">' . $phrase['_exclude'] . '..</a></span></td>
						    <td><label for=""><input type="checkbox" id="ship_options_custom_region_' . $i . '_5" name="ship_options_custom_region_' . $i . '[]" value="asia" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="ship_options_custom_region_' . $i . '_5_label">' . $phrase['_asia'] . '</span></label> <span id="ship_options_custom_region_' . $i . '_5_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">' . $phrase['_exclude'] . '..</a></span></td>
						    <td>&nbsp;</td>
						</tr>
						<tr valign="top">
						    <td><label for=""><input type="checkbox" id="ship_options_custom_region_' . $i . '_6" name="ship_options_custom_region_' . $i . '[]" value="oceania" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="ship_options_custom_region_' . $i . '_6_label">' . $phrase['_oceania'] . '</span></label> <span id="ship_options_custom_region_' . $i . '_6_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">' . $phrase['_exclude'] . '..</a></span></td>
						    <td><label for=""><input type="checkbox" id="ship_options_custom_region_' . $i . '_7" name="ship_options_custom_region_' . $i . '[]" value="antarctica" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="ship_options_custom_region_' . $i . '_7_label">' . $phrase['_antarctica'] . '</span></label> <span id="ship_options_custom_region_' . $i . '_7_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">' . $phrase['_exclude'] . '..</a></span></td>
						    <td>&nbsp;</td>
						</tr>
						</table>
					</div>
					</div>
					<hr size="1" width="100%" style="color:#cccccc; margin-top:7px; margin-bottom:7px" />					
				</div><!-- END service options ' . $i . ' -->';
		}
		
		$html .= '</div><!-- END service options --><div class="smaller blue" style="margin-top:3px"><a href="javascript:void(0)" onclick="add_additional_service()">' . $phrase['_offer_additional_service'] . '</a></div></div>';
                
                return $html;
        }
	
	/**
        * Function to print the shipping and handling duration and length in days it will take the seller to ship the item
        *
        * @param       boolean       disabled? (default false)
        *
        * @return      string        HTML representation of the shipping and handling form elements
        */
	function print_ship_handling_logic($disabled = false)
	{
		global $ilance, $ilconfig, $phrase, $onload, $project_id, $cid, $ilpage, $currencysymbol, $headinclude;
		 
		$html = '
		<table border="0" cellspacing="0" cellpadding="0">
		<tr valign="top">
			<td><div>' . $phrase['_handling_time'] . ' <div style="padding-top:3px">
			<select name="ship_handlingtime" id="ship_handlingtime" style="font-family: verdana" ' . ($disabled ? 'disabled="disabled"' : '') . '>
			<option value="1" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '1') ? 'selected="selected"' : '') . '>1 ' . $phrase['_day_lower'] . '</option>
			<option value="2" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '2') ? 'selected="selected"' : '') . '>2 ' . $phrase['_days_lower'] . '</option>
			<option value="3" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '3') ? 'selected="selected"' : '') . '>3 ' . $phrase['_days_lower'] . '</option>
			<option value="4" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '4') ? 'selected="selected"' : '') . '>4 ' . $phrase['_days_lower'] . '</option>
			<option value="5" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '5') ? 'selected="selected"' : '') . '>5 ' . $phrase['_days_lower'] . '</option>
			<option value="10" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '10') ? 'selected="selected"' : '') . '>10 ' . $phrase['_days_lower'] . '</option>
			<option value="15" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '15') ? 'selected="selected"' : '') . '>15 ' . $phrase['_days_lower'] . '</option>
			<option value="30" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '30') ? 'selected="selected"' : '') . '>30 ' . $phrase['_days_lower'] . '</option>
			</select></div></div></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			<td><div>' . $phrase['_handling_fee'] . ' <div style="padding-top:3px"><span id="ship_handlingfee_currency">$</span> <input type="text" name="ship_handlingfee" value="' . ((isset($ilance->GPC['ship_handlingfee'])) ? handle_input_keywords($ilance->GPC['ship_handlingfee']) : '') . '" id="ship_handlingfee" style="font-family: verdana; width:60px" class="input" ' . ($disabled ? 'disabled="disabled"' : '') . ' /> <span id="ship_handlingfee_currency_right"></span></div></div></td>
		</tr>
		</table>';
		 
		return $html;
	}
        
        /**
        * Function to print the list of shipping partners
        *
        * @return      string      
        */
        function print_return_policy($disabled = false)
        {
                global $ilance, $phrase, $ilconfig;
                
                $html = $returnpolicy = '';
                
                if (isset($ilance->GPC['returnaccepted']) AND $ilance->GPC['returnaccepted'] == '1')
                {
                        $return1 = 'checked="checked"';
                        $return0 = '';
                        $returnstyle = '';
                }
                else
                {
                        $return1 = '';
                        $return0 = 'checked="checked"';
                        $returnstyle = 'display:none';
                }
                
                if (isset($ilance->GPC['returnshippaidby']) AND $ilance->GPC['returnshippaidby'] == 'seller')
                {
                        $returnship1 = '';
                        $returnship2 = 'checked="checked"';
                }
                else
                {
                        $returnship1 = 'checked="checked"';
                        $returnship2 = '';
                }
                
                if (!empty($ilance->GPC['returnpolicy']))
                {
                        $returnpolicy = $ilance->GPC['returnpolicy'];        
                }
                
                $d0 = $d3 = $d7 = $d14 = $d30 = $d60 = '';
                if (empty($ilance->GPC['returnwithin']))
                {
                        $d0 = 'selected="selected"';
                }
                else
                {
                        if ($ilance->GPC['returnwithin'] == '3')
                        {
                                $d3 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returnwithin'] == '7')
                        {
                                $d7 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returnwithin'] == '14')
                        {
                                $d14 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returnwithin'] == '30')
                        {
                                $d30 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returnwithin'] == '60')
                        {
                                $d60 = 'selected="selected"';
                        }
                }
                
                $e0 = $e1 = $e2 = $e3 = '';
                if (empty($ilance->GPC['returngivenas']))
                {
                        $e0 = 'selected="selected"';
                }
                else
                {
                        if ($ilance->GPC['returngivenas'] == 'exchange')
                        {
                                $e1 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returngivenas'] == 'credit')
                        {
                                $e2 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returngivenas'] == 'moneyback')
                        {
                                $e3 = 'selected="selected"';
                        }
                }
                
                $dis = '';
                if ($disabled)
                {
                        $dis = 'disabled="disabled"';
                }
                
                $html = '<div>
                <table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                <tr class="alt1">
                        <td width="1%" valign="top"><input type="radio" name="returnaccepted" id="returnaccepted0" value="0" ' . $return0 . ' onclick="toggle_tr(\'returnpolicies\');" ' . $dis . ' /></td>
                        <td><div><label for="returnaccepted0"> <strong>' . $phrase['_returns_not_accepted'] . '</strong></label></div></td>
                </tr>
                <tr>
                        <td width="1%" valign="top"><input type="radio" name="returnaccepted" id="returnaccepted1" value="1" ' . $return1 . ' onclick="toggle_tr(\'returnpolicies\');" ' . $dis . ' /></td>
                        <td><div><label for="returnaccepted1"> <strong>' . $phrase['_returns_accepted'] . '</strong></label></div></td>
                </tr>
                <tr id="returnpolicies" style="' . $returnstyle . '">
                        <td width="1%"></td>
                        <td>
                        
                        <div>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding:0px 2px 15px 0px;" dir="' . $ilconfig['template_textdirection'] . '">
                                <tr>
                                <td>
                                <div class="grayborder" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_yellow_1x1000.gif) repeat-x;"><div class="n"><div class="e"><div class="w"></div></div></div><div>
                                
                                <div style="padding:9px">
                                <div><strong>' . $phrase['_item_must_be_returned_within'] . '</strong><div class="gray" style="padding-bottom:3px">' . $phrase['_specify_your_return_policy_in_days_a_buyer_has_to_return_the_item'] . '</div></div>
                                <div><select name="returnwithin" id="returnwithin" style="font-family: verdana" ' . $dis . '>
                                <option value="0" ' . $d0 . '>-</option>
                                <option value="3" ' . $d3 . '>3 ' . $phrase['_days'] . '</option>
                                <option value="7" ' . $d7 . '>7 ' . $phrase['_days'] . '</option>
                                <option value="14" ' . $d14 . '>14 ' . $phrase['_days'] . '</option>
                                <option value="30" ' . $d30 . '>30 ' . $phrase['_days'] . '</option>
                                <option value="60" ' . $d60 . '>60 ' . $phrase['_days'] . '</option>
                                </select></div>
                                
                                <div style="padding-top:8px"><hr size="1" width="100%" style="color:#cccccc" / ><strong>' . $phrase['_refund_will_be_provided_as'] . '</strong><div class="gray">' . $phrase['_specify_the_type_of_refund_type_the_buyer_will_receive'] . '</div></div>
                                <div><select name="returngivenas" id="returngivenas" style="font-family: verdana" ' . $dis . '>
                                <option value="" ' . $e0 . '>-</option>
                                <option value="exchange" ' . $e1 . '>' . $phrase['_exchange'] . '</option>
                                <option value="credit" ' . $e2 . '>' . $phrase['_credit'] . '</option>
                                <option value="moneyback" ' . $e3 . '>' . $phrase['_moneyback'] . '</option>
                                </select></div>
                                
                                <div style="padding-top:8px"><hr size="1" width="100%" style="color:#cccccc" / ><strong>' . $phrase['_return_shipping_paid_by'] . '</strong><div class="gray">' . $phrase['_please_decide_who_will_pay_for_return_shipping_costs'] . '</div></div>
                                <div><label for="returnshippaidby1"><input type="radio" name="returnshippaidby" id="returnshippaidby1" value="buyer" ' . $returnship1 . ' ' . $dis . ' /> ' . $phrase['_buyer'] . '</label></div>
                                <div><label for="returnshippaidby2"><input type="radio" name="returnshippaidby" id="returnshippaidby2" value="seller" ' . $returnship2 . ' ' . $dis . ' /> ' . $phrase['_seller'] . '</label></div>
                                
                                <div style="padding-top:8px; padding-bottom:3px"><hr size="1" width="100%" style="color:#cccccc" / ><strong>' . $phrase['_return_policy_details'] . '</strong></div>
                                <div class="ilance_wysiwyg">
                                <table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
                                <tr>
                                <td class="wysiwyg_wrapper" align="right" height="25">
                
                                        <table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                                        <tr>
                                                <td width="100%" align="left" class="smaller">' . $phrase['_plain_text_only_bbcode_is_currently_not_in_use_for_this_field'] . '</td>
                                                <td>
                                                                <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'returnpolicy\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . $phrase['_decrease_size'] . '" border="0" /></a></div>
                                                                <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'returnpolicy\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . $phrase['_increase_size'] . '" border="0" /></a></div>
                                                </td>
                                                <td style="padding-right:15px"></td>
                                        </tr>
                                        </table>
                                </td>
                                </tr>
                                        <tr>
                                                <td><textarea name="returnpolicy" id="returnpolicy" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg" ' . $dis . '>' . $returnpolicy . '</textarea></td>
                                        </tr>
                                </table>
                                </div>
                                </div>
                                
                                </div><div class="s"><div class="e"><div class="w"></div></div></div></div>
                                </td>
                                </tr>
                                </table>
                        </div>
                        

                </td>
                </tr>
                </table></div>';
                
                return $html;
        }
        
	/**
        * Function to print the list of shipping partners
        *
        * @return      string      
        */
        function print_shipping_partners($fieldname = 'shipperid', $hideshipperid = false, $domestic = true, $international = false, $shipperid = 0)
        {
                global $ilance, $phrase, $ilconfig;
                
                $html = $sqlextra = '';
		
		if ($domestic == 'true' AND $international == 'false')
		{
			$sqlextra = "WHERE domestic = '1' AND international = '0' ";
		}
		else if ($domestic == 'false' AND $international == 'true')
		{
			$sqlextra = "WHERE domestic = '0' AND international = '1' ";
		}
		
                $sql = $ilance->db->query("
                        SELECT shipperid, title
                        FROM " . DB_PREFIX . "shippers
			$sqlextra
                        ORDER BY title ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html .= '<select name="' . $fieldname . '" id="' . $fieldname . '" style="font-family: verdana">';
                        $html .= '<option value="">-</option>';
                        
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
				if ($res['shipperid'] == $shipperid)
				{
					$html .= ($hideshipperid == false)
						? '<option value="' . $res['shipperid'] . '" selected="selected">' . $res['title'] . '</option>'
						: '<option value="' . $res['title'] . '" selected="selected">' . $res['title'] . '</option>';
				}
				else
				{
					$html .= ($hideshipperid == false)
						? '<option value="' . $res['shipperid'] . '">' . $res['title'] . '</option>'
						: '<option value="' . $res['title'] . '">' . $res['title'] . '</option>';
				}
                        }
                        
                        $html .= '</select>';
                }
                
                return $html;
        }
	
        /**
        * Function to fetch the number of filters available for displaying within the auction posting interface.
        *
        * @param       integer      category id   
        *
        * @return      integer      return the count amount
        */
        function get_filters_quantity($cid = 0)
        {
                global $ilance, $show;
                
                $qty = 0;
                $show['advanced_profile_filters'] = false;
                
        	$sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "profile_questions
                        WHERE filtercategory = '" . intval($cid) . "'
                                AND visible = '1'
                                AND isfilter = '1'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                	$qty = 1;
                        $show['advanced_profile_filters'] = true;
                }
                
		return $qty;
        }
        
        /**
        * Function to print the auction preview
        *
        * @return      string      
        */
        function print_auction_preview()
        {
                global $ilance;
                
                $html = '<div style="padding-bottom:7px"><iframe name="preview_iframe" id="preview_iframe" width="99.5%" scrolling-bottom="yes" border="0" frameborder="0" class="" style="height:320px; border-top:1px solid blue; border-left:1px solid blue; border-right:1px solid blue; border-bottom:1px solid blue" src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER)  . 'ajax.php?do=previewlisting&mode=service"></iframe></div>';
                $html = '';
                
                return $html;
        }
        
        /**
        * Function to print the javascript on this form
        *
        * @return      string      
        */
        function print_js($cattype = '')
        {
                global $ilconfig, $show, $phrase, $onload;
                
                $js_start = '';
                if ($cattype == 'service')
                {
                        $js_start = '
<script type="text/javascript" language="Javascript">
<!--
function validateCB(theName)
{
	var counter = 0;
	var cb = document.getElementsByName(theName)
	for (i=0; i<cb.length; i++)
	{
		if ((cb[i].tagName == \'INPUT\') && (cb[i].type == \'checkbox\'))
		{
			if (cb[i].checked)
			counter++;
		}
	}	
	if (counter == 0)
	{  
		return false;
	}	
	return true;
}
function validate_title(f)
{
        haveerrors = 0;        
        if (fetch_js_object(\'project_title\').value == \'\')
        {
                haveerrors = 1;                
                showImage("project_titleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true);
                alert(phrase[\'_please_enter_a_title_for_this_listing\']);
                return(false);
        }
        else
        {
                showImage("project_titleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);                
                return(true);
        }
}
function validate_message()
{
        fetch_bbeditor_data();        
        if (fetch_js_object(\'description_id\').value == \'\')
        {
                alert(phrase[\'_please_enter_a_description_for_your_listing\']);
                return(false);        
        }
        return(true);
}
function validate_payment_method()
{
	var total = \'\';
        if (fetch_js_object(\'enableescrow2\').checked == true) 
        {
		if (!validateCB(\'paymethod[]\'))
		{
			alert(phrase[\'_you_have_selected_that_you_will_do_business_outside_the_marketplace\']);
			return(false);
		}
		total = \'1\';
        }
	if (total == \'\')
	{
		alert(phrase[\'_in_order_for_providers_to_know_how_you_will_pay_them_for_services\']);
		return(false);	
	}  
	return(true);
}
function validate_all()
{	
        return validate_title() && validate_message() && validatecustomform() && validate_payment_method();
}
//-->
</script>';        
                }
                else if ($cattype == 'product')
                {
                        $js_start = '
<script type="text/javascript" language="Javascript">
<!--
function validateCB(theName)
{
	var counter = 0;
	var cb = document.getElementsByName(theName)
	for (i=0; i<cb.length; i++)
	{
		if ((cb[i].tagName == \'INPUT\') && (cb[i].type == \'checkbox\'))
		{
			if (cb[i].checked)
			counter++;
		}
	}	
	if (counter == 0)
	{  
		return false;
	}	
	return true;
}
function validate_title()
{
        haveerrors = 0;        
        if (fetch_js_object(\'project_title\').value == \'\')
        {
                haveerrors = 1;                
                showImage("project_titleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true);
                alert(phrase[\'_please_enter_a_title_for_this_listing\']);
                return(false);
        }
        else
        {
                showImage("project_titleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);                
                return(true);
        }
}
function validate_message()
{
        fetch_bbeditor_data();        
        if (fetch_js_object(\'description_id\').value == \'\')
        {
                alert(phrase[\'_please_enter_a_description_for_your_listing\']);
                return(false);        
        }
        return(true);
}
function validate_selling_format()
{  
        var Chars = "0123456789.,";
        haveerrors = 0;       
        
        if (fetch_js_object(\'public\').checked || fetch_js_object(\'realtime\').checked)
        {
                if (fetch_js_object(\'filtered_auctiontype\').checked && fetch_js_object(\'filtered_auctiontype\').value == \'regular\') 
                {
                        if (fetch_js_object(\'startprice\').value == \'\' || fetch_js_object(\'startprice\').value <= 0)
                        {
                                alert(phrase[\'_you_must_enter_the_starting_bid_price_for_your_item\']);
                                return(false);
                        }                        
                        if (fetch_js_object(\'startprice\').value > 0 && fetch_js_object(\'reserve_price\').value != \'\' && fetch_js_object(\'reserve_price\').value > 0)
                        {
                                if (fetch_js_object(\'startprice\').value >= fetch_js_object(\'reserve_price\').value)
                                {
                                        alert(phrase[\'_your_reserve_price_cannot_be_less_or_equal\']);
                                        return(false);
                                }
                         }                        
			if (fetch_js_object(\'buynow_price\').value != \'\' && fetch_js_object(\'buynow_price\').value <= 0)
                        {
                                alert(phrase[\'_you_entered_a_buy_now_price_but_the_value_must_be_a_penny_or_more\']);
                                return(false);
                        }
			if (fetch_js_object(\'buynow_price\').value > 0 && fetch_js_object(\'reserve_price\').value != \'\' && fetch_js_object(\'reserve_price\').value > fetch_js_object(\'buynow_price\').value)
                        {
				alert(phrase[\'_your_reserve_price_cannot_exceed_your_buy_now_price\']);
				return(false);
                        }
			if (fetch_js_object(\'buynow_price\').value != \'\' && fetch_js_object(\'buynow_price\').value > 0 && fetch_js_object(\'startprice\').value != \'\' && fetch_js_object(\'startprice\').value > 0 && fetch_js_object(\'buynow_price\').value < fetch_js_object(\'startprice\').value)
                        {
				alert(phrase[\'_your_buy_now_price_cannot_be_less_or_equal_to_the_starting_bid_amount\']);
				return(false);
                        }
                        for (var i = 0; i < fetch_js_object(\'startprice\').value.length; i++)
                        {
                                if (Chars.indexOf(fetch_js_object(\'startprice\').value.charAt(i)) == -1)
                                {
                                        alert(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
                                        haveerrors = 1;
                                }
                        }                        
                        return(!haveerrors);                                
                }
                
                if (fetch_js_object(\'filtered_auctiontype0\').checked && fetch_js_object(\'filtered_auctiontype0\').value == \'fixed\') 
                {
                        if (fetch_js_object(\'buynow_price_fixed\').value == \'\' || fetch_js_object(\'buynow_price_fixed\').value <= \'0\')
                        {
                                alert(phrase[\'_you_must_enter_a_buy_now_price_for_your_item\']);
                                return(false);
                        }                        
                        if (fetch_js_object(\'buynow_qty_fixed\').value == \'\' || fetch_js_object(\'buynow_qty_fixed\').value <= \'0\')
                        {
                                alert(phrase[\'_you_must_enter_the_qty_available_for_your_buy_now_item\']);
                                return(false);
                        }                        
                        for (var i = 0; i < fetch_js_object(\'buynow_price\').value.length; i++)
                        {
                                if (Chars.indexOf(fetch_js_object(\'buynow_price_fixed\').value.charAt(i)) == -1)
                                {
                                        alert(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
                                        haveerrors = 1;
                                }
                        }                        
                        return(!haveerrors);
                }
        }
        else
        {
        	return(true);
        }    
        return(true);
}
function validate_bid_filters()
{	
        if (fetch_js_object(\'filter_city\').checked && fetch_js_object(\'filtered_city\').value == \'\') 
        {
                alert(phrase[\'_you_currently_have_city_town_filter_enabled_checkbox_is_on\']);
                return(false);
        }       
        if (fetch_js_object(\'filter_zip\').checked && fetch_js_object(\'filtered_zip\').value == \'\') 
        {
                alert(phrase[\'_you_currently_have_zip_postal_code_filter_enabled_checkbox_is_on\']);
                return(false);
        }        
        return(true);
}
function validate_shipping()
{
';
	$js_start .= '
	if (fetch_js_object(\'ship_method\').value == \'flatrate\')
	{';	
	for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
	{
		$js_start .= '
		if (fetch_js_object(\'ship_method_service_options_' . $i . '\').style.display == \'\')
		{
			if (fetch_js_object(\'ship_options_' . $i . '\').value == \'\')
			{
				alert(\'' . $phrase['_you_selected_flat_rate_shipping_but_forgot_to_include_locations_where'] . '\');
				return(false);
			}			
			if (fetch_js_object(\'ship_options_' . $i . '\').value == \'custom\')
			{
				if (fetch_js_object(\'ship_options_custom_region_' . $i . '_1\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_2\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_3\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_4\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_5\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_6\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_7\').checked == false)
				{
					alert(\'' . $phrase['_you_selected_custom_locations_for_shipping_service'] . ' ' . $i . ' ' . $phrase['_but_did_not_select_any_location_please_use_the_checkboxes'] . '\');
					return(false);
				}
			}			
			if (fetch_js_object(\'ship_service_' . $i . '\').value == \'0\' || fetch_js_object(\'ship_service_' . $i . '\').value == \'\')
			{
				alert(\'' . $phrase['_you_selected_flat_rate_shipping_but_did_not_select_any_shipping_service'] . '\');
				return(false);
			}
			if (fetch_js_object(\'freeshipping_' . $i . '\').checked == false && fetch_js_object(\'ship_fee_' . $i . '\').value == \'0\' || fetch_js_object(\'freeshipping_' . $i . '\').checked == false && fetch_js_object(\'ship_fee_' . $i . '\').value == \'\')
			{
				alert(\'' . $phrase['_you_selected_flat_rate_shipping_but_did_not_include_your_shipping_cost'] . '\');
				return(false);
			}
		}
		';
	}
	
$js_start .= '
	}
	else if (fetch_js_object(\'ship_method\').value == \'calculated\')
	{
	}
	else if (fetch_js_object(\'ship_method\').value == \'localpickup\')
	{	
	}	
	return(true);
}
function validate_return_policy()
{
        if (fetch_js_object(\'returnaccepted1\').checked == true && fetch_js_object(\'returnwithin\').value <= \'0\') 
        {
                alert(\'' . $phrase['_you_have_enabled_a_return_policy_for_this_listing_but_did_not_specify'] . '\');
                return(false);
        }        
        if (fetch_js_object(\'returnaccepted1\').checked == true && fetch_js_object(\'returngivenas\').value == \'\') 
        {
                alert(\'' . $phrase['_you_have_enabled_a_return_policy_for_this_listing_but_did_not_specify_the_refund'] . '\');
                return(false);
        }        
        if (fetch_js_object(\'returnaccepted1\').checked == true && fetch_js_object(\'returnpolicy\').value == \'\') 
        {
                alert(\'' . $phrase['_you_have_enabled_a_return_policy_for_this_listing_but_did_not_provide_your_return_policy_instruction'] . '\');
                return(false);
        }        
        return(true);
}
function validate_payment_method(formobj)
{
	var total = \'\';
	var total2 = \'\';
	var total3 = \'\';	
        if (fetch_js_object(\'enableescrow2\').checked == true)
	{		
		if (!validateCB(\'paymethod[]\'))
		{
			alert(phrase[\'_you_have_selected_that_you_will_do_business_outside_the_marketplace\']);
			return(false);
		}
		
		total = \'1\';
        }	
	if (fetch_js_object(\'enableescrow3\').checked == true)
	{
		formobj = formobj.elements;
		for (var c = 0, i = formobj.length - 1; i > -1; --i)
		{
			if (formobj[i].name && /^paymethodoptions\[\w+\]$/.test(formobj[i].name) && formobj[i].checked)
			{
				++c;
			}
		}
		if (c < 1)
		{
			alert(phrase[\'_you_have_selected_that_you_will_offer_buyers_a_direct_method_of_payment\']);
			return(false);
		}
		for (var d = 0, i = formobj.length - 1; i > -1; --i)
		{
			if (formobj[i].name && /^paymethodoptionsemail\[\w+\]$/.test(formobj[i].name) && formobj[i].value != \'\')
			{
				++d;
			}
		}
		if (d != c)
		{
			alert(phrase[\'_you_are_offering_direct_payment_gateway_for_buyers_but_did_not_enter\']);
			return(false);
		}		
		total2 = \'1\';
        }	
	if (fetch_js_object(\'enableescrow1\').checked == true)
	{
		total3 = \'1\';
	}	
	if (total == \'\' && total2 == \'\' && total3 == \'\')
	{
		alert(phrase[\'_in_order_to_sell_your_items_sucessfully_buyers_will_need_to_know_how\']);
		return(false);	
	}        
        return(true);
}
function validate_all(formobj)
{	
        return validate_selling_format() && validate_title() && validate_message() && validate_payment_method(formobj) && validatecustomform() && validate_shipping() && validate_bid_filters() && validate_return_policy();
}
function validate_all_bulk(formobj)
{	
        return validate_payment_method() && validate_shipping() && validate_bid_filters() && validate_return_policy() && disable_submit_button(formobj);
}
//-->
</script>
';        
                }
                
                return $js_start;
        }
        
        /**
        * Function to handle the revision log changes made to an auction event
        *
        * @param       string        category type (service or product)
        * @param       string        extra data to append
        *
        * @return      string      
        */
        function handle_revision_log_changes($cattype = 'service', $appendextra = '')
        {
                global $ilance, $phrase;
                
                $ilance->currency = construct_object('api.currency');
                
                $fieldphrases = array(
                        'project_title' => '_title',
                        'description' => '_description',
                        'additional_info' => '_additional_information',
                        'keywords' => '_keywords',
                        'bold' => '_bold',
                        'highlite' => '_listing_highlight',
                        'featured' => '_featured',
                        'filter_escrow' => '_escrow',
                        'paymethod' => '_pay_method',
                        'project_details' => '_event_access',
                        'bid_details' => '_bidding_privacy',
                        'filter_publicboard' => '_public_message_board',
                        'filtered_budgetid' => '_budget',
                        'filtered_auctiontype' => '_selling_format',
                        'reserve' => '_reserve',
                        'reserve_price' => '_reserve_price',
                        'startprice' => '_starting_price',
                        'buynow_price_fixed' => '_buy_now_price',
                        'buynow_qty' => '_qty',
                        'buynow_price' => '_buy_now_price',
                        'buynow_qty_fixed' => '_qty',
                        'retailprice' => '_retail_price',
                        'returngivenas' => '_refund_will_be_provided_as',
                        'returnaccepted' => '_returns_accepted',
                        'returnwithin' => '_item_must_be_returned_within',
                        'returnshippaidby' => '_return_shipping_paid_by',
                        'returnpolicy' => '_return_policy',
                );
                
                if ($cattype == 'service')
                {
                        $updatefields = array('custom','project_title','description','additional_info','keywords','bold','highlite','featured','filter_escrow','paymethod','project_details','bid_details','filter_publicboard','filtered_budgetid');
                }
                else if ($cattype == 'product')
                {
                        $updatefields = array('custom','startprice','reserve','reserve_price','buynow_price_fixed','buynow_price','buynow_qty_fixed','buynow_qty','retailprice','project_title','description','keywords','bold','highlite','featured','filter_escrow','paymethod','project_details','bid_details','filter_publicboard','filtered_auctiontype','returnaccepted','returnwithin','returngivenas','returnshippaidby','returnpolicy');
                }
                
                $log = '';
                //print_r($ilance->GPC);

                foreach ($updatefields AS $key)
                {
                		if($ilance->GPC['filtered_auctiontype'] == 'fixed' AND ($key == 'reserve_price' OR $key == 'reserve' OR $key == 'startprice' OR $key == 'buynow_price' OR $key == 'buynow_qty'))
                		{
                			continue;
                		}
                	
                        if ($key == 'description')
                        { // we need this here because of our custom wysiwyg editor moving text into hidden div called 'xyz' for posting
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC['description']) AND trim($ilance->GPC['old'][$key]) != trim($ilance->GPC['description']))
                                {
                                        $log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . $ilance->GPC['old'][$key] . '</span> <br /><br />' . $phrase['_to_upper'] . ': ' . $ilance->GPC['description'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                }
                        }
                        else if ($key == 'buynow_price_fixed')
                        {
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC['buynow_price']) AND $ilance->GPC['old'][$key] != $ilance->GPC['buynow_price'])
                                {
                                	if($ilance->GPC['old'][$key] != 0 AND $ilance->GPC['buynow_price'] == '')
                                	{
                                        $log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . $ilance->GPC['old'][$key] . '</span> ' . $phrase['_to_upper'] . ': ' . ($ilance->GPC['buynow_price'] == 0 OR $ilance->GPC['buynow_price'] = '') ? $ilance->GPC['buynow_price'] = 0 :$ilance->GPC['buynow_price'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                	}
                                }
                        }
                        else if ($key == 'buynow_qty_fixed')
                        {
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC['buynow_qty']) AND $ilance->GPC['old'][$key] != $ilance->GPC['buynow_qty'])
                                {
                                        $log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . $ilance->GPC['old'][$key] . '</span> ' . $phrase['_to_upper'] . ': ' . $ilance->GPC['buynow_qty'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                }
                        }
                        else if ($key == 'filter_escrow')
                        {
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
                                {
                                        $log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . print_boolean($ilance->GPC['old'][$key]) . '</span> ' . $phrase['_to_upper'] . ': ' . print_boolean($ilance->GPC[$key]) . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                }        
                        }
                        else if ($key == 'filter_publicboard')
                        {
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
                                {
                                        $log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . print_boolean($ilance->GPC['old'][$key]) . '</span> ' . $phrase['_to_upper'] . ': ' . print_boolean($ilance->GPC[$key]) . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                }        
                        }
                    	else if ($key == 'returnwithin')
                        {
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
                                {
                                        $log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . intval($ilance->GPC['old'][$key]) . ' ' . $phrase['_days'] . '</span> ' . $phrase['_to_upper'] . ': ' . intval($ilance->GPC[$key]) . ' ' . $phrase['_days'] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                }        
                        }
                        else if ($key == 'returngivenas' OR $key == 'returnshippaidby')
                        {
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
                                {
                                    if($ilance->GPC['old'][$key] != 'none' AND $ilance->GPC[$key] != '')
                                    {
                                		$log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . $ilance->GPC['old']["$key"] . '</span> ' . $phrase['_to_upper'] . ': ' . $ilance->GPC["$key"] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                    }
                                }        
                        }                        
                        else if ($key == 'returnaccepted')
                        {
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
                                {
                                        $log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . print_boolean($ilance->GPC['old']["$key"]) . '</span> ' . $phrase['_to_upper'] . ': ' . print_boolean($ilance->GPC["$key"]) . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                }        
                        }
                        else if ($key == 'custom')
                        {
                                //if (isset($ilance->GPC['old'][$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
                                //{
                                        //$log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . print_boolean($ilance->GPC['old'][$key]) . '</span> ' . $phrase['_to_upper'] . ': ' . print_boolean($ilance->GPC[$key]) . '</div><hr size="1" width="100%" style="color:#ebebeb" />';
                                //}        
                        }
                        else if ($key == 'reserve_price')
                        {
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->currency->format($ilance->GPC['old'][$key]) != $ilance->currency->format($ilance->GPC[$key]))
                                {
                                        $log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . $ilance->GPC['old'][$key] . '</span> ' . $phrase['_to_upper'] . ': ' . $ilance->GPC[$key] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                }        
                        }
                        else if ($key == 'paymethod')
                        {
                        	if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]))
                        	{
                        		$opt_old = $opt = '';
                        		foreach($ilance->GPC['old'][$key] AS $key2 => $value)
                        		{
                        			$opt_old .= (empty($opt)) ? $value : ', '.$value;
                        		}
                        		foreach($ilance->GPC[$key] AS $key2 => $value)
                        		{
                        			$opt .= (empty($opt)) ? $value : ', '.$value;
                        		}
                        		
                        		for($i=0;$i<10;$i++)
                        		{
                        			if(isset($ilance->GPC['old'][$key][$i]))
                        			{
                        				if(isset($ilance->GPC[$key][$i]))
                        				{
                        					if($ilance->GPC['old'][$key][$i] != $ilance->GPC[$key][$i])
                        					{
                        						$log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . $opt_old . '</span> ' . $phrase['_to_upper'] . ': ' . $opt . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                        						break;
                        					}
                        				}
                        				else
                        				{
                        					$log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . $opt_old . '</span> ' . $phrase['_to_upper'] . ': ' . $opt . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                        					break;	
                        				}
                        			}
                        			else 
                        			{
                        				if(isset($ilance->GPC[$key][$i]))
                        				{
                        					$log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . $opt_old . '</span> ' . $phrase['_to_upper'] . ': ' . $opt . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                        					break;	
                        				}
                        				else 
                        				{
                        					break;
                        				}
                        			}
                        		}
                        	}
                        }
						else
                        {
                                if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
                                {
                                        $log .= '<div><strong>' . $phrase[$fieldphrases[$key]] . '</strong></div><div style="padding-top:3px">' . $phrase['_from'] . ': <span class="gray">' . $ilance->GPC['old'][$key] . '</span> ' . $phrase['_to_upper'] . ': ' . $ilance->GPC[$key] . '</div><hr size="1" width="100%" style="color:#cccccc" />';
                                }
                        }
                }
                                
                if (!empty($log))
                {
                        // remove trailing <hr>
                        $log = mb_substr($log, 0, -50);
                        
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "projects_changelog
                                (id, project_id, datetime, changelog)
                                VALUES(
                                NULL,
                                '" . intval($ilance->GPC['rfpid']) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($log) . "')
                        ");
                }        
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>