<?php 
define('FPDF_FONTPATH','../font/');

//require('pdftable_1.9/lib/pdftable.inc.php');
require('pdftable_1.9/lib/pdftable1.inc.php');
$p = new PDFTable();
$g=get_html_translation_table(HTML_ENTITIES, ENT_QUOTES | ENT_HTML5);
$r='★';
$s=mb_detect_encoding($r);
$s=iconv("UTF-8", "ASCII//TRANSLIT", $r);
$t='&#9733;';
//$u=iconv("UTF-8", "UTF-8//TRANSLIT", $t);
//$u= html_entity_decode($r,ENT_HTML401);
$u=html_entity_decode($r,ENT_QUOTES,"ISO-8859-1");
$html="";
$html.='<table border="1">
<tr>
<td>'.$r.'
</td>
<td>'.$s.'</td>
</tr>
<tr>
<td>'.$t.'</td>
<td>'.$u.'</td>
</tr>';
//echo $html;
$p->AddPage();
$p->setfont('times','',10);	
$p->htmltable($html);

$p->output('try_'.date('ss').'.pdf','D');


?>