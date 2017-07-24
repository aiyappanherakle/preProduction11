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

include("./functions/config.php");
$ilance->encrypt = construct_object( 'api.encrypt' );

if ((isset($_GET['aaa']) and !empty($_GET['aaa'])))
{
    $text = $_GET['aaa'];
    $width = (strlen($ilance->encrypt->DecryptText($text))*9)*3;
    $height = 11*4+20;
    $heig = 0.92*1.333333;
    $font_size = 40;
    $align="right";
    $font = "Helvetica.ttf";
}
if ((isset($_GET['aa']) and !empty($_GET['aa'])))
{
    $text = $_GET['aa'];
    $width = (strlen($ilance->encrypt->DecryptText($text))*9)*3;
    $height = 11*3;
    $heig = 0.92;
    $font_size = 30;
    $align="right";
    $font = "Helvetica.ttf";
}
else if ((isset($_GET['q']) and !empty($_GET['q'])))
{
    $text = $_GET['q'];
    $width = 540;
    $height = 99;
    $heig = 1.5;
    $font_size = 33;
    $font = "arialbd.ttf";
}
else if ((isset($_GET['fq']) and !empty($_GET['fq'])))
{
    $text = $_GET['fq'];
    $width = 456;
    $height = 99;
    $font_size = 33;
    $font = "arialbd.ttf";
    $heig = 1.5;
}
else if((isset($_GET['w']) and !empty($_GET['w'])) )
{
    $text = $_GET['w'];
    $width = 1080; 
    $height = 69;
    $font_size = 40;
    $font = "arialbd.ttf";
    $heig = 0.8;
}
else if(isset($_GET['b']) and !empty($_GET['b']))
{
    $text = $_GET['b'];
    if(isset($_GET['t']))
    {
        $width=110;
        $height=14;
        $font_size = 12;
        $font = "Helvetica Neu Bold.ttf";
    }
    else
    {
        $width=90;
        $height=14;
        $font = "HelveticaNeueMed.ttf";
        $font_size = 8;
    }
    $heig = 1;

}
else
{
    
    $heig = 1.5;
    $font_size = 33;
    $font = "arialbd.ttf";
}

$ilance->encrypt->text = $text;//text will be split to two lines is it includes double pipe ||
$ilance->encrypt->width = $width;//text width generally 3 time the image size that appers in the page
$ilance->encrypt->height = $height;//same as width
$ilance->encrypt->heig = $heig;//top padding for thetext in the image
$ilance->encrypt->font_size = $font_size;
$ilance->encrypt->font = $font;//name of the font
$ilance->encrypt->align = isset($align)?$align:"left";//text align
$ilance->encrypt->font_dir='../helveticaneue/';//if the font group changes or put in some other folder

$ilance->encrypt->print_image();

?>
