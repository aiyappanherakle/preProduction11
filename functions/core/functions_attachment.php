<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

/**
* Core Attachment functions in ILance.
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/

/**
* Function fetch the full attachment path for the specified attachment type
*
* @param	string		attachment type
*
* @return	string          attachment folder
*/
function fetch_attachment_path($attachtype = '')
{
        if ($attachtype == 'profile')
        {
                $filedata = DIR_PROFILE_ATTACHMENTS;
        }
        else if ($attachtype == 'portfolio')
        {
                $filedata = DIR_PORTFOLIO_ATTACHMENTS;
        }
        else if ($attachtype == 'project')
        {
                $filedata = DIR_AUCTION_ATTACHMENTS;
        }
        else if ($attachtype == 'itemphoto')
        {
                $filedata = DIR_AUCTION_ATTACHMENTS;
        }
        else if ($attachtype == 'bid')
        {
                $filedata = DIR_BID_ATTACHMENTS;
        }
        else if ($attachtype == 'pmb')
        {
                $filedata = DIR_PMB_ATTACHMENTS;
        }
        else if ($attachtype == 'ws')
        {
                $filedata = DIR_WS_ATTACHMENTS;
        }
        else if ($attachtype == 'kb')
        {
                $filedata = DIR_KB_ATTACHMENTS;
        }
        else if ($attachtype == 'stores' OR $attachtype == 'storesitemphoto' OR $attachtype == 'storesdigital')
        {
                $filedata = DIR_STORE_ATTACHMENTS;
        }
        else if ($attachtype == 'ads')
        {
                $filedata = DIR_ADS_ATTACHMENTS;
        }
        else if ($attachtype == 'digital')
        {
                $filedata = DIR_AUCTION_ATTACHMENTS;
        }
        else if ($attachtype == 'slideshow')
        {
                $filedata = DIR_AUCTION_ATTACHMENTS;
        }
        
        return $filedata;    
}

/**
* Function fetch the full attachment path and actual file(name) for the specified attachment type
*
* @param	string		attachment type
* @param        string          actual filehash
*
* @return	string          full path and filename to the attachment
*/
function fetch_attachment_file($attachtype = '', $filehash = '', $coin_id='')
{
        if ($attachtype == 'profile')
        {
                $filedata = DIR_PROFILE_ATTACHMENTS . $filehash . '.attach';
        }
        else if ($attachtype == 'portfolio')
        {
                $filedata = DIR_PORTFOLIO_ATTACHMENTS . $filehash . '.attach';
        }
        else if ($attachtype == 'project')
        {
                $filedata = DIR_AUCTION_ATTACHMENTS .floor($coin_id/100).'00/'. $coin_id . '/' . $filehash . '.attach';
        }
        else if ($attachtype == 'itemphoto')
        {
                $filedata = DIR_AUCTION_ATTACHMENTS .floor($coin_id/100).'00/'.  $coin_id . '/' . $filehash . '.attach';
        }
        else if ($attachtype == 'bid')
        {
                $filedata = DIR_BID_ATTACHMENTS . $filehash . '.attach';
        }
        else if ($attachtype == 'pmb')
        {
                $filedata = DIR_PMB_ATTACHMENTS . $filehash . '.attach';
        }
        else if ($attachtype == 'ws')
        {
                $filedata = DIR_WS_ATTACHMENTS . $filehash . '.attach';
        }
        else if ($attachtype == 'kb')
        {
                $filedata = DIR_KB_ATTACHMENTS . $filehash . '.attach';
        }
        else if ($attachtype == 'stores' OR $attachtype == 'storesitemphoto' OR $attachtype == 'storesdigital')
        {
                $filedata = DIR_STORE_ATTACHMENTS . $filehash . '.attach';
        }
        else if ($attachtype == 'ads')
        {
                $filedata = DIR_ADS_ATTACHMENTS . $filehash . '.attach';
        }   
        else if ($attachtype == 'digital')
        {
                $filedata = DIR_AUCTION_ATTACHMENTS . $filehash . '.attach';
        }
        else if ($attachtype == 'slideshow')
        {
                $filedata = DIR_AUCTION_ATTACHMENTS .floor($coin_id/100).'00/'.  $coin_id . '/' . $filehash . '.attach';
        }

        return $filedata;
}

/**
* Function returns logic of attachment storage system and returns back required raw attachment data
*
* @param	array 	        attachment array
* @param        boolean         (true/false) determines if this attachment is "external" (true) which would indicate a "url" to display an "image/picture".
*
* @return	string          Returns raw attachment file data
*/
function fetch_attachment_rawdata($attachment, $isexternal = false)
{
        global $ilconfig;
        
        if ($ilconfig['attachment_dbstorage'])
        {
                return $attachment['filedata'];
        }
        else
        {
                if ($isexternal == false)
                {
                        $attachment['filepath'] = fetch_attachment_file($attachment['attachtype'], $attachment['filehash'],$attachment['coin_id']);
                        $attachment['filedata'] = file_get_contents($attachment['filepath']);
                }
                
                return $attachment['filedata'];
        }
}

/**
* Function to print out the captcha via GD + FreeType
*
* @param	integer 	length of captcha phrase
*
* @return	string          Returns image data
*/
function print_captcha($length = 5)
{
        $src = 'abcdefghjkmnpqrstuvwxyz23456789';
        if (mt_rand(0, 1) == 0)
        {
                $src = mb_strtoupper($src);
        }
        $srclen = mb_strlen($src) - 1;
    
        // what font file to use
        $font = DIR_FONTS . 'AppleGaramond.ttf';
    
        // captcha output type (jpeg, png, gif)
        $output_type = 'png';
    
        // font size range, angle range, character padding
        $min_font_size = 25;
        $max_font_size = 35;
        $min_angle = -15;
        $max_angle = 15;
    
        // font character padding
        $char_padding = 1;
    
        $data = array();
        $image_width = $image_height = 0;
    
        // build the data array of the characters, size, placement, etc.
        $_SESSION['ilancedata']['user']['captcha'] = '';
        for ($i = 0; $i < $length; $i++)
        {
                $char = mb_strtoupper(mb_substr($src, mt_rand(0, $srclen), 1));
                $_SESSION['ilancedata']['user']['captcha'] .= "$char";
                
                $size = mt_rand($min_font_size, $max_font_size);
                $angle = mt_rand($min_angle, $max_angle);        
                $bbox = imagettfbbox($size, $angle, $font, $char);
                $char_width = max($bbox[2],$bbox[4]) - min($bbox[0],$bbox[6]);
                $char_height = max($bbox[1],$bbox[3]) - min($bbox[7],$bbox[5]);
        
                $image_width += $char_width + $char_padding;
                $image_height = max($image_height, $char_height);
                $data[] = array('char' => $char, 'size' => $size, 'angle' => $angle, 'height' => $char_height, 'width' => $char_width,);
        }
    
        // calculate the final image size, add some padding
        $x_padding = 12;
        $image_width += ($x_padding * 2);
        $image_height = ($image_height * 1.5) + 2;
    
        // build the image, and allocte the colors
        $im = imagecreate($image_width, $image_height);
        $r = 51 * mt_rand(4, 5);
        $g = 51 * mt_rand(4, 5);
        $b = 51 * mt_rand(4, 5);
        $color_bg = imagecolorallocate($im, $r, $g, $b);
    
        $r = 51 * mt_rand(3, 4);
        $g = 51 * mt_rand(3, 4);
        $b = 51 * mt_rand(3, 4);
        $color_line0 = imagecolorallocate($im, $r, $g, $b);
    
        $r = 51 * mt_rand(3, 4);
        $g = 51 * mt_rand(3, 4);
        $b = 51 * mt_rand(3, 4);
        $color_line1 = imagecolorallocate($im, $r, $g, $b);
    
        $r = 51 * mt_rand(1, 2);
        $g = 51 * mt_rand(1, 2);
        $b = 51 * mt_rand(1, 2);
    
        $color_text = imagecolorallocate($im, $r, $g, $b);
        $color_border = imagecolorallocate($im, 0, 0, 0);
    
        // make the random background lines
        for ($l = 0; $l < 10; $l++)
        {
                $c = 'color_line' . ($l%2);
                $lx = mt_rand(0, $image_width + $image_height);
                $lw = mt_rand(0, 3);
                if ($lx > $image_width)
                {
                        $lx -= $image_width;
                        imagefilledrectangle($im, 0, $lx, $image_width - 1, $lx + $lw, $$c );
                }
                else
                {
                        imagefilledrectangle($im, $lx, 0, $lx+$lw, $image_height - 1, $$c );
                }
        }
    
        // output each character
        $pos_x = $x_padding + ($char_padding / 2);
        foreach($data AS $d)
        {
                $pos_y = (($image_height + $d['height']) / 2);
                imagettftext($im, $d['size'], $d['angle'], $pos_x, $pos_y, $color_text, $font, $d['char']);
                $pos_x += $d['width'] + $char_padding;
        }
    
        // create a border
        imagerectangle($im, 0, 0, $image_width - 1, $image_height - 1, $color_border);
    
        switch ($output_type)
        {
                case 'jpeg':
                {
                        header('Content-type: image/jpeg');
                        imagejpeg($im, null, 100);
                        break;
                }            
                case 'png':
                {
                        header('Content-type: image/png');
                        imagepng($im);
                        break;
                }            
                case 'gif':
                {
                        header('Content-type: image/gif');
                        imagegif($im);
                        break;
                }
        }
        
        imagedestroy($im);
}

/*
* Function for determining if a particular attachment id is associated with a private workspace folder.
*
* @param       integer        attachment id
*
* @return      bool           true or false if attachment exists to a private workspace folder
*/
function is_private_workspace_attachment($attachid = 0)
{
	global $ilance, $myapi;
        
	$sql = $ilance->db->query("
		SELECT tblfolder_ref
		FROM " . DB_PREFIX . "attachment
		WHERE attachid = '" . intval($attachid) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql);
		if ($res['tblfolder_ref'] > 0)
		{
			$sql2 = $ilance->db->query("
				SELECT folder_type
				FROM " . DB_PREFIX . "attachment_folder
				WHERE id = '" . $res['tblfolder_ref'] . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$res2 = $ilance->db->fetch_array($sql2);
				if ($res2['folder_type'] == '1')
				{
					// attachment IS related to private workspace..
					return 1;
				}
			}
		}
	}
        
	return 0;
}

/*
* Function to fetch the attachment type
*
* @param       string         type
* @param       integer        project id
*
* @return      string         Returns the attachment type
*/
function fetch_attachment_type($type, $projectid = '')
{
        global $ilance, $phrase;
        
        switch ($type)
        {
                case 'profile':
                {
                        return $phrase['_profile_logo'];
                        break;
                }            
                case 'portfolio':
                {
                        return $phrase['_portfolio_item'];
                        break;
                }            
                case 'project':
                {
                        return $phrase['_listing_attachment'] . '<div class="smaller">' . $projectid . '</div>';
                        break;
                }
                case 'itemphoto':
                case 'storesitemphoto':
                {
                        return $phrase['_item_photo'] . '<div class="smaller">' . $projectid . '</div>';
                        break;
                }            
                case 'bid':
                {
                        return $phrase['_bid_attachment'] . '<div class="smaller">' . $projectid . '</div>';
                        break;
                }            
                case 'pmb':
                {
                        return $phrase['_pmb_attachment'];
                        break;
                }            
                case 'ws':
                {
                        return $phrase['_mediashare'] . '<div class="smaller">' . $projectid . '</div>';
                        break;
                }            
                case 'digital':
                case 'storesdigital':
                {
                        return $phrase['_digital_download'] . '<div class="smaller">' . $projectid . '</div>';
                        break;
                }            
                case 'slideshow':
                {
                        return $phrase['_slideshow_photo'] . '<div class="smaller">' . $projectid . '</div>';
                        break;
                }
                case 'stores':
                {
                        return 'Storefront Logo<div class="smaller">' . $projectid . '</div>';
                        break;
                }
        }
}

/*
* Function to move all attachments within the database to the filepath
*
* @return      string         Returns a notice of actions that occured
*/
function move_attachments_to_filepath()
{
        global $ilance, $ilconfig, $phrase, $ilpage;
        
        $notice = '';
        
        $sql = $ilance->db->query("
                SELECT attachid, attachtype, filedata, filename
                FROM " . DB_PREFIX . "attachment
                ORDER BY attachid ASC
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $notice = '<ol>';
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $attachpath = fetch_attachment_path($res['attachtype']);
                        $newfilehash = md5(uniqid(microtime()));
                        $newfilename = $attachpath . $newfilehash . '.attach';
                        
                        // remove attachment from database and set the new file hash
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "attachment
                                SET filehash = '" . $ilance->db->escape_string($newfilehash) . "',
                                filedata = ''
                                WHERE attachid = '" . $res['attachid'] . "'
                        ");
                        
                        // write the attachment to the file system
                        $fp = @fopen($newfilename, "wb");
                        fwrite($fp, $res['filedata']);
                        @fclose($fp);
                        
                        $notice .= '<li>Moved <strong>' . $res['filename'] . ' to ' . $newfilename . '</strong></li>';
                }
                $notice .= '</ol>';
        }
        
        $ilance->db->query("
                UPDATE " . DB_PREFIX . "configuration
                SET value = '0'
                WHERE name = 'attachment_dbstorage'
        ");
        
        return $notice;
}

/*
* Function to move all attachments within the filepath to the database
*
* @return      string         Returns a notice of actions that occured
*/
function move_attachments_to_database()
{
        global $ilance, $ilconfig, $phrase, $ilpage;
        
        $notice = '';
        
        $sql = $ilance->db->query("
                SELECT attachid, attachtype, filehash, filename
                FROM " . DB_PREFIX . "attachment
                ORDER BY attachid ASC
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $notice = '<ol>';
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $attachpath = fetch_attachment_path($res['attachtype']);
                        $filename = $attachpath . $res['filehash'] . '.attach';
                        
                        if (file_exists($filename))
                        {
                                $filesize = filesize($filename);
                                
                                $fp = fopen($filename, "rb");
                                $newfiledata = fread($fp, $filesize);
                                fclose($fp);
        
                                if (empty($newfiledata) OR !isset($newfiledata))
                                {
                                        $newfiledata = 'empty_filedata';
                                }
                                
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "attachment
                                        SET filedata = '" . $ilance->db->escape_string($newfiledata) . "'
                                        WHERE attachid = '" . $res['attachid'] . "'
                                ");
                                unset($newfiledata, $fp);
                                
                                // remove attachment from file system
                                @unlink($filename);
                                
                                $notice .= '<li>Moved <strong>' . $filename . ' to database</strong></li>';
                        }
                }                
                $notice .= '</ol>';
                
                /*$empties = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "attachment
                        WHERE filedata = 'empty_filedata'
                ");
                $emptystring = '';
                if ($ilance->db->num_rows($empties) > 0)
                {
                        while ($empty = $ilance->db->fetch_array($empties))
                        {
                                $attachmentids[] = $empty['attachid'];
                        }
                        if (is_array($attachmentids))
                        {
                                $emptystring  = "<br /><br /><strong>Notice:</strong> " . sizeof($attachmentids) . " empty attachments were imported, to remove these empty attachments run the following database queries<br />\n";
                                $emptystring .= "DELETE FROM " . DB_PREFIX . "attachment WHERE attachid IN(" . implode(',', $attachmentids) . ")<br />\n";
                        }
                }*/
        }
        
        $ilance->db->query("
                UPDATE " . DB_PREFIX . "configuration
                SET value = '1'
                WHERE name = 'attachment_dbstorage'
        ");
        
        return $notice;
}

function unsharpmask(&$thumb, $amount = 100, $radius = .5, $threshold = 3)
{
        if ($amount > 500)
        {
                $amount = 500;
        }
        $amount = $amount * 0.016;
        if ($radius > 50)
        {
                $radius = 50;
        }
        $radius = $radius * 2;
        if ($threshold > 255)
        {
                $threshold = 255;
        }

        $radius = abs(round($radius));
        if ($radius == 0)
        {
                return true;
        }

        $w = imagesx($thumb);
        $h = imagesy($thumb);
        $imgCanvas = imagecreatetruecolor($w, $h);
        $imgCanvas2 = imagecreatetruecolor($w, $h);
        $imgBlur = imagecreatetruecolor($w, $h);
        $imgBlur2 = imagecreatetruecolor($w, $h);
        imagecopy ($imgCanvas, $thumb, 0, 0, 0, 0, $w, $h);
        imagecopy ($imgCanvas2, $thumb, 0, 0, 0, 0, $w, $h);

        for ($i = 0; $i < $radius; $i++)
        {
                imagecopy ($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1); // up left
                imagecopymerge ($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
                imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
                imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25); // up right
                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
                imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 ); // up
                imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
                imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);
                imagecopy ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 20 );
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 16.666667);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
                imagecopy ($imgCanvas2, $imgBlur2, 0, 0, 0, 0, $w, $h);
        }
        imagedestroy($imgBlur);
        imagedestroy($imgBlur2);

        for ($x = 0; $x < $w; $x++)
        { // each row
                for ($y = 0; $y < $h; $y++)
                { // each pixel

                        $rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
                        $rOrig = (($rgbOrig >> 16) & 0xFF);
                        $gOrig = (($rgbOrig >> 8) & 0xFF);
                        $bOrig = ($rgbOrig & 0xFF);

                        $rgbBlur = ImageColorAt($imgCanvas, $x, $y);

                        $rBlur = (($rgbBlur >> 16) & 0xFF);
                        $gBlur = (($rgbBlur >> 8) & 0xFF);
                        $bBlur = ($rgbBlur & 0xFF);

                        // When the masked pixels differ less from the original
                        // than the threshold specifies, they are set to their original value.
                        $rNew = (abs($rOrig - $rBlur) >= $threshold) ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))	: $rOrig;
                        $gNew = (abs($gOrig - $gBlur) >= $threshold) ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))	: $gOrig;
                        $bNew = (abs($bOrig - $bBlur) >= $threshold) ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))	: $bOrig;

                        if (($rOrig != $rNew) OR ($gOrig != $gNew) OR ($bOrig != $bNew))
                        {
                                $pixCol = ImageColorAllocate($thumb, $rNew, $gNew, $bNew);
                                ImageSetPixel($thumb, $x, $y, $pixCol);
                        }
                }
        }
        imagedestroy($imgCanvas);
        imagedestroy($imgCanvas2);

        return true;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>