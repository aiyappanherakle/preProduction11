<?php
//ob_start('ob_gzhandler');
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) OR !empty($_SERVER['HTTP_IF_NONE_MATCH']))
{
	$sapi_name = php_sapi_name();
	if ($sapi_name == 'cgi' OR $sapi_name == 'cgi-fcgi')
	{
		header('Status: 304 Not Modified');
	}
	else
	{
		header('HTTP/1.1 304 Not Modified');
	}
	
	header('Content-Type:');
	header('X-Powered-By:');
        
	if (!empty($_REQUEST['id']))
	{
		header('Etag: "' . $_REQUEST['id'] . '"');
	}
        
	exit();
}


define('LOCATION','attachment');

define('DIR_AUCTION_ATTACHMENTS', '/home/gc/public_html/uploads/attachments/auctions/');

define('USERAGENT', (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'));

define('TIMESTAMPNOW',
		mktime(gmdate('H',time() + 3600 * (-5 + 0)),gmdate('i',time() + 3600 * (-5 + 0)),gmdate('s',time() + 3600 * (-5 + 0)),gmdate('m',time() + 3600 * (-5 + 0)),
		gmdate('d',time() + 3600 * (-5 + 0)),gmdate('Y',time() + 3600 * (-5 + 0))));

function browser($browser, $version = 0)
{
		global $_SERVER;
		static $is;
            
                $agent = mb_strtolower(USERAGENT);
                
		if (!is_array($is))
		{
			$useragent = $agent;
			$is = array(
				'opera' => 0,
				'ie' => 0,
				'mozilla' => 0,
				'firebird' => 0,
				'firefox' => 0,
				'camino' => 0,
				'konqueror' => 0,
				'safari' => 0,
				'webtv' => 0,
				'netscape' => 0,
				'mac' => 0,
				'chrome' => 0,
				'aol' => 0,
				'lynx' => 0,
				'phoenix' => 0,
				'omniweb' => 0,
				'icab' => 0,
				'mspie' => 0,
				'netpositive' => 0,
				'galeon' => 0,
				'iphone' => 0,
				'ipad' => 0,
				'android' => 0,
				'blackberry' => 0,
                        );
                        
			if (mb_strpos($useragent, 'opera') !== false)
			{
				preg_match('#opera(/| )([0-9\.]+)#', $useragent, $regs);
				$is['opera'] = $regs[2];
			}
			if (mb_strpos($useragent, 'msie ') !== false AND !$is['opera'])
			{
				preg_match('#msie ([0-9\.]+)#', $useragent, $regs);
				$is['ie'] = $regs[1];
			}
			if (mb_strpos($useragent, 'mac') !== false)
			{
				$is['mac'] = 1;
			}
			if (mb_strpos($useragent, 'camino') !== false)
			{
				$is['camino'] = 1;
			}
                        if (mb_strpos($useragent, 'chrome') !== false)
			{
				$is['chrome'] = 1;
			}
			if (mb_strpos($useragent, 'safari') !== false OR mb_strpos($useragent, 'safari') !== false AND $is['mac'])
			{
				preg_match('#safari/([0-9\.]+)#', $useragent, $regs);
				$is['safari'] = $regs[1];
			}
			if (mb_strpos($useragent, 'konqueror') !== false)
			{
				preg_match('#konqueror/([0-9\.-]+)#', $useragent, $regs);
				$is['konqueror'] = $regs[1];
			}
			if (mb_strpos($useragent, 'gecko') !== false AND !$is['safari'] AND !$is['konqueror'] AND !$is['chrome'])
			{
				preg_match('#gecko/(\d+)#', $useragent, $regs);
				$is['mozilla'] = $regs[1];
				if (mb_strpos($useragent, 'firefox') !== false OR mb_strpos($useragent, 'firebird') !== false OR mb_strpos($useragent, 'phoenix') !== false)
				{
					preg_match('#(phoenix|firebird|firefox)( browser)?/([0-9\.]+)#', $useragent, $regs);
					$is['firebird'] = $regs[3];
					if ($regs[1] == 'firefox')
					{
						$is['firefox'] = $regs[3];
					}
				}
				if (mb_strpos($useragent, 'chimera') !== false OR mb_strpos($useragent, 'camino') !== false)
				{
					preg_match('#(chimera|camino)/([0-9\.]+)#', $useragent, $regs);
					$is['camino'] = $regs[2];
				}
			}
			if (mb_strpos($useragent, 'webtv') !== false)
			{
				preg_match('#webtv/([0-9\.]+)#', $useragent, $regs);
				$is['webtv'] = $regs[1];
			}
			if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#', $useragent, $regs))
			{
				$is['netscape'] = "$regs[1].$regs[2]";
			}
                        if (mb_strpos($useragent, 'aol') !== false)
			{
				$is['aol'] = 1;
			}
                        if (mb_strpos($useragent, 'lynx') !== false)
			{
				$is['lynx'] = 1;
			}
                        if (mb_strpos($useragent, 'phoenix') !== false)
			{
				$is['phoenix'] = 1;
			}
			if (mb_strpos($useragent, 'firebird') !== false)
			{
				$is['firebird'] = 1;
			}
                        if (mb_strpos($useragent, 'omniweb') !== false)
			{
				$is['omniweb'] = 1;
			}
                        if (mb_strpos($useragent, 'icab') !== false)
			{
				$is['icab'] = 1;
			}
                        if (mb_strpos($useragent, 'mspie') !== false)
			{
				$is['mspie'] = 1;
			}
                        if (mb_strpos($useragent, 'netpositive') !== false)
			{
				$is['netpositive'] = 1;
			}
                        if (mb_strpos($useragent, 'galeon') !== false)
			{
				$is['galeon'] = 1;
			}
			if (mb_strpos($useragent, 'iphone') !== false)
			{
				$is['iphone'] = 1;
			}
			if (mb_strpos($useragent, 'ipad') !== false)
			{
				$is['ipad'] = 1;
			}
			if (mb_strpos($useragent, 'android') !== false)
			{
				$is['android'] = 1;
			}
			if (mb_strpos($useragent, 'blackberry') !== false)
			{
				$is['blackberry'] = 1;
			}
		}
                
		$browser = mb_strtolower($browser);
		if (mb_substr($browser, 0, 3) == 'is_')
		{
			$browser = mb_substr($browser, 3);
		}
                
		if ($is["$browser"])
		{
			if ($version)
			{
				if ($is["$browser"] >= $version)
				{
					return $is["$browser"];
				}
			}
			else
			{
				return $is["$browser"];
			}
		}
                
		return 0;
	
}
	
if (isset($_REQUEST['id']) and !empty($_REQUEST['id']))
{
	   $wherecondition = (strlen($_REQUEST['id']) == 32) ?  "filehash = '" . mysql_escape_string($_REQUEST['id']) . "'" : "attachid = '" . intval($_REQUEST['id']) . "'";
	   
	   $cmd     = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : '';
       $subcmd  = isset($_REQUEST['subcmd']) ? $_REQUEST['subcmd'] : '';	
	   $required_width=isset($_REQUEST['w']) ? $_REQUEST['w'] : '';	
	   $required_height=isset($_REQUEST['w']) ? $_REQUEST['h'] : '';
		$generated_thumb=DIR_AUCTION_ATTACHMENTS .'thumbs' . '/'. $_REQUEST['id'] .'.'.$required_width.'x'.$required_height.$subcmd;
					/*if(is_file($generated_thumb))
					{
						header('Content-Type: image/jpeg');
						header('Cache-control: max-age=31536000');
						header('Expires: ' . gmdate("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
						header('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
						header('ETag: "' . $_REQUEST['id'] . '"');
						readfile($generated_thumb);
						exit;
					}*/
					
		require_once('./functions/connect.php');

		$con = mysql_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD);

		if(!$con)
		{
			die('cannot connect'.mysql_error());
		}

		mysql_select_db(DB_DATABASE,$con);
			
	   $sql = mysql_query("SELECT filename, filetype, filesize, filehash, filedata, filetype, coin_id
                           FROM " . DB_PREFIX . "attachment
                           WHERE $wherecondition
                           LIMIT 1");
		 
		if(mysql_num_rows($sql) > 0)
		{
		
			 $row = mysql_fetch_assoc($sql);
				
			 $coin_id = $row['coin_id'];  
			  
			 $filehash =  $row['filehash']; 
			  if(is_file(DIR_AUCTION_ATTACHMENTS .floor($coin_id/100).'00/'.  $coin_id . '/'. $filehash .'.'.$subcmd))
					{
						header('Content-Type: image/jpeg');
						readfile(DIR_AUCTION_ATTACHMENTS .floor($coin_id/100).'00/'.  $coin_id . '/'. $filehash .'.'.$subcmd);
						exit;
					}	
			
			 $row['filedata'] = file_get_contents(DIR_AUCTION_ATTACHMENTS .floor($coin_id/100).'00/'.  $coin_id . '/' . $filehash . '.attach');
			  
		      if($cmd == 'thumb')
			  {			  
					$src = imagecreatefromstring($row['filedata']);
					$width = imagesx($src);
					$height = imagesy($src);
									
					switch ($subcmd)
					{
							case 'results':
							{
							
								if(isset($_REQUEST['bigimge']) and $_REQUEST['bigimge'] == '1'){
								
								  $maxwidthnew = '206';
								  $maxheightnew = '172';
								
								}else{
								
								  $maxwidthnew = '96';
								  $maxheightnew = '72';
								}
								
									$_REQUEST['w'] = isset($_REQUEST['w']) ? intval($_REQUEST['w']) : $maxwidthnew;
									$_REQUEST['h'] = isset($_REQUEST['h']) ? intval($_REQUEST['h']) : $maxheightnew;
									break;
							}
							case 'resultsgallery':
							{
									$_REQUEST['w'] = isset($_REQUEST['w']) ? intval($_REQUEST['w']) : 150;//$ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
									$_REQUEST['h'] = isset($_REQUEST['h']) ? intval($_REQUEST['h']) : 150;//$ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
									break;
							}
				
							case 'itemphoto':
							{
									$_REQUEST['w'] = isset($_REQUEST['w']) ? intval($_REQUEST['w']) : '1024';//$ilconfig['attachmentlimit_productphotomaxwidth'];
									$_REQUEST['h'] = isset($_REQUEST['h']) ? intval($_REQUEST['h']) : '768';//$ilconfig['attachmentlimit_productphotomaxheight'];
									break;
							}
							default:
							{
									$_REQUEST['w'] = isset($_REQUEST['w']) ? intval($_REQUEST['w']) : 96;//$ilconfig['attachmentlimit_thumbnailmaxwidth'];
									$_REQUEST['h'] = isset($_REQUEST['h']) ? intval($_REQUEST['h']) : 72;//$ilconfig['attachmentlimit_thumbnailmaxheight'];
									break;
							}
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
					
					
					$create_width = $_REQUEST['w'];
					$create_height = $_REQUEST['h'];
										
					$thumb = @imagecreatetruecolor($create_width, $create_height) or die('Cannot Initialize new GD image stream');
					@imagecopyresampled($thumb, $src, 0, 0, 0, 0, $create_width, $create_height, $width, $height);
					
					//imagejpeg($thumb,$generated_thumb);
					imagedestroy($src);
					
					$array = array(array(-1.2, -1, -1.2),array(-1, 20, -1),array(-1.2, -1, -1.2)); 
					$sharp = array_sum(array_map('array_sum', $array));            
					imageconvolution($thumb, $array, $sharp, 0); 
					
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
					header('Expires: ' . gmdate("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
					header('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
					header('ETag: "' . $_REQUEST['id'] . '"');
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
			
				  
			  }
			  else
			  {		
			       	$ext = mb_substr(mb_strrchr($row['filename'], '.'), 1);
					$isIE = (browser('ie') OR browser('opera')) ? true : false;
					$filetype = ($isIE) ? 'application/octetstream' : 'application/octet-stream';
					$row['filename'] = (browser('mozilla')) ? "filename*=utf-8''" . rawurlencode($row['filename']) : 'filename="' . rawurlencode($row['filename']) . '"';
					
					header('Content-type: ' . $filetype);
					header('Cache-control: max-age=31536000');
					header('Expires: ' . gmdate("D, d M Y H:i:s", 31536000 + 31536000) . ' GMT');
					header('Last-Modified: ' . gmdate('D, d M Y H:i:s', 31536000) . ' GMT');
					header('ETag: "' . $_REQUEST['id'] . '"');
					header('Content-Length: '.strlen($row['filedata']));
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					
					// which image extentions can we view inline?
					if (in_array($ext, array('jpg', 'jpe', 'jpeg', 'gif', 'png')))
					{
							// display images inline
							header('Content-disposition: inline; ' . $row['filename']);
					}
					else
					{
							// force files to be downloaded
							header('Content-disposition: attachment; ' . $row['filename']);
					}
					
					echo $row['filedata'];  			  	
			  }
			
        //db query end
		}

  //set of id 

}
else
{
	echo '<div align="center">"Image cannot be display, it contain errors"</div>';
	exit();
}
?>