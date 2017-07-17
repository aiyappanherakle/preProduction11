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
* Class to handle the auction posting interface for any type of auction supported in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class auction_questions extends auction
{
        function fetch_custom_listing_answer($projectid = 0, $formname = '', $type = 'service')
        {
                global $ilance, $phrase, $ilconfig, $show;
                
                $html = '-';
                
                $table = ($type == 'service') ? 'project_answers' : 'product_answers';
                $qid = ($type == 'service') ? $ilance->db->fetch_field(DB_PREFIX . "project_questions", "formname = '" . $ilance->db->escape_string($formname) . "'", "questionid") : $ilance->db->fetch_field(DB_PREFIX . "product_questions", "formname = '" . $ilance->db->escape_string($formname) . "'", "questionid");
                
                $sql = $ilance->db->query("
                        SELECT answer
                        FROM " . DB_PREFIX . $table . "
                        WHERE questionid = '" . intval($qid) . "'
                                AND project_id = '" . intval($projectid) . "'
                                AND visible = '1'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        
                        if (is_serialized($res['answer']))
                        {
                                $html = unserialize($res['answer']);
                                $html = $html[0];
                        }
                        else
                        {
                                $html = trim($res['answer']);
                        }
                }
                
                return $html;
        }
        
        /**
        * Function to handle all answerable auction questions within the posting system.
        *
        * @param       integer       category id
        * @param       integer       project id
        * @param       string        display mode (input, preview, update, output)
        * @param       string        category type (service or product)
        *
        * @return      string        HTML representation of the custom auction questions
        */
        function construct_auction_questions($cid = 0, $projectid = 0, $mode = '', $type = '', $columns = 4)
        {
                global $ilance, $ilpage, $myapi, $phrase, $headinclude, $ilconfig, $show;
                
                $table1 = ($type == 'service') ? 'project_questions' : 'product_questions';
                $table2 = ($type == 'service') ? 'project_answers' : 'product_answers';
                
                $html = '';
                $cols = 0;
                
                $pid = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "parentid");
                $extracids = "AND (cid = '" . intval($cid) . "' OR cid = '-1')";
                $var = $ilance->categories->fetch_parent_ids($cid);
                $explode = explode(',', $var);
                if (in_array($pid, $explode))
                {
                        $extracids = "AND (FIND_IN_SET(cid, '$var') AND recursive = '1' OR cid = '-1')";
                }
                unset($explode, $var);
                
                // #### QUESTION DISPLAY TYPE ##################################

                // #### input mode #############################################
                if ($mode == 'input')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . $table1 . "
                                WHERE visible = '1'
                                        $extracids
                                ORDER BY sort
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $show['categoryfindertable'] = true;
                                
                                $headinclude .= "
<script type=\"text/javascript\">
function customImage(imagename, imageurl, errors)
{
        document[imagename].src = imageurl;
        if (!haveerrors && errors)
        {
                haveerrors = errors;
                alert(phrase['_please_fix_the_fields_marked_with_a_warning_icon_and_retry_your_action']);
        }
}
function validatecustomform(f)
{
        haveerrors = 0;
";
                                $isrequiredjs = '';
                                $num = 0;                                
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $formdefault = '';
                                        if (isset($res['formdefault']) AND $res['formdefault'] != '')
                                        {
                                                $formdefault = $res['formdefault'];
                                        }
                                        
                                        $overridejs = 0;
                                        switch ($res['inputtype'])
                                        {
                                                case 'yesno':
                                                {
                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked"> ' . $phrase['_yes'] . '</label> <label for="' . $res['formname'] . '0"><input type="radio" id="' . $res['formname'] . '0" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0"> ' . $phrase['_no'] . '</label>';
                                                        $overridejs = 1;
                                                        break;
                                                }                                
                                                case 'int':
                                                {
                                                        $input = '<input class="input" size="3" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" />';
                                                        break;
                                                }
                                                case 'textarea':
                                                {
                                                        $input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $formdefault . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">' . $phrase['_increase_size'] . '</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">' . $phrase['_decrease_size'] . '</a></div></div>';
                                                        break;
                                                }                                
                                                case 'text':
                                                {
                                                        $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" />';
                                                        break;
                                                }
                                                case 'url':
                                                {
                                                        $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" />';
                                                        break;
                                                }                                                        
                                                case 'multiplechoice':
                                                {
                                                        if (!empty($res['multiplechoice']))
                                                        {
                                                                $choices = explode('|', $res['multiplechoice']);
                                                                $input = $phrase['_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices'] . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                $input .= '<option value="">-</option>';
                                                                $input .= '<optgroup label="' . $phrase['_select'] . ':">';
                                                                foreach ($choices AS $choice)
                                                                {
                                                                        if (!empty($choice))
                                                                        {
                                                                                $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                        }
                                                                }
                                                                $input .= '</optgroup>';
                                                                $input .= '</select>';
                                                        }
                                                        break;
                                                }                                                    
                                                case 'pulldown':
                                                {
                                                        if (!empty($res['multiplechoice']))
                                                        {
                                                                $choices = explode('|', $res['multiplechoice']);
                                                                $input = '<select style="font-family: verdana" name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                $input .= '<option value="">-</option>';
                                                                foreach ($choices AS $choice)
                                                                {
                                                                        if (!empty($choice))
                                                                        {
                                                                                $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                        }
                                                                }
                                                                $input .= '</select>';
                                                        }
                                                        break;
                                                }                                                
                                        }
                                        
                                        $isrequired = '';
                                        if ($res['required'] AND $overridejs == 0)
                                        {
                                                $questionid = $res['questionid'];
                                                $formname = $res['formname'];
                                                if (isset($ilance->GPC['custom'][$questionid][$formname]) AND $ilance->GPC['custom'][$questionid][$formname] == $res['formdefault'])
                                                {
                                                        $isrequired .= '<img name="' . stripslashes($res['formname']) . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif" width="21" height="13" border="0" alt="' . $phrase['_this_form_field_is_required'] . '" />';
                                                }
                                                else 
                                                {
                                                        $isrequired .= '<img name="' . stripslashes($res['formname']) . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . $phrase['_this_form_field_is_required'] . '" />';
                                                }
                                        }
                                        
                                        if ($cols == 0)
                                        {
                                                $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                        }

                                        $html .= '<td width="25%" valign="top"><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong><div class="gray" style="padding-bottom:3px">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</div><div>' . $input . ' ' . $isrequired . '</div></div><div style="padding-bottom:7px"></div></td>';
                                        
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
                                
                                $headinclude .= $isrequiredjs;
                                $headinclude .= "\nreturn (!haveerrors);\n}\n</script>\n";
                        }
                        else
                        {
                                $show['categoryfindertable'] = false;
                                $html = '';
                                $headinclude .= "<script type=\"text/javascript\">function validatecustomform(f){return true;}</script>\n";
                        }
                }
                
                // #### update mode ############################################
                else if ($mode == 'update')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . $table1 . "
                                WHERE visible = '1'
                                        $extracids
                                ORDER BY sort
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $show['categoryfindertable'] = true;
                                
                                $c = 0;
                                $headinclude .= "
<script type=\"text/javascript\">
function customImage(imagename, imageurl, errors)
{
        document[imagename].src = imageurl;
        if (!haveerrors && errors)
        {
                haveerrors = errors;
                alert(phrase['_please_fix_the_fields_marked_with_a_warning_icon_and_retry_your_action']);
        }
}
function validatecustomform(f)
{
        haveerrors = 0;
";
                                $isrequiredjs = $isrequired = '';
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $answertoinput = '';
                                        $sql2 = $ilance->db->query("
                                                SELECT answer
                                                FROM " . DB_PREFIX . $table2 . "
                                                WHERE questionid = '" . $res['questionid'] . "'
                                                        AND project_id = '" . intval($projectid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
                                                $answertoinput = stripslashes($res2['answer']);
                                        }
                                        
                                        $formdefault = '';
                                        if (isset($res['formdefault']) AND $res['formdefault'] != '')
                                        {
                                                $formdefault = $res['formdefault'];
                                        }
                                        
                                        $overridejs = 0;
                                        switch ($res['inputtype'])
                                        {
                                                case 'yesno':
                                                {
                                                        if (!empty($answertoinput))
                                                        {
                                                                if ($answertoinput == 1)
                                                                {
                                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> ' . $phrase['_yes'] . '</label><label for="' . $res['formname'] . '0"><input type="radio" id="' . $res['formname'] . '0" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> ' . $phrase['_no'] . '</label>';
                                                                }
                                                                else
                                                                {
                                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" /> ' . $phrase['_yes'] . '</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" checked="checked" /> ' . $phrase['_no'] . '</label>';
                                                                }
                                                        }
                                                        else
                                                        {
                                                                $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> ' . $phrase['_yes'] . '</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> ' . $phrase['_no'] . '</label>';
                                                        }
                                                        $overridejs = 1;
                                                        break;
                                                }                                
                                                case 'int':
                                                {
                                                        //$input = '<input class="input" id="' . $res['formname'] . '" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . htmlentities($answertoinput, ENT_QUOTES) . '" />';
                                                        $input = '<input class="input" id="' . $res['formname'] . '" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . ilance_htmlentities($answertoinput) . '" />';
                                                        break;
                                                }
                                                case 'textarea':
                                                {
                                                        //$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . htmlentities($answertoinput, ENT_QUOTES) . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">' . $phrase['_increase_size'] . '</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">' . $phrase['_decrease_size'] . '</a></div></div>';
                                                        $input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . ilance_htmlentities($answertoinput) . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">' . $phrase['_increase_size'] . '</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">' . $phrase['_decrease_size'] . '</a></div></div>';
                                                        break;
                                                }                                
                                                case 'text':
                                                {
                                                        //$input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . htmlentities($answertoinput, ENT_QUOTES) . '" />';
                                                        $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . ilance_htmlentities($answertoinput) . '" />';
                                                        break;
                                                }
                                                case 'url':
                                                {
                                                        //$input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . htmlentities($answertoinput, ENT_QUOTES) . '" />';
                                                        $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . ilance_htmlentities($answertoinput) . '" />';
                                                        break;
                                                }                                                
                                                case 'multiplechoice':
                                                {
                                                        if (!empty($res['multiplechoice']))
                                                        {
                                                                $choices = explode('|', $res['multiplechoice']);
                                                                
                                                                $input = $phrase['_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices'] . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                $input .= '<optgroup label="' . $phrase['_select'] . ':">';
                                                                
                                                                if (empty($answertoinput))
                                                                {
                                                                        $answers = array();
                                                                }
                                                                else
                                                                {
                                                                        $answers = unserialize($answertoinput);
                                                                }
                                                                
                                                                foreach ($choices AS $choice)
                                                                {
                                                                        if (in_array($choice, $answers))
                                                                        {
                                                                                //$input .= '<option value="' . trim(htmlentities($choice, ENT_QUOTES)) . '" selected="selected">' . trim(htmlentities($choice, ENT_QUOTES)) . '</option>';
                                                                                $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '" selected="selected">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                        }
                                                                        else
                                                                        {
                                                                                //$input .= '<option value="' . trim(htmlentities($choice, ENT_QUOTES)) . '">' . trim(htmlentities($choice, ENT_QUOTES)) . '</option>';
                                                                                $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                        }
                                                                }
                                                                $input .= '</optgroup>';
                                                                $input .= '</select>';
                                                        }
                                                        break;
                                                }
                                                case 'pulldown':
                                                {
                                                        if (!empty($res['multiplechoice']))
                                                        {
                                                                $choices = explode('|', $res['multiplechoice']);
                                                                $input = '<select style="font-family: verdana" name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="'.$res['formname'].'">';
                                                                if (empty($answertoinput))
                                                                {
                                                                        $answers = array();
                                                                }
                                                                else
                                                                {
                                                                        $answers = unserialize($answertoinput);
                                                                }
                                                                $input .= '<option value="">-</option>';
                                                                foreach ($choices AS $choice)
                                                                {
                                                                        if (isset($answers[0]) AND $choice == $answers[0])
                                                                        {
                                                                                //$input .= '<option value="' . trim(htmlentities($choice, ENT_QUOTES)) . '" selected="selected">' . trim(htmlentities($choice, ENT_QUOTES)) . '</option>';
                                                                                $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '" selected="selected">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                        }
                                                                        else
                                                                        {
                                                                                $input .= '<option value="' . trim(ilance_htmlentities($choice)) . '">' . trim(ilance_htmlentities($choice)) . '</option>';
                                                                        }
                                                                }
                                                                $input .= '</select>';
                                                        }
                                                        break;
                                                }
                                        }
                                        
                                        $isrequired = '';
                                        if ($res['required'] AND $overridejs == 0)
                                        {
                                                $isrequired .= '<img name="' . stripslashes($res['formname']) . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . $phrase['_this_form_field_is_required'] . '" />';
                                                $isrequiredjs .= "\n(fetch_js_object('" . stripslashes($res['formname']) . "').value.length < 1) ? customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . stripslashes($res['formname']) . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
                                        }
                                        
                                        if ($cols == 0)
                                        {
                                                $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                        }

                                        $html .= '<td width="25%" valign="top"><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong><div class="gray" style="padding-bottom:3px">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</div><div>' . $input . ' ' . $isrequired . '</div></div><div style="padding-bottom:7px"></div></td>';
                                        
                                        $cols++;
                                        
                                        if ($cols == $columns)
                                        {
                                                $html .= '</tr>';
                                                $cols = 0;
                                        }
                                        
                                        $c++;
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
                                
                                $headinclude .= $isrequiredjs;
                                $headinclude .= "\nreturn (!haveerrors);\n}\n</script>\n";
                        }
                        else
                        {
                                $show['categoryfindertable'] = false;
                                $headinclude .= "<script type=\"text/javascript\">function validatecustomform(f) { return true; }</script>\n";
                        }
                }
                
                // #### output mode ############################################
                else if ($mode == 'output')
                {
                        $show['itemspecifics'] = false;
                        
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . $table1 . "
                                WHERE visible = '1'
                                        $extracids
                                ORDER BY sort
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $c = 0;
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $answer = '';
                                        $sql2 = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . $table2 . "
                                                WHERE questionid = '" . $res['questionid'] . "'
                                                        AND project_id = '" . intval($projectid) . "'
                                                        AND visible = '1'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
                                                $answer = $res2['answer'];
                                        }
                                        
                                        // if answer is empty don't show it
                                        if (isset($answer) AND (!empty($answer) OR $answer != ''))
                                        {
                                                $show['itemspecifics'] = true;
                                                
                                                if ($cols == 0)
                                                {
                                                        $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                                }

                                                $html .= '<td width="25%" valign="top"><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong>: <span class="gray">';
                                                //$html .= '<td width="25%" valign="top">';
                                                // input type switch display
                                                switch ($res['inputtype'])
                                                {
                                                        case 'yesno':
                                                        {
                                                                if ($answer == 1)
                                                                {
                                                                        $html .= $phrase['_yes'];
                                                                }
                                                                else
                                                                {
                                                                        $html .= $phrase['_no'];
                                                                }
                                                                break;
                                                        }
                                                        case 'int':
                                                        {
                                                                $html .= $answer . '&nbsp;';
                                                                break;
                                                        }                                        
                                                        case 'textarea':
                                                        {
                                                                //$html .= htmlentities(stripslashes($answer), ENT_QUOTES) . '&nbsp;';
                                                                $html .= ilance_htmlentities(stripslashes($answer)) . '&nbsp;';
                                                                break;
                                                        }                                        
                                                        case 'text':
                                                        {
                                                                //$html .= htmlentities(stripslashes($answer), ENT_QUOTES) . '&nbsp;';
                                                                $html .= ilance_htmlentities(stripslashes($answer)) . '&nbsp;';
                                                                break;
                                                        }
                                                        case 'url':
                                                        {
                                                                //$html .= '<a href="http://' . htmlentities(stripslashes($answer), ENT_QUOTES) . '" target="_blank">' . htmlentities(stripslashes($answer), ENT_QUOTES) . '</a>&nbsp;';
                                                                $html .= '<a href="http://' . ilance_htmlentities(stripslashes($answer)) . '" target="_blank">' . ilance_htmlentities(stripslashes($answer)) . '</a>&nbsp;';
                                                                break;
                                                        }                                                        
                                                        case 'multiplechoice':
                                                        {
                                                                if (!empty($answer) OR $answer != '')
                                                                {
                                                                        $answers = unserialize($answer);
                                                                        $fix = '';
                                                                        foreach ($answers AS $answered)
                                                                        {
                                                                                //$fix .= htmlentities(stripslashes($answered), ENT_QUOTES) . ', ';
                                                                                $fix .= ilance_htmlentities(stripslashes($answered)) . ', ';
                                                                        }
                                                                        $html .= mb_substr($fix, 0, -2);
                                                                }
                                                                else
                                                                {
                                                                        $html .= '&nbsp;';
                                                                }
                                                                break;
                                                        }
                                                        case 'pulldown':
                                                        {
                                                                if (!empty($answer) OR $answer != '')
                                                                {
                                                                        $answers = unserialize($answer);
                                                                        $fix = '';
                                                                        foreach ($answers AS $answered)
                                                                        {
                                                                                //$fix .= htmlentities(stripslashes($answered), ENT_QUOTES);
                                                                                $fix .= ilance_htmlentities(stripslashes($answered));
                                                                        }
                                                                        if (empty($fix))
                                                                        {
                                                                                if ($type == 'product')
                                                                                {
                                                                                        $html .= ($show['is_owner'] ? '[ <span style="" class="smaller blue"><a href="' . $ilpage['selling'] . '?cmd=product-management&amp;state=' . $type . '&amp;id=' . intval($ilance->GPC['id']) . '#categoryfinder">' . $phrase['_edit'] . '</a></span> ]' : '-');
                                                                                }
                                                                                else
                                                                                {
                                                                                        $html .= ($show['is_owner'] ? '[ <span style="" class="smaller blue"><a href="' . $ilpage['buying'] . '?cmd=rfp-management&amp;state=' . $type . '&amp;id=' . intval($ilance->GPC['id']) . '#categoryfinder">' . $phrase['_edit'] . '</a></span> ]' : '-');
                                                                                }
                                                                        }
                                                                        else
                                                                        {
                                                                                $html .= $fix;
                                                                        }
                                                                }
                                                                else
                                                                {
                                                                        $html .= '&nbsp;';
                                                                }
                                                                break;
                                                        }
                                                }                                                        
                                                $html .= '</span></div></td>';
                                                
                                                $cols++;
                                                $c++;
                                                
                                                if ($cols == $columns)
                                                {
                                                        $html .= '</tr>';
                                                        $cols = 0;
                                                }
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
                }
                
                if ($html != '')
                {
                        $html = '<table border="0" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">' . $html . '</table>';
                }
                
                return $html;
        }    
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>