<?php
 
class not_list_hotlist
{
	function not_listed_hotlistcoins()
	{
	    global $ilance,$ilconfig;
		$sql="update " . DB_PREFIX . "projects p 
		left join " . DB_PREFIX . "cron_not_placebid c on c.project_id=p.project_id 
		left join " . DB_PREFIX . "users u  on u.user_id=p.user_id
		set p.hotlists=1,c.Bid_listed='Yes' 
		where p.status='open' and c.status='hot' and c.Bid_listed='No' and u.status='active'";
		$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
    }
}

?>
