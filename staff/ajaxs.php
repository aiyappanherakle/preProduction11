 
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
	'jquery_custom_ui',
	'flashfix'
);

// #### require backend ########################################################
require_once('./../functions/config.php');

 if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'change')
{
        if(isset($ilance->GPC['change']) AND $ilance->GPC['change'] == 'cronchange' AND isset($ilance->GPC['checkvalue']))
    {
 
        if ($ilance->GPC['checkvalue'] == 1)
        {
            $sql = $ilance->db->query("UPDATE  " . DB_PREFIX . "cron
                        SET  active = 1   
                        WHERE  cronid = '32'");
                        echo '1';  
        }
        else
        {   
            $sql = $ilance->db->query("UPDATE  " . DB_PREFIX . "cron
                        SET  active = 0   
                        WHERE  cronid = '32'"); 
                        echo '0'; 

        }
 
    }

}else
{


 
$sql = $ilance->db->query(" UPDATE " . DB_PREFIX . "subscriber SET status='".$_GET['status']."' WHERE subscriber_id='".$_GET['id']."' ");
 
echo $sql;
 exit;

 }

    


?>
