<?php 
/*  bug 2542 */
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	
// sample array 
$p['137136']='Only 5 graded by PCGS as MS-70.';
$p['137131']='Only 5 graded by PCGS as MS-70. POP 1';
$p['137140']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137195']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137194']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137160']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137196']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137193']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137161']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137128']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137138']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137137']='Pop 1. The lone example graded by PCGS at this grade.';
$p['137166']='Only 3 coins graded by PCGS as MS-70.';
$p['137141']='Only 2 coins graded by PCGS as MS-70.';
$p['137143']='Only 3 coins graded by PCGS as MS-70.';
$p['137129']='Only 7 coins gr   aded by PCGS as MS-70.';
$p['137200']='Only 2 coins graded by PCGS as MS-70.'; 

$count=0;
$changed='';
$changed_only_coin='';
$not_changed='';
$donot_exist='';
	foreach($p as $id=>$bid)
	{
         $ilance->db->query("UPDATE  " . DB_PREFIX . "coins SET  description = '".$bid."' WHERE  coin_id = '".$id."'");
         $ilance->db->query("UPDATE  " . DB_PREFIX . "projects SET  description = '".$bid."' WHERE  project_id = '".$id."'");     
        }
	
	
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*  bug 2542 */

?>