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



$sqlfolder = $ilance->db->query("SELECT folder FROM ".DB_PREFIX."modules_group
WHERE modulegroup = 'mmi_invitation'");
if ($ilance->db->num_rows($sqlfolder) > 0)
{
	$resfolder = $ilance->db->fetch_array($sqlfolder);
}
if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update_config'){

    // update the database configuration based on the form inputs....
    $config = $ilance->GPC;



    foreach($ilance->GPC['config'] as $k => $v){
        $query = 'UPDATE '.DB_PREFIX.'mmi_invitation_configuration ';
        $query .= ' SET `value` = "' . $v .'" WHERE `inputname` = "'. $k.'"';
        if(!$ilance->db->query($query)){
            $error[] = $k . ' was not set in the db <br/>';
        }

    }


    if(!isset($error)){

        print_action_success("You have successfully updated your MMI Invitation Settings", $_SERVER['PHP_SELF'].'?module=mmi_invitation');
						exit();
    } else {
        foreach($error as $p => $t){
            echo $t;
        }
        exit();
    }
}

?>
