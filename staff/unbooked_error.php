<?php
define('LOCATION', 'admin');

require_once('./../functions/config.php');
error_reporting(E_ALL);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
$on=isset($ilance->GPC['on'])?$ilance->GPC['on']:'2013-01-31';
   $sql="SELECT *  FROM " . DB_PREFIX . "buynow_orders where date(orderdate)<='".$on."' and date(orderdate)>DATE_SUB( '".$on."', INTERVAL 30 DAY) group by project_id";
   '<br>';
$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
if($ilance->db->num_rows($res)>0)
{
	while($line=$ilance->db->fetch_array($res))
	{
	//SELECT max(actual_end_date)  FROM `ilance_coin_relist` WHERE `coin_id` = 40530 and actual_end_date<'2013-01-31'
		  $sql1="SELECT coin_id,min(enddate) as enddate, min(actual_end_date) as actual_enddate FROM " . DB_PREFIX . "coin_relist where coin_id='".$line['project_id']."' and date(enddate)>'".$on."'";
		//and actual_end_date<'".$line['orderdate']."' 
		  '<br>';
		$res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($res1)>0)
		{
			while($line1=$ilance->db->fetch_array($res1))
			{
			
			if($line1['coin_id']<1)
			{
				//coin table
				$sql3="SELECT *  FROM " . DB_PREFIX . "coins WHERE  coin_id='".$line['project_id']."'";
				$res3 = $ilance->db->query($sql3, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($res3)>0)
				{
					while($line3=$ilance->db->fetch_array($res3))
					{
						 $line1['actual_enddate']=$line3['End_Date'];
						 $line1['coin_id']=$line['project_id'];
						 $line1['enddate']=$line3['End_Date'];	
					}
				}
			}
			
				  $sql2="SELECT *  FROM " . DB_PREFIX . "buynow_orders WHERE  project_id='".$line1['coin_id']."' and date(orderdate)<='".$on."' and date(orderdate)>'".$line1['actual_enddate']."'";
				  '<br>';
				$res2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
				if($ilance->db->num_rows($res2)>0)
				{
					while($line2=$ilance->db->fetch_array($res2))
					{					
	 echo  $line2['project_id'].','.$line2['buyer_id'].','.$line2['amount'].','.$line2['qty'].','.$line2['orderdate'].','.$line1['enddate'];
			echo			  '<br>';
						//exit;
						
					}
				}
				 
			}
		}
	}
}
}
else
{
  'login';
}
?>
