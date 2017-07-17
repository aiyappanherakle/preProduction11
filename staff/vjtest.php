<?php
require_once('../functions/config.php');

error_reporting(E_ALL);
define("DIR_MOVE",'/home/gc/public_html/uploads/attachments/vjmove/');
echo $sql="SELECT p.project_id,a.attachid,a.filehash,p.date_end,p.bids FROM ilance_attachment a 
left join ilance_projects p on p.project_id=a.project_id 
WHERE p.`date_end` < '2012-06-01 00:00:00' AND p.status IN ('closed', 'expired') and a.filehash is not null 
AND ((p.filtered_auctiontype = 'regular' AND p.haswinner  = '1') 
OR (p.buynow = '0' AND p.filtered_auctiontype = 'fixed'))
ORDER BY `p`.`date_end`  ASC";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
    while($line= $ilance->db->fetch_array($result))
    {
        $hash=$line['filehash'];
        $coin_id=$line['project_id'];
                $m=DIR_MOVE.floor($coin_id/100).'00/'.$coin_id;
                $source=DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id.'/'.$hash.'.attach';
                if(!is_dir($m))
                mkdir($m,0777,true);
                if(is_writable($m))
                {
                    if(is_file($source))
                    {
                        rename($source,$m.'/'.$hash.'.attach');    
                    }else
                    {
                        echo "source note redable";
                    }
                }else
                {
                    echo 'desctination not writable';
                }
                
    }
}

/*
$move="/home/gc/public_html/uploads/attachments/move/";
$sql="SELECT * FROM attachment_files where status=1 ";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
    while($line= $ilance->db->fetch_array($result))
    {
        $ter=explode("/",$line['filename']);
        //print_r($ter);
        $src=$line['filename'];
        $dest=$move.$ter['7'].'/'.$ter['8'].'/'.$ter['9'];
        $m=$move.$ter['7'].'/'.$ter['8'].'/';
                if(!is_dir($m))
                mkdir($m,0777,true);
                if(is_writable($m))
                {
                    if(is_file($src))
                    {
                        
                       rename($src,$dest);    
                       $ilance->db->query("update attachment_files set status=2 where id='".$line['id']."'");
                    }else
                    {
                        echo $line['filename']."source note redable<br>";
                    }
                }else
                {
                    echo $line['filename']."destination note redable<br>";
                }

    }
}


///public_html/uploads/attachments/auctions/100/
$dir="/home/gc/public_html/uploads/attachments/auctions/";
$move="/home/gc/public_html/uploads/attachments/move/";
error_reporting(E_ALL);
echo '<pre>';
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
foreach($objects as $name => $object){
    $filehash= basename($name);
    $sql="INSERT INTO `attachment_files` ( `filename`, `path`, `size`) VALUES ( '".$name."', '".$filehash."', '".filesize($name)."');";
    $result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
}


$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
foreach($objects as $name => $object){
    $filehash= basename($name);
    $sql="INSERT INTO `gc_ilance`.`attachment_files` ( `filename`, `path`, `size`) VALUES ( '".$name."', '".$filehash."', '".filesize($name)."');";
    $result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
       
}


error_reporting(E_ALL);
define("DIR_MOVE",'/home/gc/public_html/uploads/attachments/move/');
$sql="SELECT p.project_id,a.attachid,a.filehash,p.date_end FROM ilance_attachment a left join ilance_projects p on p.project_id=a.project_id WHERE p.date_end <= '2012-01-01' and p.bids>1 
ORDER BY `p`.`date_end`  ASC";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
    while($line= $ilance->db->fetch_array($result))
    {
        $hash=$line['filehash'];
        $coin_id=$line['project_id'];
                $m=DIR_MOVE.floor($coin_id/100).'00/'.$coin_id;
                $source=DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id.'/'.$hash.'.attach';
                if(!is_dir($m))
                mkdir($m,0777,true);
                if(is_writable($m))
                {
                    if(is_file($source))
                    {
                        rename($source,$m.'/'.$hash.'.attach');    
                    }else
                    {
                        echo "source note redable";
                    }
                }else
                {
                    echo 'desctination not writable';
                }
                
    }
}


$sql="SELECT *  FROM `ilance_projects` WHERE `date_end` <= '2012-01-01 00:00:00' and bids>1 limit 10";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
    while($line= $ilance->db->fetch_array($result))
    {
        $coin_id=$line['project_id'];
        $sql1="SELECT * FROM " . DB_PREFIX . "attachment WHERE project_id = '" . $line['project_id'] . "'";
        echo $sql1.'<br>';
        $result1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
        if($ilance->db->num_rows($result1)>0)
        {
            while($line1= $ilance->db->fetch_array($result1))
            {
                $hash=$line1['filehash'];
                echo $m=DIR_MOVE.floor($coin_id/100).'00/'.$coin_id;
                if(!is_dir($m))
                mkdir($m,0777,true);
                rename($m.'/'.$hash.'.attach',DIR_AUCTION_ATTACHMENTS.floor($coin_id/100).'00/'.$coin_id.'/'.$hash.'.attach');
            }
        }else
        {

        }
    }
}
*/
/*$dir="/home/gc/public_html/uploads/attachments/auctions/";
error_reporting(E_ALL);
$fp=fopen("file_log.txt","a");
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
foreach($objects as $name => $object){
    if(strpos($name,'.attach')>0)
    {
    	$filehash= basename($name,'.attach');
    	$sql="SELECT *  FROM `ilance_attachment` WHERE `filehash` LIKE '".$filehash."'";
    	$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
    	if($ilance->db->num_rows($result)==0)
    	{
    		//echo 'move this file '.$name.' <br>';
    		$move='/home/gc/public_html/uploads/attachments/move/'.substr($name,strpos($name,'auctions/')+strlen('auctions/'));
			$move_dir=substr($move,0,strrpos($move,"/")+1);
			
			if(!is_writable($move_dir))
				mkdir($move_dir,0777, true);
                rename($name,$move);
			
    	}
    }else
    {
        fwrite($fp,$name."\n");
    }
    fclose($fp);
}

$sql="SELECT * FROM attachment_files where moved=0";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
    while($line= $ilance->db->fetch_array($result))
    {
        $ter=explode("/",$line['filename']);
        //print_r($ter);
        $src=$line['filename'];
        $dest=$move.$ter['7'].'/'.$ter['8'].'/'.$ter['9'];
        $m=$move.$ter['7'].'/'.$ter['8'].'/';
                if(!is_dir($m))
                mkdir($m,0777,true);
                if(is_writable($m))
                {
                    if(is_file($src))
                    {
                        rename($src,$dest);    
                        $ilance->db->query("update attachment_files set moved=1 where id='".$line['id']."'");
                    }else
                    {
                        echo $line['filename']."source note redable<br>";
                    }
                }else
                {
                    echo $line['filename']."destination note redable<br>";
                }

    }
}


$sql="SELECT * FROM " . DB_PREFIX . "attachments WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'";
$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($result)>0)
{
    while($line= $ilance->db->fetch_array($result))
    {
        $ter=$line['project_id'];
    }
}

*/

?>
