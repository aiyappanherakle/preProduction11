<?php
require_once('./../functions/config.php');
error_reporting(E_ALL);

$selcoin = $ilance->db->query("SELECT * FROM ".DB_PREFIX."coins WHERE relist_count > 0 AND coin_id >= 6000");
while($row_value = $ilance->db->fetch_array($selcoin))
{
	for($i=0;$i < $row_value['relist_count'];$i++)
	{
		echo '<br>'.$row_value['coin_id'];
	}
}