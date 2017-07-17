<?php
require_once('./../functions/config.php');
$start=285810;
$end=303068;

for($i=$start;$i<=$end;$i++)
{
$query="select * from ilance_invoices where invoiceid='".$i."'";
$result=$ilance->db->query($query);
if($ilance->db->num_rows($result)==0)
{
	echo $i;
	echo '<br>';
}
}
?>