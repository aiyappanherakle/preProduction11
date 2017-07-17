<?php
require_once('./functions/config.php');
error_reporting(E_ALL);
//define("DIR_MOVE",'/home/gc/public_html/uploads/attachments/auctions/');
$sql="SELECT a.project_id, a.attachid, a.filehash
FROM ilance_attachment_returned a
WHERE a.attachid IS NOT NULL
ORDER BY `a`.`project_id` ASC  ";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
    while($line= $ilance->db->fetch_array($result))
    {
		
        $hash=$line['filehash'];
        $coin_id=$line['project_id'];
        $source=DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id.'/'.$hash.'.attach';
        if(!is_file($source))
        {
            echo "<br>source note redable, ".$coin_id.",".$hash;
        }
    }
}
