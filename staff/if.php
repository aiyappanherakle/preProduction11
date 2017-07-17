<?php 
require_once('./../functions/config.php');
if (empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
echo "login to cont";exit;
}
error_reporting(E_ALL);
	$query=$ilance->db->query("select * from ".DB_PREFIX."consignments where listing_fee!='free_listings' order by consignid desc");
	if($ilance->db->num_rows($query)>0)
	{
		while($line=$ilance->db->fetch_array($query))
		{
		$query_sql="select p.project_id,p.date_end from ".DB_PREFIX."coins c, ".DB_PREFIX."projects p  where c.consignid='".$line['consignid']."' and p.project_id=c.coin_id and p.status='expired' and p.haswinner = 0";
			$query1=$ilance->db->query($query_sql);
			if($ilance->db->num_rows($query1)>0)
			{
				while($line1=$ilance->db->fetch_array($query1))
				{
				 
				 	$query2=$ilance->db->query("select invoiceid from ".DB_PREFIX."invoices where projectid='".$line1['project_id']."' and isif=1");
					if($ilance->db->num_rows($query2)>0)
					{
						while($line2=$ilance->db->fetch_array($query2))
						{
							$query3=$ilance->db->query("select invoiceid from ".DB_PREFIX."invoices where projectid='".$line1['project_id']."'");
							if($ilance->db->num_rows($query3)==1)
							{
								while($line3=$ilance->db->fetch_array($query3))
								{
								echo $line3['invoiceid'].','.$line1['date_end'];
								echo '<br>';
								}
							}
						
						}
					}
				
				}
			}else
			{
			
			}
		}
	}

?>