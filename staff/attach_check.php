<?php 
require_once('./../functions/config.php');

$query="select * from ".DB_PREFIX."attachment ORDER BY project_id DESC";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result)>0)
{
	while($line=$ilance->db->fetch_array($result))
	{
	$coin_id=$line['project_id'];
	$hash=$line['filehash'];
	$file_pathed_name=DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id.'/'.$hash.'.attach';
		if(!is_file($file_pathed_name))
		{
		echo $coin_id."|".$line['filehash'].'<br>';
		
		}
		
		
	}
}

?>