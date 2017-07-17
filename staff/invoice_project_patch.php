<?php
define('LOCATION', 'admin');

require_once('./../functions/config.php');
error_reporting(E_ALL);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	$ilance->db->query("TRUNCATE TABLE  `ilance_invoice_projects_unique`");
        $ilance->db->query("insert into `ilance_invoice_projects_unique` (SELECT * FROM  `ilance_invoice_projects` )");
       /* $ilance->db->query("ALTER TABLE  `ilance_invoice_projects_unique` CHANGE  `id`  `id` INT( 10 ) UNSIGNED NOT NULL");
        $ilance->db->query("ALTER TABLE ilance_invoice_projects_unique DROP PRIMARY KEY");
        $ilance->db->query("ALTER TABLE ilance_invoice_projects_unique DROP INDEX invoice_id");
    */
        $sql="SELECT max(id) as pk,invoice_id FROM `ilance_invoice_projects_unique` group by invoice_id having count(invoice_id)>1
ORDER BY id  DESC";
	$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($res)>0)
	{
		while($line=$ilance->db->fetch_array($res))
		{
		$sql1="SELECT *  FROM ilance_invoice_projects_unique WHERE  invoice_id='".$line['invoice_id']."' and id<'".$line['pk']."'";
		'<br>';
		$res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res1)>0)
		{
			while($line1=$ilance->db->fetch_array($res1))
			{
				echo $q="delete from ilance_invoice_projects_unique where id='".$line1['id']."'"; 
                                $ilance->db->query($q);
				echo '<br>';
			}
		}
			
		}
	}

}else
{
echo 'login';
}

?>