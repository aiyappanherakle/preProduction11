<?php
error_reporting(0);
require_once('../functions/conf.php');
$con = mysqli_connect(SERVER,SERVER_USERNAME,SERVER_PASSWORD,DATABASE);
define('DIR_AUCTION_ATTACHMENTS', DIR_SERVER_ROOT.'uploads/attachments/auctions/');		
define('COMPRESSED_IMAGE_PATH',DIR_SERVER_ROOT.'image/');
$image=$_SERVER['REQUEST_URI'];
$path_array=array_filter(explode('/',$image));
$filename=array_pop($path_array);
reset($path_array);
array_shift($path_array);
reset($path_array);
$path_array['0']=isset($path_array['0'])?$path_array['0']:'0';
$path_array['1']=isset($path_array['1'])?$path_array['1']:'0';

$destination_path=COMPRESSED_IMAGE_PATH.'/'.implode('/',$path_array).'/';
$designation_file=$destination_path.'/'.$filename;
$_REQUEST['h']=isset($path_array['0'])?$path_array['0']:'0';
$_REQUEST['w']=isset($path_array['1'])?$path_array['1']:'0';

$wherecondition=' filename="'.$filename.'"';
if(!is_file($designation_file))
{

 	$sql = mysqli_query($con,"SELECT filename, filetype, filesize, filehash, filedata, filetype, coin_id
                           FROM " . DB_PREFIX . "attachment
                           WHERE $wherecondition
                           LIMIT 1");
	if(mysqli_num_rows($sql) > 0)
	{
		$row = mysqli_fetch_assoc($sql);
		$coin_id = $row['coin_id'];
		$filehash =  $row['filehash'].'.attach'; 
		$src_path=DIR_AUCTION_ATTACHMENTS .floor($coin_id/100).'00/'.  $coin_id . '/'. $filehash;
		$destination_path=$destination_path;
		if(!is_readable($destination_path))
		{
			mkdir($destination_path,0777,true);
		}
		if(is_file($src_path))
		{
			$filedata = file_get_contents($src_path);
			$src = imagecreatefromstring($filedata);
			$width = imagesx($src);
			$height = imagesy($src);
					if($_REQUEST['w']==0 and $_REQUEST['h']==0)
					{
						$_REQUEST['w']=$width;
						$_REQUEST['h']=$height;
					}
					$xscale = $width / $_REQUEST['w'];
					
					$yscale = $height / $_REQUEST['h'];
					
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
					imagejpeg($thumb,$designation_file,100);
					chmod($designation_file, 0777);
					
					
					if (!empty($row['filetype']))
					{
						header("Content-type: " . $row['filetype']);
					}
					else
					{
						header("Content-type: unknown/unknown");        
					}
					
					//header("Content-Encoding: gzip");
					header('Cache-control: max-age=31536000');
					header('Expires: ' . gmdate("D, d M Y H:i:s", time() + 31536000) . ' GMT');
					header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
					header('ETag: "' . $filename . '"');
					header("Content-disposition: inline; filename=\"" . $row['filename'] . "\"");
					header('Content-transfer-encoding: binary');
								   
					if(imagetypes() & IMG_GIF) 
					{
						imagegif($thumb);
					}
					else if(imagetypes() & IMG_JPG) 
					{
						imagejpeg($thumb);
					}
					else if(imagetypes() & IMG_PNG) 
					{
						imagepng($thumb, null, 0, PNG_NO_FILTER);
					}
					else if(imagetypes() & IMG_WBMP) 
					{
						imagewbmp($thumb);
							
					}
					
					echo $thumb;
					imagedestroy($thumb);
		}else
		{
		echo "src not found";
		}
	}
	mysqli_close($con);
}

?>
