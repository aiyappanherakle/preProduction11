<?php include('functions/config.php');

$sql="SELECT *  FROM " . DB_PREFIX . "invoices WHERE  invoiceid='198312'";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
		echo $line['invoiceid'];
	}
}

$sql="SELECT *  FROM " . DB_PREFIX . "invoices WHERE  invoiceid='198312'";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
		echo $line['invoiceid'];
	}
}

$sql="SELECT *  FROM " . DB_PREFIX . "invoices WHERE  invoiceid='198312'";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
		echo $line['invoiceid'];
	}
}

$sql="SELECT *  FROM " . DB_PREFIX . "invoices WHERE  invoiceid='198312'";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
		echo $line['invoiceid'];
	}
}

$sql="SELECT *  FROM " . DB_PREFIX . "invoices WHERE  invoiceid='198312'";
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
		echo $line['invoiceid'];
	}
}

$ilance->template->fetch('main', 'main.html');
        $ilance->template->pprint('main', $pprint_array);
        exit();
?>
<img src="http://www.greatcollections.com/attachment.php?cmd=thumb&subcmd=itemphoto&id=89ac8b43acb6df3f710cbba5fcd7b3ac&w=268">
<img src="http://www.greatcollections.com/attachment1.php?cmd=thumb&subcmd=itemphoto&id=89ac8b43acb6df3f710cbba5fcd7b3ac&w=268">