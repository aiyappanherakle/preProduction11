<?php
//listing fee patch
define('LOCATION', 'admin');
require_once('./../functions/config.php');
error_reporting(E_ALL);

$headings[0]='projectid';
$headings[1]='project insertion fee';
$headings[2]='project startprice';
$headings[3]='coin listing fee';
$headings[4]='invoice listing fee';
$headings[5]='project user';
$headings[6]='final if';

$sql="SELECT p.project_id,p.insertionfee,p.startprice,c.listing_fee,i.amount,p.user_id FROM ilance_projects p  
left join ilance_coins c on c.coin_id=p.project_id
left join ilance_invoices i on i.projectid=p.project_id and i.isif=1
WHERE p.status='expired'
and c.listing_fee in (' $42.50 + listing','$100 + listing','$125 + listing','$75 only')";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
		
$sql1="SELECT *  FROM ilance_insertion_fees d where d.groupname='".$line['listing_fee']."' and d.insertion_from<='".$line['startprice']."'  and  (d.insertion_to>='".$line['startprice']."' or d.insertion_to<0)";

		$res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res1)>0)
		{
			while($line1=$ilance->db->fetch_array($res1))
			{
				$line['newamount']= $line1['amount'];
			}
		}
		$data[]=$line;
	}

$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "ifpatch-$timeStamp";
	$action = 'csv';
		if ($action == 'csv')
		{
			header("Pragma: cache");
			header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
			header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
			echo $reportoutput;
			die();
		}
	}
?>