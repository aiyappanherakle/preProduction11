<?php
require_once('./../functions/config.php');
error_reporting(E_ALL);
echo $row = 1;
if (($handle = fopen(DIR_SERVER_ROOT."staff/given.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        //echo "<p> $num fields in line $row: <br /></p>\n";
        $row++;
		$invoice=$data[0];
		$date=$data[1];
		
		$sql="SELECT *  FROM " . DB_PREFIX . "invoices WHERE  invoiceid='".$invoice."' and date(scheduled_date)='".$date."'";

		$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res)==0)
		{
		echo 	$invoice;
		echo '<br>';
		}


    }
    fclose($handle);
}else
{
echo "fnof";
}
exit;

/*
$r=array(294025,297423,298191,298372,302008,302649,302655,302696,302701,302008,302696,305998,297423,298372,302655,302701,307105,306158,307194,305998,306158,307105,302649,307194,308737,311427,298191,311504,311523,311587,311523,311427,311587,312252,294025,308737,311504,312252);

foreach($p as $invoice=>$date)
{



}

*/
?>
