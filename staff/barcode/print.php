<?php
require_once('../../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
$ilance->GPC['id']=isset($ilance->GPC['id'])?$ilance->GPC['id']:415028;
	echo "<img src='https://www.greatcollections.com/staff/barcode/barcode.php?text=inv".$ilance->GPC['id']."' border=0><br>";
	
		$sql="SELECT project_id  FROM ilance_invoice_projects WHERE final_invoice_id = '".$ilance->GPC['id']."'";
		$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res)>0)
		{
			while($line=$ilance->db->fetch_array($res))
			{
				echo "<img src='https://www.greatcollections.com/staff/barcode/barcode.php?text=item".$line['project_id']."' border=0 style=\"padding: 3px;\"><br>";
			}
		}
		echo '<BR>';echo '<BR>';echo '<BR>';echo '<BR>';
	echo "<img src='https://www.greatcollections.com/staff/barcode/barcode.php?text=removenext' border=0 style=\"padding: 3px;\"><br>";
	echo "<img src='https://www.greatcollections.com/staff/barcode/barcode.php?text=removelast' border=0 style=\"padding: 3px;\"><br>";
	echo "<img src='https://www.greatcollections.com/staff/barcode/barcode.php?text=reset' border=0 style=\"padding: 3px;\"><br>";
}else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
?>