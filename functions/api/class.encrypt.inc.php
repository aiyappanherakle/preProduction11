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
* Escrow class to perform the majority of escrow and related payment functions in ILance
*
* @package      iLance
* @version	$Revision: 1.0.0 $
* @author       ILance
*/
class encrypt
{ 
    private $skey="123456789@great765271234";
    var $text='';
    var $width='';
    var $height='';
    var $font='';
    var $font_dir='';
    var $font_size='';
    var $heig=1;
    function safegc_b64encode($string) 
    {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }

    function Encrypt_Amount($value)
    {
        if(!$value){return false;}
        //$value = "Sold $123.98 || hammer $12.10";
        $skey = $this->skey;
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $skey, $text, MCRYPT_MODE_ECB, $iv);
        //echo safegc_b64encode($crypttext);exit;
        return trim($this->safegc_b64encode($crypttext));
    }
    function safegc_b64decode($string) 
    {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        return base64_decode($data);
    }

    function DecryptAmount($data, $value)
    {
        if(!$value){return false;}

        $skey = $this->skey;
        $crypttext = $this->safegc_b64decode($value); 
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $skey, $crypttext, MCRYPT_MODE_ECB, $iv);
        //echo $decrypttext;exit;
        return trim($decrypttext);
    }
    function DecryptText($value)
    {
        return $this->DecryptAmount(DATE('ddMMYY'),$value);
    }
    
    function print_image()
    {
        $text=$this->DecryptText($this->text);
        $width=$this->width;
        $height=$this->height;
        $font_dir=$this->font_dir;
        $font_size=$this->font_size;
        $font=$this->font;
        $heig=$this->heig;
        $text=wordwrap($text, 50, "||", TRUE);
        $im = imagecreate($width, $height) or die("Cannot Initialize new GD image stream");
        //set the background color of the image
        if(isset($_GET['c']))
            $background_color = imagecolorallocate($im, 255, 255, 204);
        else
            $background_color = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);

        //set the color for the text
        if(isset($_GET['v']))//v for blue text in first line
        {
            $text_color = imagecolorallocate($im, 118, 162, 200);
            $font = "Helvetica Neu Bold.ttf";
        }
        else
            $text_color = imagecolorallocate($im, 0x00, 0x00, 0x00);

        $angle = 0;

        $splittext = explode ( "||" , $text );
        $lines = count($splittext);
        $i = 0;
        foreach ($splittext as $text) 
        {
            if($i>0)//on line two the text becomes grey
            {
                $text_color = imagecolorallocate($im, 153, 153, 153);
                $font_size = 33;
            }
            $text_box = imagettfbbox($font_size,$angle,$font_dir.$font,$text);
            $text_widthh = abs(max($text_box[2], $text_box[4]));
            $text_height = abs(max($text_box[5], $text_box[7]));
            $x = (imagesx($im) - $text_widthh)/6;
            $y = ((imagesy($im) + $text_height)/2)-($lines-$heig)*$text_height;
            $lines=$lines-1.3;
            $i++;
            imagettftext($im, $font_size, $angle, 1, $y, $text_color, $font_dir.$font, $text);
        }

        header('Pragma: public');
        header('Cache-control: max-age=31536000');
        header('Expires: ' . gmdate("D, d M Y H:i:s", time() + 31536000) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy($im);
    }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>