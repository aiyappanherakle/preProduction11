<?php 
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
$query="SELECT project_id,count(id),count(distinct user_id) FROM `ilance_proxybid` group by project_id having count(id)!=count(distinct user_id)";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result)>0)
{
	while($line=$ilance->db->fetch_array($result))
	{
	$line['project_id'];
	$query1="select * from ilance_proxybid where project_id ='".$line['project_id']."'";
	$result1=$ilance->db->query($query1);
	if($ilance->db->num_rows($result1)>0)
	{
		while($line1=$ilance->db->fetch_array($result1))
		{
		echo $line1['project_id'].','.$line1['user_id'].','.$line1['id'].','.$line1['maxamount'].','.$line1['date_added'];
		echo '<br>';
		}
	}
	}
}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

?>