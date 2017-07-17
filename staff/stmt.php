<?php


include("mpdf/mpdf.php");
$html='aass<pagebreak orientation="portrait" type="NEXT-ODD" />

asda';
$mpdf=new mPDF(); 

$mpdf->WriteHTML($html);
$mpdf->Output();
exit;
?>