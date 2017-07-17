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
* Registration questions class to perform displaying and updating for registration questions within ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class registration_questions
{
        /**
        * Function for displaying custom registration questions based on the pages within registration the admin has predefined.
        *
        * @param       integer       page number
        * @param       string        mode (input, updateprofile, updateprofileadmin, update and output1)
        * @param       integer       user id
        *
        * @return      string        HTML representation of the question registration question
        */
        function construct_register_questions($pageid = 1, $mode = '', $userid = 0)
        {
                global $ilance, $myapi, $phrase, $headinclude, $ilconfig, $ilpage;
                
                if ($mode == 'input')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "register_questions
                                WHERE pageid = '" . intval($pageid) . "'
                                    AND visible = '1'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $c = 0;
                                $html = $isrequiredjs = $isrequired = '';
                
                                // enable custom header javascript
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
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        if (isset($res['formdefault']) AND $res['formdefault'] != '')
                                        {
                                                $formdefault = $res['formdefault'];
                                        }
                                        else
                                        {
                                                $formdefault = '';
                                        }
                    
                                        switch ($res['inputtype'])
                                        {
                                                case 'yesno':
                                                {
                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked"> ' . $phrase['_yes'] . '</label> <label for="' . $res['formname'] . '2"><input type="radio" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0"> ' . $phrase['_no'] . '</label>';
                                                        break;
                                                }                        
                                                case 'int':
                                                {
                                                        $input = '<input class="input" size="3" type="text" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" style="font-family: verdana" />';
                                                        break;
                                                }
                                                case 'textarea':
                                                {
                                                        //$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $formdefault . '</textarea><br /> <div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">'.$phrase['_increase_size'].'</a>&nbsp; <a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">'.$phrase['_decrease_size'].'</a></div></div>';
                                                        $input = '
                                                        <style id="wysiwyg_html" type="text/css">
                                                        <!--
                                                        ' . $ilance->styles->css_cache['csswysiwyg'] . '
                                                        //-->
                                                        </style>
                                                        <div class="ilance_wysiwyg">
                                                        <table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
                                                        <tr>
                                                        <td class="wysiwyg_wrapper" align="right" height="25">
                                        
                                                                <table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                                                                <tr>
                                                                        <td width="100%" align="left" class="smaller">' . $phrase['_plain_text_only_bbcode_is_currently_not_in_use_for_this_field'] . '</td>
                                                                        <td>
                                                                                        <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . $phrase['_decrease_size'] . '" border="0" /></a></div>
                                                                                        <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . $phrase['_increase_size'] . '" border="0" /></a></div>
                                                                        </td>
                                                                        <td style="padding-right:15px"></td>
                                                                </tr>
                                                                </table>
                                                        </td>
                                                        </tr>
                                                                <tr>
                                                                        <td><textarea id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $formdefault . '</textarea></td>
                                                                </tr>
                                                        </table>
                                                        </div>';
                                                        
                                                        break;
                                                }                        
                                                case 'text':
                                                {
                                                        $input = '<input class="input" type="text" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $formdefault . '" style="font-family: verdana" />';
                                                        break;
                                                }                                                
                                                case 'multiplechoice':
                                                {
                                                        if (!empty($res['multiplechoice']))
                                                        {
                                                                $choices = explode('|', $res['multiplechoice']);
                                                                $input = $phrase['_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices'] . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']">';
                                                                $input .= '<optgroup label="' . $phrase['_select'] . '">';
                                                                foreach ($choices as $choice)
                                                                {
                                                                        if (!empty($choice))
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
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
                                                                $input = '<select name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="font-family: verdana">';
                                                                foreach ($choices as $choice)
                                                                {
                                                                        if (!empty($choice))
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
                                                                        }
                                                                }
                                                                $input .= '</select>';
                                                        }
                                                        break;
                                                }
                                        }

                                        if ($res['required'])
                                        {
                                                $questionid = $res['questionid'];
                                                $formname = $res['formname'];
                                                
                                                if (isset($_POST['custom'][$questionid][$formname]) AND $_POST['custom'][$questionid][$formname] == $res['formdefault '])
                                                {
                                                        $isrequired = '<img name="custom[' . $res["questionid"] . '][' . $res["formname"] . ']error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif" width="21" height="13" border="0" alt="' . $phrase['_this_form_field_is_required'] . '" />';
                                                }
                                                else 
                                                {
                                                        $isrequired = '<img name="custom[' . $res["questionid"] . '][' . $res["formname"] . ']error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . $phrase['_this_form_field_is_required'] . '" />';
                                                }
                                        }
                                        else
                                        {
                                                $isrequired = '';
                                        }
                    
                                        $html .= '
                                        <table width="100%"  border="0" cellspacing="3" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
                                        <tr>
                                                <td><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong></div></td>
                                        </tr>
                                        <tr>
                                                <td><div class="gray" style="padding-bottom:3px">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</div>' . $input . ' ' . $isrequired . '</td>
                                        </tr>
                                        </table>
                                        <div style="padding-bottom:9px"></div>';
                                        $c++;
                                }
                                
                                $headinclude .= $isrequiredjs;
                                $headinclude .= "\nreturn (!haveerrors);\n}\n</script>\n";
                        }
                        else
                        {
                                $html = '';
                                $headinclude .= "<script type=\"text/javascript\">function validatecustomform(f){return true;}</script>\n";
                        }
                }
                else if ($mode == 'updateprofile')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "register_questions
                                WHERE visible = '1'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $c = 0;
                                $html = $isrequiredjs = $isrequired = '';
                
                                // enable custom header javascript
                                $headinclude .= "
<script type=\"text/javascript\">
<!--
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
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        // do we have an answer?
                                        $answertoinput = $formdefault = '';
                                        
                                        $sql2 = $ilance->db->query("
                                                SELECT answer
                                                FROM " . DB_PREFIX . "register_answers
                                                WHERE questionid = '" . $res['questionid'] . "'
                                                    AND user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $res2 = $ilance->db->fetch_array($sql2);
                                                $answertoinput = stripslashes($res2['answer']);
                                        }
                                        
                                        if (!empty($res['formdefault']))
                                        {
                                                $formdefault = $res['formdefault'];
                                        }
                    
                                        switch ($res['inputtype'])
                                        {
                                                case 'yesno':
                                                {
                                                        if ($answertoinput != '')
                                                        {
                                                                if ($answertoinput == 1)
                                                                {
                                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> ' . $phrase['_yes'] . '</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> ' . $phrase['_no'] . '</label>';
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
                                                        break;
                                                }                                            
                                                case 'int':
                                                {
                                                        $input = '<input class="input" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                        break;
                                                }                                            
                                                case 'textarea':
                                                {
                                                        //$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $answertoinput . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">'.$phrase['_increase_size'].'</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">'.$phrase['_decrease_size'].'</a></div></div>';
                                                        $input = '
                                                        <style id="wysiwyg_html" type="text/css">
                                                        <!--
                                                        ' . $ilance->styles->css_cache['csswysiwyg'] . '
                                                        //-->
                                                        </style>
                                                        <div class="ilance_wysiwyg">
                                                        <table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
                                                        <tr>
                                                        <td class="wysiwyg_wrapper" align="right" height="25">
                                        
                                                                <table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                                                                <tr>
                                                                        <td width="100%" align="left" class="smaller">' . $phrase['_plain_text_only_bbcode_is_currently_not_in_use_for_this_field'] . '</td>
                                                                        <td>
                                                                                        <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . $phrase['_decrease_size'] . '" border="0" /></a></div>
                                                                                        <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . $phrase['_increase_size'] . '" border="0" /></a></div>
                                                                        </td>
                                                                        <td style="padding-right:15px"></td>
                                                                </tr>
                                                                </table>
                                                        </td>
                                                        </tr>
                                                                <tr>
                                                                        <td><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $answertoinput . '</textarea></td>
                                                                </tr>
                                                        </table>
                                                        </div>';
                                                        
                                                        break;
                                                }                                            
                                                case 'text':
                                                {
                                                        $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                        break;
                                                }                                            
                                                case 'multiplechoice':
                                                {
                                                        if (!empty($res['multiplechoice']))
                                                        {
                                                                $choices = explode('|', $res['multiplechoice']);
                                                                $input = $phrase['_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices'] . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                
                                                                if (is_serialized($answertoinput))
                                                                {
                                                                        $answers = unserialize($answertoinput);
                                                                }
                                                                
                                                                if (empty($answers))
                                                                {
                                                                        $answers = array();
                                                                }
                                                                
                                                                $input .= '<optgroup label="' . $phrase['_select'] . '">';
                                                                foreach ($choices as $choice)
                                                                {
                                                                        if (in_array($choice, $answers))
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
                                                                        }
                                                                        else
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
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
                                                                $input = '<select name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '" style="font-family: verdana">';
                                                                
                                                                if (is_serialized($answertoinput))
                                                                {
                                                                        $answers = unserialize($answertoinput);
                                                                }
                                                                
                                                                if (empty($answers))
                                                                {
                                                                        $answers = array();
                                                                }
                                                                
                                                                foreach ($choices as $choice)
                                                                {
                                                                        if (in_array($choice, $answers))
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
                                                                        }
                                                                        else
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
                                                                        }
                                                                }
                                                                $input .= '</select>';
                                                        }
                                                        break;
                                                }
                                        }
                    
                                        if ($res['required'])
                                        {
                                                $isrequired = '<img name="' . $res['formname'] . 'error" src="'.$ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . $phrase['_this_form_field_is_required'] . '" />';
                                                $isrequiredjs .= "\n (fetch_js_object('" . $res['formname'] . "').value.length < 1) ? customImage(\"" . $res['formname'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . $res['formname'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
                                        }
                                        else
                                        {
                                                $isrequired = '';
                                        }
                    
                                        $html .= '
                                        <div style="padding-bottom:9px"><div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong></div>
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
                                        <tr>
                                                <td colspan="3"><div class="gray">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . ' ' . $isrequired . '</div></td>
                                        </tr>
                                        <tr>
                                                <td align="left" height="33">' . $input . '</td>
                                        </tr>
                                        </table></div>
                                        ';
                                        $c++;
                                }
                                
                                $headinclude .= $isrequiredjs;
                                $headinclude .= "\nreturn (!haveerrors);\n}\n//-->\n</script>\n";
                        }
                        else
                        {
                                $html = '';
                                $headinclude .= "<script type=\"text/javascript\">\n<!--\nfunction validatecustomform(f){return true;}\n//-->\n</script>\n";
                        }    
                }
                else if ($mode == 'updateprofileadmin')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "register_questions
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $c = 0;
                                $html = '';
                
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        // only selecting actual questions that have been answered by this user
                                        $answertoinput = '';
                                        
                                        $sql2 = $ilance->db->query("
                                                SELECT answerid, answer
                                                FROM " . DB_PREFIX . "register_answers
                                                WHERE questionid = '" . $res['questionid'] . "'
                                                    AND user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $res2 = $ilance->db->fetch_array($sql2);
                                                $answertoinput = stripslashes($res2['answer']);
                                                
                                                $formdefault = '';
                                                if (!empty($res['formdefault']))
                                                {
                                                        $formdefault = $res['formdefault'];
                                                }
                            
                                                switch ($res['inputtype'])
                                                {
                                                        case 'yesno':
                                                        {
                                                                if ($answertoinput != '')
                                                                {
                                                                        if ($answertoinput == 1)
                                                                        {
                                                                                $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> ' . $phrase['_yes'] . '</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> ' . $phrase['_no'] . '</label>';
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
                                                                break;
                                                        }                                                    
                                                        case 'int':
                                                        {
                                                                $input = '<input class="input" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                                break;
                                                        }                                                    
                                                        case 'textarea':
                                                        {
                                                                //$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $answertoinput . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">'.$phrase['_increase_size'].'</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">'.$phrase['_decrease_size'].'</a></div></div>';
                                                                $input = '
<style id="wysiwyg_html" type="text/css">
<!--
' . $ilance->styles->css_cache['csswysiwyg'] . '
//-->
</style>
<div class="ilance_wysiwyg">
<table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
<td class="wysiwyg_wrapper" align="right" height="25">

        <table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
        <tr>
                <td width="100%" align="left" class="smaller">' . $phrase['_plain_text_only_bbcode_is_currently_not_in_use_for_this_field'] . '</td>
                <td>
                                <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . $phrase['_decrease_size'] . '" border="0" /></a></div>
                                <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . $phrase['_increase_size'] . '" border="0" /></a></div>
                </td>
                <td style="padding-right:15px"></td>
        </tr>
        </table>
</td>
</tr>
        <tr>
                <td><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $answertoinput . '</textarea></td>
        </tr>
</table>
</div>';
                                                                break;
                                                        }                                                    
                                                        case 'text':
                                                        {
                                                                $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                                break;
                                                        }                                                    
                                                        case 'multiplechoice':
                                                        {
                                                                if (!empty($res['multiplechoice']))
                                                                {
                                                                        $choices = explode('|', $res['multiplechoice']);
                                                                        $input = $phrase['_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices'] . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                        
                                                                        $answers = array();
                                                                        if (is_serialized($answertoinput))
                                                                        {
                                                                                $answers = unserialize($answertoinput);
                                                                        }
                                                                        
                                                                        if (empty($answers))
                                                                        {
                                                                                $answers = array();
                                                                        }
                                                                        
                                                                        $input .= '<optgroup label="' . $phrase['_select'] . ':">';
                                                                        foreach ($choices as $choice)
                                                                        {
                                                                                if (in_array($choice, $answers))
                                                                                {
                                                                                        $input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
                                                                                }
                                                                                else
                                                                                {
                                                                                        $input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
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
                                                                        $input = '<select name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '" style="font-family: verdana">';
                                                                        
                                                                        if (is_serialized($answertoinput))
                                                                        {
                                                                                $answers = unserialize($answertoinput);
                                                                        }
                                                                        
                                                                        if (empty($answers))
                                                                        {
                                                                                $answers = array();
                                                                        }
                                                                        
                                                                        foreach ($choices as $choice)
                                                                        {
                                                                                if (in_array($choice, $answers))
                                                                                {
                                                                                        $input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
                                                                                }
                                                                                else
                                                                                {
                                                                                        $input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
                                                                                }
                                                                        }
                                                                        $input .= '</select>';
                                                                }
                                                                break;
                                                        }
                                                }
                            
                                                $action = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-answer&amp;id=' . $res2['answerid'] . '&amp;uid=' . intval($userid) . '" target="_self" title="Remove this answer" onclick="return confirm_js(\''.$phrase['_please_take_a_moment_to_confirm_your_action'].'\')"><img src="'.$ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="Remove this answer" /></a>';
                                                $html .= '
                                                <div><strong>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</strong></div>
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
                                                <tr>
                                                        <td colspan="3"><span class="gray">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . '</span></td>
                                                </tr>
                                                <tr>
                                                        <td align="left" height="33">' . $input . ' &nbsp;&nbsp;' . $action . '</td>
                                                </tr>
                                                </table>
                                                <hr size="1" width="100%" style="color:#cccccc" />
                                                ';
                                                $c++;
                                        }
                                }
                        }
                        else
                        {
                            $html = '';
                        }    
                }
                else if ($mode == 'update')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "register_questions
                                WHERE pageid = '" . intval($pageid) . "'
                                    AND visible = '1'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $c = 0;
                                $html = $isrequiredjs = '';
                
                                // enable custom header javascript
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
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        // do we have an answer?
                                        $answertoinput = '';
                                        
                                        $sql2 = $ilance->db->query("
                                                SELECT answer
                                                FROM " . DB_PREFIX . "register_answers
                                                WHERE questionid = '" . $res['questionid'] . "'
                                                    AND user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $res2 = $ilance->db->fetch_array($sql2);
                                                $answertoinput = stripslashes($res2['answer']);
                                        }
                    
                                        $formdefault = '';
                                        if (isset($res['formdefault']) AND $res['formdefault'] != '')
                                        {
                                                $formdefault = $res['formdefault'];
                                        }
                                        
                                        switch ($res['inputtype'])
                                        {
                                                case 'yesno':
                                                {
                                                        if ($answertoinput != '')
                                                        {
                                                                if ($answertoinput == 1)
                                                                {
                                                                        $input = '<label for="' . $res['formname'] . '1"><input type="radio" id="' . $res['formname'] . '1" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="1" checked="checked" /> ' . $phrase['_yes'] . '</label><label for="' . $res['formname'] . '2"><input type="radio" id="' . $res['formname'] . '2" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="0" /> ' . $phrase['_no'] . '</label>';
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
                                                        break;
                                                }                        
                                                case 'int':
                                                {
                                                        $input = '<input class="input" size="3" type="text" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                        break;
                                                }                        
                                                case 'textarea':
                                                {
                                                        //$input = '<div class="ilance_wysiwyg"><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px;" wrap="physical">' . $answertoinput . '</textarea><br /><div style="width:300px;"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)">'.$phrase['_increase_size'].'</a>&nbsp;<a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)">'.$phrase['_decrease_size'].'</a></div></div>';
                                                        $input = '
                                                        <style id="wysiwyg_html" type="text/css">
                                                        <!--
                                                        ' . $ilance->styles->css_cache['csswysiwyg'] . '
                                                        //-->
                                                        </style>
                                                        <div class="ilance_wysiwyg">
                                                        <table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
                                                        <tr>
                                                        <td class="wysiwyg_wrapper" align="right" height="25">
                                        
                                                                <table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
                                                                <tr>
                                                                        <td width="100%" align="left" class="smaller">' . $phrase['_plain_text_only_bbcode_is_currently_not_in_use_for_this_field'] . '</td>
                                                                        <td>
                                                                                        <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="' . $phrase['_decrease_size'] . '" border="0" /></a></div>
                                                                                        <div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $res['formname'] . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="' . $phrase['_increase_size'] . '" border="0" /></a></div>
                                                                        </td>
                                                                        <td style="padding-right:15px"></td>
                                                                </tr>
                                                                </table>
                                                        </td>
                                                        </tr>
                                                                <tr>
                                                                        <td><textarea id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $answertoinput . '</textarea></td>
                                                                </tr>
                                                        </table>
                                                        </div>';
                                                        
                                                        break;
                                                }                        
                                                case 'text':
                                                {
                                                        $input = '<input class="input" type="text" id="' . $res['formname'] . '" name="custom[' . $res['questionid'] . '][' . $res['formname'] . ']" value="' . $answertoinput . '" style="font-family: verdana" />';
                                                        break;
                                                }                                            
                                                case 'multiplechoice':
                                                {
                                                        if (!empty($res['multiplechoice']))
                                                        {
                                                                $choices = explode('|', $res['multiplechoice']);
                                                                $input = $phrase['_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices'] . '<br /><select style="width:250px; height:70px; font-family: verdana" multiple name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '">';
                                                                
                                                                if (is_serialized($answertoinput))
                                                                {
                                                                        $answers = unserialize($answertoinput);
                                                                }
                                                                
                                                                if (empty($answers))
                                                                {
                                                                        $answers = array();
                                                                }
                                                                
                                                                $input .= '<optgroup label="' . $phrase['_select'] . '">';
                                                                foreach ($choices as $choice)
                                                                {
                                                                        if (in_array($choice, $answers))
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
                                                                        }
                                                                        else
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
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
                                                                $input = '<select name="custom[' . $res['questionid'] . '][' . $res['formname'] . '][]" id="' . $res['formname'] . '" style="font-family: verdana">';
                                                                
                                                                if (is_serialized($answertoinput))
                                                                {
                                                                        $answers = unserialize($answertoinput);
                                                                }
                                                                
                                                                if (empty($answers))
                                                                {
                                                                        $answers = array();
                                                                }
                                                                
                                                                foreach ($choices as $choice)
                                                                {
                                                                        if (in_array($choice, $answers))
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>';
                                                                        }
                                                                        else
                                                                        {
                                                                                $input .= '<option value="' . trim($choice) . '">' . $choice . '</option>';
                                                                        }
                                                                }
                                                                $input .= '</select>';
                                                        }
                                                        break;
                                                }
                                        }
                    
                                        if ($res['required'])
                                        {
                                                $isrequired = '<img name="' . $res['formname'] . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="' . $phrase['_this_form_field_is_required'] . '" />';
                                                $isrequiredjs .= "\n(fetch_js_object('" . $res['formname'] . "').value.length < 1) ? customImage(\"" . $res['formname'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"" . $res['formname'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false);";
                                        }
                                        else
                                        {
                                                $isrequired = '';
                                        }
                    
                                        $html .= '
                                        <tr>
                                            <td colspan="5">
                                                    <table width="100%" border="0" cellspacing="3" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
                                                    <tr>
                                                            <td width="50%" valign="top">
                    
                                                                    <fieldset class="fieldset" style="margin:0px">
                                                                    <legend>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</legend>
                                                                    <table width="100%" border="0" cellspacing="3" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
                                                                    <tr>
                                                                            <td colspan="3">' . stripslashes($res['description_' . $_SESSION['ilancedata']['user']['slng']]) . ' ' . $isrequired . '</td>
                                                                    </tr>
                                                                    <tr>
                                                                            <td align="left" height="33">' . $input . '</td>
                                                                    </tr>
                                                                    </table>
                                                                    </fieldset>
                                                            </td>
                                                    </tr>
                                                    </table>
                                            </td>
                                        </tr>';
                                        $c++;
                                }
                                
                                $headinclude .= $isrequiredjs;
                                $headinclude .= "\nreturn (!haveerrors);\n}\n</script>\n";
                        }
                        else
                        {
                                $html = '';
                                $headinclude .= "<script type=\"text/javascript\">function validatecustomform(f){return true;}</script>\n";
                        }
                }
                else if ($mode == 'output1')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "register_questions
                                WHERE visible = '1'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $c = 0;
                                $html = '';
                                while ($res = $ilance->db->fetch_array($sql))
                                {
                                        $sql2 = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "register_answers
                                                WHERE questionid = '" . $res['questionid'] . "'
                                                    AND user_id = '" . intval($userid) . "'
                                                    AND visible = '1'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                $res2 = $ilance->db->fetch_array($sql2);
                                                $html .= '<tr><td colspan="4" align="left"><fieldset class="fieldset" style="margin:0px"><legend>' . stripslashes($res['question_' . $_SESSION['ilancedata']['user']['slng']]) . '</legend>';
                        
                                                // input type switch display
                                                switch ($res['inputtype'])
                                                {
                                                        case 'yesno':
                                                        {
                                                                if ($res2['answer'] == 1)
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
                                                                $html .= intval($res2['answer']) . '&nbsp;';
                                                                break;
                                                        }                            
                                                        case 'textarea':
                                                        {
                                                                $html .= stripslashes($res2['answer']) . '&nbsp;';
                                                                break;
                                                        }                            
                                                        case 'text':
                                                        {
                                                                $html .= stripslashes($res2['answer']) . '&nbsp;';
                                                                break;
                                                        }                                                    
                                                        case 'multiplechoice':
                                                        {
                                                                if (!empty($res2['answer']))
                                                                {
                                                                        if (is_serialized($res2['answer']))
                                                                        {
                                                                                $answers = unserialize($res2['answer']);
                                                                        }
                                                                        
                                                                        if (empty($answers))
                                                                        {
                                                                                $answers = array();
                                                                        }
                                                                        
                                                                        $fix = '';
                                                                        foreach ($answers as $answered)
                                                                        {
                                                                                $fix .= stripslashes($answered) . ', ';
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
                                                                if (!empty($res2['answer']))
                                                                {
                                                                        if (is_serialized($res2['answer']))
                                                                        {
                                                                                $answers = unserialize($res2['answer']);
                                                                        }
                                                                        
                                                                        if (empty($answers))
                                                                        {
                                                                                $answers = array();
                                                                        }
                                                                        
                                                                        $fix = '';
                                                                        foreach ($answers as $answered)
                                                                        {
                                                                                $fix .= stripslashes($answered) . ', ';
                                                                        }
                                                                        
                                                                        $html .= mb_substr($fix, 0, -2);
                                                                }
                                                                else
                                                                {
                                                                        $html .= '&nbsp;';
                                                                }
                                                                
                                                                break;
                                                        }
                                                }
                                                
                                                $html .= '</fieldset></td></tr>';
                                        }
                                        else
                                        {
                                                $html = '';
                                        }
                                }
                        }
                        else
                        {
                                $html = '';
                        }
                }
                else
                {
                        $html = '';
                }
                
                ($apihook = $ilance->api('construct_register_questions_end')) ? eval($apihook) : false;
                
                return $html;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>