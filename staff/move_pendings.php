<?php 


// #### require backend ########################################################
require_once('./../functions/config.php');

$sql_ship = $ilance->db->query("
                               SELECT coin_id
                        	   FROM " . DB_PREFIX . "coins where 
                             End_Date = '2012-07-23' and project_id='0'
							   
                        ");
						
 while ($result = $ilance->db->fetch_array($sql_ship))
{
//echo "update  " . DB_PREFIX . "coins  set pending='1' where coin_id='".$result['coin_id']."'";
//echo '<br/>';
// $sel= $ilance->db->query("update  " . DB_PREFIX . "coins  set pending='1' where coin_id='".$result['coin_id']."'");

}

?>