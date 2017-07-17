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

define('OIBASE', DIR_FUNCTIONS . 'custom/OpenInviter/');
define('OIBASE_URL', HTTP_SERVER . 'functions/custom/OpenInviter/');
include_once OIBASE.'openinviter.php';
/**
 * Description of class.mmi_invitation.inc.php
 *
 * @author magneticmg
 */
class mmi_invitation {

    var $config = array();


    function __construct() {

       // $this->loadConfig();

        //self::parray($this->config);
    }

    function loadConfig() {

        global $ilance;


        if(!$ilance->db->table_exists(DB_PREFIX.'mmi_invitation_configuration')) {
            return;
        }
        $query = $ilance->db->query(self::_buildConfig());

        if($ilance->db->num_rows($query) > 0) {

            while($res = $ilance->db->fetch_array($query)) {

                $this->config[$res['inputname']] = $res['value'];
            }
        }

    }
    function _buildConfig() {

        $query = 'SELECT * FROM ' . DB_PREFIX.'mmi_invitation_configuration';
        return $query;
    }

    public function printConfigForm() {

        global $ilance;
        $table = NULL;
        /**
         * Cannot access this from the front end...
         */
        /*  if (empty($_SESSION['ilancedata']['admin']['userid']))
         {
            echo 'This script cannot be parsed indirectly.';
            exit();
         }
        */



        $query = self::_buildConfig();
        $configsql = $ilance->db->query($query);
        $typeattributes = $this->getConfigInputAttributes();
        if ($ilance->db->num_rows($configsql) > 0) {
            while ($res = $ilance->db->fetch_array($configsql)) {
                $input = '';
                $config[$res['name']] = $res['value'];
                if($res['inputtype'] == 'yesno') {

                    $yeschecked = ($res['value'] == 1) ? 'checked="checked"' : NULL;
                    $nochecked = ($res['value'] == 0) ? 'checked="checked"' : NULL;
                    $input = '<label>Yes</label><input name="config['.$res['inputname'].']" type="radio" value="1" '.$yeschecked.'/>
                            <label>No</label><input name="config['.$res['inputname'].']" type="radio" value="0" '.$nochecked.'/>';
                } else  if($res['inputtype'] == 'textarea') {
                    $input ='<textarea '.$typeattributes[$res['inputtype']].' name="config['.$res['inputname'].']">'.$res['value'].'</textarea>';
                } else if($res['inputtype'] == 'pulldown') {
                    $input = $res['inputcode'];
                } else {
                    $input ='<input '.$typeattributes[$res['inputtype']].' name="config['.$res['inputname'].']" type="'.$res['inputtype'].'" value="'.$res['value']. '"/>';
                }

                $table .= ' <tr><td>'.ucfirst($res['name']).'</td><td>'.$input.'</td><td>  </td></tr>';
            }
        }

        $html = '
         <form id="mmi_invitation_config" action="components.php" name="config[]" method="post" >
            <input type="hidden" name="module" value="mmi_invitation"/>
            <input type="hidden" name="subcmd" value="_update_config"/>
            <input type="hidden" name="cmd" value="components"/>
            <table width="100%">
                <tr><td class="tablehead_alt" colspan="3">MMI Invitation Configuration Table</td></tr>
                <tr><td class="tableheadcat">Name</td><td class="tableheadcat">Value</td><td class="tableheadcat"> holder </td></tr>
                '. $table .'

            </table>


            <input class="button" type="submit" value="submit"/>
         </form>';
        $html .= $this->renderOIPlugins();

        return $html;

    }
    function &getOIPlugins() {

        static $oi_services;

        if(!$oi_services) {

            $inviter = new openinviter();
            $oi_services = $inviter->getPlugins();
        }

        return $oi_services;

    }

    function renderOIPlugins() {

        $oi_services = &$this->getOIPlugins();

        $html = '<table width="500px">
<tr><td class="tablehead_alt" colspan="2">Available Services</td></tr>
<tr><th class="tableheadcat">Service</th><th class="tableheadcat">Abbreviation</th></tr>';
        foreach($oi_services as $type => $services) {
            $html .= '<tr ><td colspan="2" style="font-size: 16px"><b>'. $type . '</b></tr>';
            foreach($services as $abr => $service) {
                $html .= '<tr><td style="padding-left: 10px;">'. $service['name'] . '</td><td>' . $abr .'</td></tr>';
            }
        }
        $html .= '</table>';
        return $html;
    }
    /**
     *
     */
    function getConfigInputAttributes() {

        $att = array(
                'textarea' => ' rows="5" cols="40"',
                'text' =>  'length="40"'

        );

        return $att;

    }
    /**
     * Filter out the services based on what we have selected in Admincp
     * @param <type> $oi_services
     */
    function filterServices(& $oi_services) {

        foreach($oi_services as $type => $services) {
            $allowed = $this->getServiceArray($type);

            foreach ($services as $abr => $service) {

                if(!in_array($abr, $allowed)) {
                    unset($oi_services[$type][$abr]);
                }

            }
            if(count($oi_services[$type]) < 1)
                unset($oi_services[$type]);

        }

    }
    function &getFilteredOIServices() {

        static $foi_services;

        if(!$foi_services) {
            $oi_services =  &self::getOIPlugins();
            $foi_services = $oi_services;
            self::filterServices($foi_services);
        }

        return $foi_services;

    }

    function getServiceArray($type = 'email') {

        $string = $this->config[$type];
        $array = explode(',', $string);
        return $array;

    }

    function &getOInviter() {
        static $instance;

        if(!$instance) {

            $instance = new openinviter();

        }
        return $instance;
    }

    /**
     * Method to reture a reference to the built email object. This version leaves the
     * {{receivername}} empty. The idea is to clone the returned object in the recipient.
     */
    function &getEmailMessage($forshow = false){
        global $ilance;
        static $email;

        if(!$email){
            $rid = $_SESSION['ilancedata']['user']['ridcode'];
            $user_id = $_SESSION['ilancedata']['user']['userid'];
            $firstname = $_SESSION['ilancedata']['user']['firstname'];
            $lastname = $_SESSION['ilancedata']['user']['lastname'];
            $fromname = $firstname . ' '.$lastname;
            if(empty ($fromname)) {
                        $fromname = $prase['_a_contact_of_ yours'];
            }
            $email = construct_dm_object('email', $ilance);

            $settings = array(
                            '{{from_name}}' => $fromname,
                            '{{rid}}' => $rid,
                                                       
                    );
            if(isset($ilance->GPC['message_box'])){
            
                $settings['{{usermessage}}'] =  $ilance->GPC['message_box'];
            }
            if($forshow){

                $settings['{{usermessage}}'] = '<div id="usermessage"></div>';

            }



                    $email->slng = $_SESSION['ilancedata']['user']['slng'];//fetch_site_slng();
                    $email->get('invitation');
                     
                    $email->from = $ilance->GPC['email_box'];
                    $email->fromname = $fromname;
                    $email->set($settings);
                    
            }

            return $email;

    }

    /**
     * This method is meant for future release, will help personalize the message the members.
     * Held up by the way open invider processes sendMessage() for social networks. 
     *
     * @param array $contacts
     * @param <type> $usermessage
     */
    function processEmail(array $contacts, $usermessage){

        $baseMessage = &$this->getEmailMessage();
        $settings = array('{{usermessage}}'=> $usermessage);
        foreach($contacts as $name=>$email){

                $recMessageObj = clone ($baseMessage);
                if($name != $email){

                    $settings = array('{{receivername}}' => $name);
                } else {
                    $settings = array('{{receivername}}' => null);
                }

        }

    }

}
?>
