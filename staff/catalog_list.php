<?php
require_once('./../functions/config.php');
      $coin_list_level = $ilance->db->query("
			SELECT cid FROM " . DB_PREFIX . "projects where status='open'");
			if($ilance->db->num_rows($coin_list_level) > 0)
			{
			$row_count_list = 0;
			while($row_coin_list = $ilance->db->fetch_array($coin_list_level))
			{
			$query=$ilance->db->query("select cid from ilance_categories where cid='".$row_coin_list['cid']."'");
			if($ilance->db->num_rows($query)==0)
			{
			echo $row_coin_list['cid'];
			echo '<br>';
			}
			}
			}

?>