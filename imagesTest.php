<?php
$string_text[0] = 'Sold: $237.60';
$string_text[1] = '($216.00 hammer)';
$widt = round(strlen($string_text[0])*7.6)+12;//echo $widt;exit;
$heit = 35;

$im = @imagecreate($widt, $heit)
or die("Cannot Initialize new GD image stream");
$heig = 1.5;
$font_size = 11;
$font = 'helveticaneue/arialbd.ttf';
// $font = "helveticaneue/Helvetica Neu Bold.ttf";

//set the background color of the image
$background_color = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);

//set the color for the text
$text_color = imagecolorallocate($im, 0x00, 0x00, 0x00);

$angle = 0;

$splittext = $string_text;
$lines = count($splittext);
$i = 0;
foreach ($splittext as $text) {

    if($i>0)
    {
        $text_color = imagecolorallocate($im, 153, 153, 153);
        $font_size = 10;
    }
    else
    {

    }
            

    $text_box = imagettfbbox($font_size,$angle,$font,$text);
    $text_width = abs(max($text_box[2], $text_box[4]));
    $text_height = abs(max($text_box[5], $text_box[7]));
    $x = (imagesx($im) - $text_width)/6;
    $y = ((imagesy($im) + $text_height)/2)-($lines-$heig)*$text_height;
    $lines=$lines-1.3;
    $i++;
    imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font, $text);
}

//setting the image header in order to proper display the image
header("Content-Type: image/png");
imagepng($im);
imagedestroy($im);
?>