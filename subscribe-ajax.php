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

// #### load required phrase groups ############################################
$phrase['groups'] = array(
        'watchlist',
        'registration',
        'search',
        'stores',
        'wantads',
        'subscription',
        'preferences',
        'buying',
        'selling',
        'rfp',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
        'tabfx',
        'jquery',
	'flashfix'
);

// #### setup script location ##################################################
define('LOCATION','ajax');
define('SKIP_SESSION', true);

// #### require backend ########################################################
require_once('./functions/config.php');




    if(isset($_GET['ufehx']) && $_GET['ufehx'] != '')
        
       { 
          $email = $_GET['ufehx'] ;

          $pattern = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$"; 

	      if (eregi($pattern, $email)){ 

	        

	        $sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "subscriber
                                        WHERE email = '".$_GET['ufehx']."'");
                                if ($ilance->db->num_rows($sql) > 0)
                                {

                                	echo 'Already Existing Email Address';

                                }
                                else
                                {
                                $ipaddress      = IPADDRESS;
                                $subscriber_insert = $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "subscriber
                                (subscriber_id, email,subscribe_date, status, ipaddress) VALUES ('', '".$email."',  now(), '1','" . $ilance->db->escape_string($ipaddress) . "')");
                                echo 'Email address subscribed successfully';


                                }      





	      } 
	      else 
	      { 
	        echo 'Enter Valid Email Address';
	      } 
                                           
      } 
       else
	    {
    
           echo  'Enter the email Address';

	    }


?>