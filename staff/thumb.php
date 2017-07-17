<?php 
require_once('/home/gc/public_html/functions/config.php');
define('DIR_AUCTION_ATTACHMENTS', '/home/gc/public_html/uploads/attachments/auctions/');

$sql="SELECT * FROM ilance_attachment where thumb_status=0 ORDER BY attachid  DESC limit 5";
$result=$ilance->db->query($sql);
if($ilance->db->num_rows($result)>0)
{
	while($line=$ilance->db->fetch_array($result))
	{
		
	$sql1="SELECT * FROM ilance_img_sizes where w>0 or h>0";
	$result1=$ilance->db->query($sql1);
	if($ilance->db->num_rows($result1)>0)
	{
		while($line1=$ilance->db->fetch_array($result1))
		{
			$required_width=$line1['w'] ;
			$required_height=$line1['h'];
			$id=$line1['id'];
			$coin_id=$line['project_id'];
			list($f,$e)=explode('.',$line['filename']);
			$target_folder=DIR_AUCTION_ATTACHMENTS .floor($coin_id/100).'00/'.  $coin_id . '/';
			$generated_file_name=$f.'-'.$id.'.'.$e;
			$generated_thumb=$target_folder.$generated_file_name;
			$row['filedata'] = file_get_contents($target_folder . $line['filehash'] . '.attach');
			$src = imagecreatefromstring($row['filedata']);
			$width = imagesx($src);
			$height = imagesy($src);
			$xscale = $width / $required_width;
			$yscale = $height / $required_height;
			if ($xscale > $yscale)
			{
				$_REQUEST['w'] = round($width / $xscale);
				$_REQUEST['h'] = round($height / $xscale);
			}
			else
			{
				$_REQUEST['w'] = round($width / $yscale);
				$_REQUEST['h'] = round($height / $yscale);
			}
			
			// #### ensure we don't stretch the image's width ##############
			if ($width < $_REQUEST['w'])
			{
				$_REQUEST['w'] = $width;            
			}
			
			// #### ensure we don't stretch the image's height #############
			if ($height < $_REQUEST['h'])
			{
				$_REQUEST['h'] = $height;
			}
			//@unlink($generated_thumb);
			
			$create_width = $_REQUEST['w'];
			$create_height = $_REQUEST['h'];
								
			$thumb = @imagecreatetruecolor($create_width, $create_height) or die('Cannot Initialize new GD image stream');
			@imagecopyresampled($thumb, $src, 0, 0, 0, 0, $create_width, $create_height, $width, $height);
			
			
			imagedestroy($src);
			
			$array = array(array(-1.2, -1, -1.2),array(-1, 20, -1),array(-1.2, -1, -1.2)); 
			$sharp = array_sum(array_map('array_sum', $array));            
			imageconvolution($thumb, $array, $sharp, 0); 
			imagejpeg($thumb,$generated_thumb,100);
			$ilance->db->query("INSERT INTO ilance_thumbnail (attach_id,image_name,thumbnail_name,path,create_date,
				coin_id,size_id) VALUES (".$line['attachid'].",'". $line['filename']."', '".$generated_file_name."', 
				'".$target_folder."', '".DATETIME24H."', '".$coin_id."', '".$line1['id']."')");
		}
	}	
	$ilance->db->query("update ilance_attachment set thumb_status=1 where attachid='".$line['attachid']."'");	

	}
	
}
?>

