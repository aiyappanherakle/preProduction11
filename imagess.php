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
|| # http://www.ilance.com | http://www.ilance.com/eula | info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

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

    $skey = "123456789@great76527";
    $crypttext = safegc_b64decode($value); 
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $skey, $crypttext, MCRYPT_MODE_ECB, $iv);
    //echo $decrypttext;exit;
    return trim($decrypttext);
}


if ((isset($_GET['q']) and !empty($_GET['q'])))
{
    $text = DecryptAmount(DATE('ddMMYY'), $_GET['q']);
    $string_text = explode('||', $text);
    $widt = round(strlen($string_text[0])*7.6)+22;//echo $widt;exit;
    $widt = 180*3;
    $heit = 33*3;

}
else if ((isset($_GET['fq']) and !empty($_GET['fq'])))
{
    $text = DecryptAmount(DATE('ddMMYY'), $_GET['fq']);
    $string_text = explode('||', $text);
    $widt = round(strlen($string_text[0])*7.6)+18;
    $widt = 152*3;
    $heit = 33*3;
}
else if((isset($_GET['w']) and !empty($_GET['w'])) )
{
    $text = DecryptAmount(DATE('ddMMYY'), $_GET['w']);
    // echo $text.'<br/>';
    // echo strlen($text).'<br/>'; exit;
    $text_lenght =  strlen(preg_replace('/[^a-zA-Z0-9$.()]/', '', $text))+5;
    if($text_lenght > 34)
        $widt = round(($text_lenght*8)+35);
    else
        $widt = round(($text_lenght*8)+15);

    // echo $text_lenght.'<br/>';
    // echo $text;
    // exit;
    //$widt = round(($text_lenght*8)+15);
    $widt = 360*3; 
    $heit = 23*3;
}
else if(isset($_GET['b']) and !empty($_GET['b']))
{
    $text = DecryptAmount(DATE('ddMMYY'), $_GET['b']);
}
else
{
    $text="Error! ";
}



$text=wordwrap($text, 50, "||", TRUE);
// echo 'Sold: '.$soldprice.'<br/>';
// echo strlen('Sold: '.$soldprice);exit;


//try to create an image
if(isset($_GET['b']))
{
    if(isset($_GET['t']))
    {
        $im = @imagecreate(110, 14) or die("Cannot Initialize new GD image stream");
        $font_size = 12;
        $font = "helveticaneue/Helvetica Neu Bold.ttf";//Helvetica Neu Bold
    }
    else
    {
        $im = @imagecreate(90, 14) or die("Cannot Initialize new GD image stream");
        $font = "helveticaneue/HelveticaNeueMed.ttf";
        $font_size = 8;
    }
    
    $heig = 1;
    
}
else if(isset($_GET['w']))
{
    $im = @imagecreate($widt, $heit) or die("Cannot Initialize new GD image stream");
    $font_size = 40;//12
    $font = "helveticaneue/arialbd.ttf";//Helvetica Neu Bold
    $heig = 0.8;
}
else if(isset($_GET['fq']) && isset($_GET['fq']))
{
    $im = @imagecreate($widt, $heit) or die("Cannot Initialize new GD image stream");
    $font_size = 33;//12
    $font = "helveticaneue/arialbd.ttf";//Helvetica Neu Bold
    $heig = 1.5;
}
else
{
    $im = @imagecreate($widt, $heit)
    or die("Cannot Initialize new GD image stream");
    $heig = 1.5;
    $font_size = 33;//11;
    $font = "helveticaneue/arialbd.ttf";//Helvetica Neu Bold
}


//set the background color of the image
if(isset($_GET['c']))
    $background_color = imagecolorallocate($im, 255, 255, 204);
else
    $background_color = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);

//set the color for the text
if(isset($_GET['v']))
{
    $text_color = imagecolorallocate($im, 118, 162, 200);
    $font = "helveticaneue/Helvetica Neu Bold.ttf";
}
else
    $text_color = imagecolorallocate($im, 0x00, 0x00, 0x00);
//adf the string to the image




$angle = 0;

$splittext = explode ( "||" , $text );
$lines = count($splittext);
$i = 0;
foreach ($splittext as $text) {
    
    // $text_box = imagettfbbox($font_size,$angle,$font,$text);
    // $text_width = abs(max($text_box[2], $text_box[4]));
    // $text_height = abs(max($text_box[5], $text_box[7]));
    

    if($i>0)
    {
        $text_color = imagecolorallocate($im, 153, 153, 153);
        //$font = "helveticaneue/HelveticaNeue Light.ttf";
        //$x = (imagesx($im) - $text_width)/6;
        //$y = ((imagesy($im) + $text_height)/2)-($lines-1)*$text_height;
        $font_size = 33;
        
        if ((isset($_GET['q']) and !empty($_GET['q'])))
            $text = $text;
    }
    else
    {
        //$x = (imagesx($im) - $text_width)/6;
        //$y = ((imagesy($im) + $text_height)/2)-($lines-1.5)*$text_height;
    }
            

    $text_box = imagettfbbox($font_size,$angle,$font,$text);
    $text_width = abs(max($text_box[2], $text_box[4]));
    $text_height = abs(max($text_box[5], $text_box[7]));
    $x = (imagesx($im) - $text_width)/6;
    $y = ((imagesy($im) + $text_height)/2)-($lines-$heig)*$text_height;
    $lines=$lines-1.3;
    $i++;
    // if((isset($_GET['w']) and !empty($_GET['w'])) )
    //     imagettftext($im, $font_size, $angle, 1, $y, $text_color, $font, $text);
    // else
    //     imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font, $text);

    imagettftext($im, $font_size, $angle, 1, $y, $text_color, $font, $text);
}

//setting the image header in order to proper display the image
header('Pragma: public');
header('Cache-control: max-age=31536000');
header('Expires: ' . gmdate("D, d M Y H:i:s", time() + 31536000) . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
header("Content-Type: image/png");
imagepng($im);
imagedestroy($im);
?>