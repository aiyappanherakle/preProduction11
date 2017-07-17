<?php

if(isset($_GET['subval']) and $_GET['subval']=='acc_v1')
{
	$t["09d5db557f480d3849f84b4fd82b465a"]="242628";
$t["0c2dfc987a3eec254a54373f664c4b4d"]="243064";
$t["0e91411b06c749fa7c2c676485eae471"]="243338";
$t["11a18633feed5e87b81225b89feb7f54"]="243061";
$t["1375e49a34d23f7e2a717d5fd17c373e"]="235633";
$t["1519b20378f663b9fcbb9aea0f676c3f"]="243069";
$t["256270186560241e29995e49702ad6c3"]="231527";
$t["2ab7d47b3d56b3083d71a51fbc75822b"]="230503";
$t["38e70e94273ac78e07845d7a3dc9fc11"]="230759";
$t["49f2fc442fa32f2d0de2735cc70379e5"]="244174";
$t["5a74c83aacaafc52700738d492424735"]="230532";
$t["61fbc3b272c83514f0a895e3480c49a1"]="243881";
$t["67429d228b05384258fb3817dd019868"]="244256";
$t["7c6914a42e3b4029ca1403ca70dcaf10"]="233076";
$t["7f7d4c1ecf8bd630b0d2ceec6991057d"]="243869";
$t["90bc86c121ca151f0d0c9bbdac74cf3b"]="243873";
$t["974f1bfcd71ca2883d5023fc37273a9b"]="227752";
$t["9d6881f8cc819db119d5b6f96c16f4ba"]="18653";
$t["a6c3eee5a2c06d4e9a4b779877ef8369"]="234009";
$t["b656af97de036aa32ef6f9af9c81d2ff"]="243254";
$t["c2af9901189d21ef612fc62cd7bfd77f"]="243867";
$t["c90bd3eab9a133d30db8b35d2b4d3ebe"]="243872";
$t["cf3f3036087ebf7c554de4e24878c582"]="243066";
$t["d0e5cb46773fa410e9d0fca2d1bac02f"]="243909";
$t["db092d6c679f4caf1319a0dffedbc05e"]="243349";
$t["e01f07d7c4c944724ae62a81dfb5556f"]="181851";
$t["e74e1b0ad98209a6fa2fdd865c2d6d2c"]="243271";
$t["e9d3861464b0e3038e7049661d16a963"]="245501";
$t["ea3fc56a6c1b7f7c2889595f0dc44375"]="243068";
$t["f5ead084bcc5c2118cf08c8aa98adceb"]="243092";
$t["f6149f5e5bac656b8ea08f1c23e13627"]="243101";
header("location:/email_v2/".$t[$_GET['id']]."-1.jpg");
}



// #### setup script location ##################################################
define('LOCATION','attachment');
$drawborder = false;
$labelimage = false;
$unsharpmask = true;
@ini_set('zlib.output_compression', 'Off');
if (@ini_get('output_handler') == 'ob_gzhandler' AND @ob_get_length() !== false)
{	
	@ob_end_clean();
	header('Content-Encoding:');
}
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) OR !empty($_SERVER['HTTP_IF_NONE_MATCH']))
{
	// fetch sapi result
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
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_attachment.php');
error_reporting(E_ALL);
($apihook = $ilance->api('attachment_start')) ? eval($apihook) : false;
// captcha
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'captcha')
{
        ($apihook = $ilance->api('attachment_captcha_start')) ? eval($apihook) : false;
        print_captcha(5);
}
else
{
        // we don't want the connection activity logging any attachment activity
        define('SKIP_SESSION', true);
}
$ilance->GPC['attachmentid'] = 0;
if (isset($ilance->GPC['crypted']) AND !empty($ilance->GPC['crypted']))
{
	// do we have an attachment id?
	// this would be for members that download digital file attachments
	// so we'll add some extra encryption to the url ..
	$ilance->GPC['uncrypted'] = decrypt_url($ilance->GPC['crypted']);
	if ($ilance->GPC['uncrypted']['id'] > 0)
	{
		$ilance->GPC['id'] = intval($ilance->GPC['uncrypted']['id']);
	}
}
if (isset($ilance->GPC['id']) AND !empty($ilance->GPC['id']))
{
        if (strlen($ilance->GPC['id']) == 32)
        {
                // use new more improved hash method
                $ilance->GPC['attachmentid'] = $ilance->GPC['id'];
                $wheresql = "filehash = '" . $ilance->db->escape_string($ilance->GPC['attachmentid']) . "'";
        }
        else
        {
                // use old method of serving attachment ids
                $ilance->GPC['attachmentid'] = intval($ilance->GPC['id']);
                $wheresql = "attachid = '" . intval($ilance->GPC['attachmentid']) . "'";
        }
}
else
{
        $sapi_name = php_sapi_name();
        if ($sapi_name == 'cgi' OR $sapi_name == 'cgi-fcgi')
        {
                header('Status: 404 Not Found');
        }
        else
        {
                header('HTTP/1.1 404 Not Found');
        }
        exit();
}
$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
$ilance->GPC['subcmd'] = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';
/*
// update attachment view counter
$ilance->db->query("
        UPDATE " . DB_PREFIX . "attachment
        SET counter = counter + 1
        WHERE $wheresql
        LIMIT 1
");
*/
// #### BEGIN ATTACHMENT LOGIC #################################################
$sql = $ilance->db->query("
        SELECT filename, filetype, filesize, filehash, filedata, filetype, attachtype, project_id, user_id, isexternal, coin_id, IF(thumbnail_filesize > 0, 1, 0) AS hasthumbnail
        FROM " . DB_PREFIX . "attachment
        WHERE $wheresql
        LIMIT 1
");
if ($ilance->db->num_rows($sql) > 0)
{
        $attachment = $ilance->db->fetch_array($sql, DB_ASSOC);        
        $attachment['filedata'] = fetch_attachment_rawdata($attachment, $attachment['isexternal']);
        // #### THUMBNAIL ITEM #################################################
        if ($ilance->GPC['cmd'] == 'thumb')
        {
                $src = imagecreatefromstring($attachment['filedata']);
                $width = imagesx($src);
                $height = imagesy($src);
                // thumbnails formatted for admincp defined sizes
                switch ($ilance->GPC['subcmd'])
                {
                        case 'results':
                        {
				$ilance->GPC['w'] = isset($ilance->GPC['w']) ? intval($ilance->GPC['w']) : $ilconfig['attachmentlimit_searchresultsmaxwidth'];
				$ilance->GPC['h'] = isset($ilance->GPC['h']) ? intval($ilance->GPC['h']) : $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                break;
                        }
			case 'resultsgallery':
                        {
				$ilance->GPC['w'] = isset($ilance->GPC['w']) ? intval($ilance->GPC['w']) : $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
				$ilance->GPC['h'] = isset($ilance->GPC['h']) ? intval($ilance->GPC['h']) : $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
                                break;
                        }
			case 'resultssnapshot':
                        {
				$ilance->GPC['w'] = isset($ilance->GPC['w']) ? intval($ilance->GPC['w']) : $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
				$ilance->GPC['h'] = isset($ilance->GPC['h']) ? intval($ilance->GPC['h']) : $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
                                break;
                        }
                        case 'portfolio':
                        {
                                $ilance->GPC['w'] = isset($ilance->GPC['w']) ? intval($ilance->GPC['w']) : $ilconfig['attachmentlimit_portfoliothumbwidth'];
                                $ilance->GPC['h'] = isset($ilance->GPC['h']) ? intval($ilance->GPC['h']) : $ilconfig['attachmentlimit_portfoliothumbheight'];
                                break;
                        }
			case 'portfoliofeatured':
                        {
                                $ilance->GPC['w'] = isset($ilance->GPC['w']) ? intval($ilance->GPC['w']) : 320;
                                $ilance->GPC['h'] = isset($ilance->GPC['h']) ? intval($ilance->GPC['h']) : 240;
                                break;
                        }
                        case 'itemphoto':
                        {
                                $ilance->GPC['w'] = isset($ilance->GPC['w']) ? intval($ilance->GPC['w']) : $ilconfig['attachmentlimit_productphotomaxwidth'];
                                $ilance->GPC['h'] = isset($ilance->GPC['h']) ? intval($ilance->GPC['h']) : $ilconfig['attachmentlimit_productphotomaxheight'];
                                break;
                        }
                        default:
                        {
                                $ilance->GPC['w'] = isset($ilance->GPC['w']) ? intval($ilance->GPC['w']) : $ilconfig['attachmentlimit_thumbnailmaxwidth'];
                                $ilance->GPC['h'] = isset($ilance->GPC['h']) ? intval($ilance->GPC['h']) : $ilconfig['attachmentlimit_thumbnailmaxheight'];
                                break;
                        }
                }
		$xscale = $width / $ilance->GPC['w'];
                $yscale = $height / $ilance->GPC['h'];
		if ($xscale > $yscale)
		{
			$ilance->GPC['w'] = round($width / $xscale);
			$ilance->GPC['h'] = round($height / $xscale);
		}
		else
		{
			$ilance->GPC['w'] = round($width / $yscale);
			$ilance->GPC['h'] = round($height / $yscale);
		}
		// #### ensure we don't stretch the image's width ##############
		if ($width < $ilance->GPC['w'])
		{
			$ilance->GPC['w'] = $width;            
		}
		// #### ensure we don't stretch the image's height #############
		if ($height < $ilance->GPC['h'])
		{
			$ilance->GPC['h'] = $height;
		}
		if ($drawborder)
		{
			$create_width = $ilance->GPC['w'] + 2;
			$create_height = $ilance->GPC['h'] + 2;
			$dest_x_start = 1;
			$dest_y_start = 1;
		}
		else
		{
			$create_width = $ilance->GPC['w'];
			$create_height = $ilance->GPC['h'];
			$dest_x_start = 0;
			$dest_y_start = 0;
		}
		if ($labelimage)
		{
			$font = 2;
			$labelboxheight = ($drawborder) ? 13 : 14;
			if ($attachment['filesize'])
			{
				$filesize = $attachment['filesize'];
			}
			else
			{
				$filesize = @filesize($attachment['filedata']);
			}
			if ($filesize / 1024 < 1)
			{
				$filesize = 1024;
			}
			if ($width)
			{
				$dimensions = $width . 'x' . $height;
			}
			else
			{
				$dimensions = (!empty($width) AND !empty($height)) ? "{$width}x{$height}" : '';
			}
			$sizestring = (!empty($filesize)) ? number_format($filesize / 1024, 0, '', '') . 'kb' : '';
			if (($string_length = (strlen($string = "$dimensions $sizestring $attachment[filetype]") * imagefontwidth($font))) < $ilance->GPC['w'])
			{
				$finalstring = $string;
				$finalwidth = $string_length;
			}
			else if (($string_length = (strlen($string = "$dimensions $sizestring") * imagefontwidth($font))) < $ilance->GPC['w'])
			{
				$finalstring = $string;
				$finalwidth = $string_length;
			}
			else if (($string_length = (strlen($string = $dimensions) * imagefontwidth($font))) < $ilance->GPC['w'])
			{
				$finalstring = $string;
				$finalwidth = $string_length;
			}
			else if (($string_length = (strlen($string = $sizestring) * imagefontwidth($font))) < $ilance->GPC['w'])
			{
				$finalstring = $string;
				$finalwidth = $string_length;
			}
			if (!empty($finalstring))
			{
				$create_height += $labelboxheight;
				if ($drawborder)
				{
					$label_x_start = ($ilance->GPC['w'] - ($finalwidth)) / 2 + 2;
					$label_y_start = ($labelboxheight - imagefontheight($font)) / 2 + $ilance->GPC['h'] + 1;
				}
				else
				{
					$label_x_start = ($ilance->GPC['w'] - ($finalwidth)) / 2 + 1;
					$label_y_start = ($labelboxheight - imagefontheight($font)) / 2 + $ilance->GPC['h'];
				}
			}
		}
		$thumb = @imagecreatetruecolor($create_width, $create_height) or die('Cannot Initialize new GD image stream');
		$bgcolor = imagecolorallocate($thumb, 255, 255, 255);
		imagefill($thumb, 0, 0, $bgcolor);
		@imagecopyresampled($thumb, $src, $dest_x_start, $dest_y_start, 0, 0, $ilance->GPC['w'], $ilance->GPC['h'], $width, $height);
		imagedestroy($src);
		if (PHP_VERSION != '4.3.2' AND $unsharpmask)
		{
			unsharpmask($thumb);
		}
		if ($labelimage AND !empty($finalstring))
		{
			$bgcolor = imagecolorallocate($thumb, 0, 0, 0);
			$recstart = ($drawborder) ? $create_height - $labelboxheight - 1 : $create_height - $labelboxheight;
			imagefilledrectangle($thumb, 0, $recstart, $create_width, $create_height, $bgcolor);
			$textcolor = imagecolorallocate($thumb, 255, 255, 255);
			imagestring($thumb, $font, $label_x_start, $label_y_start, $finalstring, $textcolor);
		}
		if ($drawborder)
		{
			$bordercolor = imagecolorallocate($thumb, 0, 0, 0);
			imageline($thumb, 0, 0, $create_width, 0, $bordercolor);
			imageline($thumb, 0, 0, 0, $create_height, $bordercolor);
			imageline($thumb, $create_width - 1, 0, $create_width - 1, $create_height, $bordercolor);
			imageline($thumb, 0, $create_height - 1, $create_width, $create_height - 1, $bordercolor);
		}
                if (!empty($attachment['filetype']))
                {
                        header("Content-type: " . $attachment['filetype']);
                }
                else
                {
                        header("Content-type: unknown/unknown");        
                }
                header('Cache-control: max-age=31536000');
                header('Expires: ' . gmdate("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                header("Content-disposition: inline; filename=\"" . $attachment['filename'] . "\"");
                header('Content-transfer-encoding: binary');
                if (imagetypes() & IMG_GIF) 
                {
                        imagegif($thumb);
                }
                else if (imagetypes() & IMG_JPG) 
                {
                        imagejpeg($thumb, '', 100);
                }
                else if (imagetypes() & IMG_PNG) 
                {
                        imagepng($thumb, null, 0, PNG_NO_FILTER);
                }
                else if (imagetypes() & IMG_WBMP) 
                {
                        imagewbmp($thumb);
                }
		if (isset($ilance->GPC['subval']) and $ilance->GPC['subval'] == 'acc')
		{		
		imagejpeg($thumb, '/home/gc/public_html/'.$ilconfig['email_image_folder'].'/'.$attachment['filename'], 100);
		}
		if (isset($ilance->GPC['subval']) and $ilance->GPC['subval'] == 'acc_v1')
		{		
		imagejpeg($thumb, '/home/gc/public_html/'.$ilconfig['email_image_folder_v2'].'/'.$attachment['filename'], 100);
		}
		echo $thumb;
		imagedestroy($thumb);
        }
        // #### PROFILE LOGO ###################################################
        else if ($ilance->GPC['cmd'] == 'profile')
        {
                $im = imagecreatefromstring($attachment['filedata']);
                $width = imagesx($im);
                $height = imagesy($im);
                if ($width > $ilconfig['attachmentlimit_profilemaxwidth'] OR $height > $ilconfig['attachmentlimit_profilemaxheight'])
                {
                        $ratio = ($width / $height);
                        if (($width / $height) > $ratio)
                        {
                                $width = ($height * $ratio);
                        }
                        else
                        {
                                $height = ($width / $ratio);
                        }
                }
                $thumb = @imagecreatetruecolor($width, $height) or die('Cannot Initialize new GD image stream');
                // resize the image
                imagecopyresized($thumb, $im, 0, 0, 0, 0, $width, $height, imagesx($im), imagesy($im));
                if (!empty($attachment['filetype']))
                {
                        header('Content-type: ' . $attachment['filetype']);
                }
                else
                {
                        header('Content-type: unknown/unknown');        
                }
                header('Cache-control: max-age=31536000');
                header('Expires: ' . gmdate("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                header("Content-disposition: inline; filename=\"" . $attachment['filename'] . "\"");
                header('Content-transfer-encoding: binary');
                // output thumbnail
                if (imagetypes() & IMG_GIF) 
                {
                        $out = imagegif($thumb);
                }
                else if (imagetypes() & IMG_JPG) 
                {
                        $out = imagejpeg($thumb);
                }
                else if (imagetypes() & IMG_PNG) 
                {
                        $out = imagepng($thumb);
                }
                else if (imagetypes() & IMG_WBMP) 
                {
                        $out = imagewbmp($thumb);
                }
                echo $out;
                imagedestroy($im);
                imagedestroy($thumb);
        }
        // #### PORTFOLIO ITEM #################################################
        else if ($ilance->GPC['cmd'] == 'portfolio')
        {
                header('Cache-control: max-age=31536000');
                header('Expires: ' . gmdate("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                header("Content-disposition: inline; filename=\"" . $attachment['filename'] . "\"");
                header('Content-transfer-encoding: binary');
                if (!empty($attachment['filetype']))
                {
                        header('Content-type: ' . $attachment['filetype']);
                }
                else
                {
                        header('Content-type: unknown/unknown');        
                }
                echo $attachment['filedata'];
        }
        // #### EVERYTHING ELSE ################################################
        else
        {
                $canviewattachment = true;
                // is this attachment associated with any auction bids?
                // if so we must check to see if the auction in question is sealed or blind
                // and if this is the case we deny access to the attachment if the viewer
                // does not meet necessary requirements (is not owner, is not uploader or is not admin)
                if ($attachment['project_id'] > 0)
                {
                        // fetch the project owner id
                        $attachment['project_owner_id'] = fetch_project_ownerid($attachment['project_id']);
                        // is this a workspace attachment?
                        if ($attachment['attachtype'] == 'ws')
                        {
                                // fetch the project winner id
                                $attachment['project_winner_id'] = fetch_project_winnerid($attachment['project_id']);
                                // are we allowed to view digital download?
                                $canviewattachment = false;
                                // does the viewing user have access to download the attachment?
                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
                                        // project owner
                                        AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_owner_id']
                                        // attachment upload user
                                        OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
                                        // awarded winner (product or service)
                                        OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_winner_id']
                                        // administrator
                                        OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
                                {
                                        // attachment is a workspace attachment and we've passed the checkup
                                        // we are most likely the owner or admin viewing
                                        // so we'll allow the attachment to be downloaded
                                        $canviewattachment = true;
                                        if (is_private_workspace_attachment(intval($ilance->GPC['attachmentid'])))
                                        {
                                                // because this attachment is private we should only be
                                                // the uploader or admin!
                                                $canviewattachment = false;
                                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
                                                        // attachment upload user
                                                        AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
                                                        // administrator
                                                        OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
                                                {
                                                        $canviewattachment = true;
                                                }
                                        }
                                }
                        }
                        // is this a digital download attachment?
                        else if ($attachment['attachtype'] == 'digital')
                        {
                                // fetch the project winner id
                                $attachment['project_winner_id'] = fetch_project_winnerid($attachment['project_id']);
                                // are we allowed to view digital download?
                                $canviewattachment = false;
                                // does the viewing user have access to download the attachment?
                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
                                        // project winner
                                        AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_winner_id']
                                        // project owner
                                        OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_owner_id']
                                        // attachment upload user
                                        OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
                                        // administrator
                                        OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
                                {
                                        // attachment is a digital download and we've passed the checkup
                                        // we are most likely the owner or admin viewing
                                        // so we'll allow the attachment to be downloaded
                                        $canviewattachment = true;
                                }
                        }
                        // for everything else (will check if sealed and/or is invite only auction)
                        else
                        {
                                // is this an attachment uploaded by a project owner?
                                if ($attachment['project_owner_id'] > 0)
                                {
                                        // this is an attachment uploaded by a project owner
                                        // we will now check what type of security this auction has such as sealed bids and/or if auction is invite only
                                        // if the auction is invite only the attachments can only be downloaded by the owner, admin and registered invited user(s) (not email invited users)
                                        // is this a sealed or blind bid auction?
                                        if (is_sealed_auction($attachment['project_id']))
                                        {
                                                $canviewattachment = false;
                                                // auction event with this bid is sealed or blind
                                                // does the viewing user have access to download the attachment?
                                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
							AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
                                                                OR $_SESSION['ilancedata']['user']['userid'] == $attachment['project_owner_id']
                                                                OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
                                                {
                                                        // auction is sealed or blind and we've passed the checkup
                                                        // we are most likely the owner or uploader or admin viewing
                                                        // so we'll allow the attachment to be downloaded
                                                        $canviewattachment = true;
                                                }
                                        }
                                        // is this an invite only auction?
                                        else if (is_inviteonly_auction($attachment['project_id']))
                                        {
                                                $canviewattachment = false;
                                                $attachment['invitedusers'] = array();
                                                // fetch users invited for this specific auction id
                                                $invited = $ilance->db->query("
                                                        SELECT seller_user_id AS userid
                                                        FROM " . DB_PREFIX . "project_invitations
                                                        WHERE project_id = '" . $attachment['project_id'] . "'
                                                                AND seller_user_id > 0
                                                ");
                                                if ($ilance->db->num_rows($invited) > 0)
                                                {
                                                        while ($resinvited = $ilance->db->fetch_array($invited))
                                                        {
                                                                // build the user invited array to compare against below
                                                                $attachment['invitedusers'][] = $resinvited['userid'];
                                                        }
                                                }
                                                unset($resinvited, $invited);
                                                // does the viewing user have access to download the attachment?
                                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
							AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
                                                                OR $_SESSION['ilancedata']['user']['userid'] == $attachment['project_owner_id']
                                                                OR $_SESSION['ilancedata']['user']['isadmin'] == '1'
                                                                OR in_array($_SESSION['ilancedata']['user']['userid'], $attachment['invitedusers'])))
                                                {
                                                        // auction is invite only and we've passed the checkup
                                                        // we are most likely the owner, admin or a invited user viewing
                                                        // so we'll allow the attachment to be downloaded
                                                        $canviewattachment = true;
                                                }
                                        }
                                        else
                                        {
                                                // since this bid does not appear to be placed on a sealed or blind or invite only auction we will let the attachment be downloaded
                                                $canviewattachment = true;
                                        }
                                }	
                        }
                }
                if ($canviewattachment)
                {
                        $ext = fetch_extension($attachment['filename']);
                        $isIE = iif($ilance->common->is_webbrowser('ie') OR $ilance->common->is_webbrowser('opera'), true, false);
                        $filetype = ($isIE) ? 'application/octetstream' : 'application/octet-stream';
                        $attachment['filename'] = ($ilance->common->is_webbrowser('mozilla')) ? "filename*=utf-8''" . rawurlencode($attachment['filename']) : 'filename="' . rawurlencode($attachment['filename']) . '"';
                        header('Content-type: ' . $filetype);
                        header('Cache-control: max-age=31536000');
                        header('Expires: ' . gmdate("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                        header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                        header('Content-Length: ' . strlen($attachment['filedata']));
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        // which image extentions can we view inline?
                        if (in_array($ext, array('jpg', 'jpe', 'jpeg', 'gif', 'png')))
                        {
                                // display images inline
                                header('Content-disposition: inline; ' . $attachment['filename']);
                        }
                        else
                        {
                                // force files to be downloaded
                                header('Content-disposition: attachment; ' . $attachment['filename']);
                        }
                        echo $attachment['filedata'];        
                }
                // look's like we cannot view this attachment!
                // download attachment_denied.txt located in the ./uploads/ folder.
                else
                {
                        $attachment['filename'] = 'attachment_denied.txt';
                        $attachment['filedata'] = file_get_contents(DIR_UPLOADS . $attachment['filename']);
                        $isIE = iif($ilance->common->is_webbrowser('ie') OR $ilance->common->is_webbrowser('opera'), true, false);
                        $filetype = ($isIE) ? 'application/octetstream' : 'application/octet-stream';
                        $attachment['filename'] = ($ilance->common->is_webbrowser('mozilla')) ? "filename*=utf-8''" . rawurlencode($attachment['filename']) : 'filename="' . rawurlencode($attachment['filename']) . '"';
                        header('Content-type: ' . $filetype);
                        header('Cache-control: max-age=31536000');
                        header('Expires: ' . gmdate("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                        header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                        header('Content-Length: ' . strlen($attachment['filedata']));
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        header('Content-disposition: attachment; ' . $attachment['filename']);
                        echo $attachment['filedata'];
                }        
        }
}
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>