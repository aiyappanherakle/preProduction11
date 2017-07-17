<?php

/*==========================================================================*\
 || ######################################################################## ||
 || # MMInc PHP                                          				  #	||
 || # -------------------------------------------------------------------- # ||
 || # Copyright ©2000–2007 Magnetic Merchandising Inc. All Rights Reserved.# ||
 || # This file may not be redistributed in whole or significant part. 	  # ||
 || # -------------------------------------------------------------------- # ||
 || # http://www.magneticmerchandising.com  info@magneticmerchandising.com # ||
 || # -------------------------------------------------------------------- # ||
 || ######################################################################## ||
 \*==========================================================================*/


/**
 * Description of class.mmi_invitation_post.inc.php
 *
 * @author magneticmg
 */
class mmi_invitation_post {

    function post_service_selection_html($stype = 'pulldown'){
        global $ilance, $phrase;
        $oi_services = & $ilance->invitation->getFilteredOIServices();
        $inviter = &$ilance->invitation->getOInviter();

         switch ($stype) {
             case 'pulldown':
               $html = "<select class='thSelect' name='provider_box'><option value=''>{$phrase['_select_one']}</option>";
                 foreach ($oi_services as $type=>$providers) {
                $html.="<optgroup label='{$inviter->pluginTypes[$type]}'>";
                foreach ($providers as $provider=>$details)
                    $html.="<option value='{$provider}'".($ilance->GPC['provider_box'] == $provider?' selected':'').">{$details['name']}</option>";
                $html.="</optgroup>";
            }
              $html.="</select>";

                 break;

             case 'radio':
                 $html = '<br/><div id="oi_selectionlist">';
                 foreach ($oi_services as $type=>$providers) {
                 $html .= "<div class='oi_provider_label'><label><b>{$inviter->pluginTypes[$type]}</b></label></div>";
                    foreach ($providers as $provider=>$details){
                        $html .= "<div class='oi_provider'><label>{$details['name']}</label><input type='radio' name='provider_box' value='{$provider}'/></div>";
                    }
                }
                   $html .= '</div>';


             default:
                 break;
         }

         return $html;
    }
    //put your code here
}
?>
