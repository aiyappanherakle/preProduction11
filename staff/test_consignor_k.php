<?php
require_once('./../functions/config.php');


if (empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
	echo "login to cont";exit;
}

$current_week_consignor_query = "SELECT case when i.p2b_user_id=0 then i.user_id else i.p2b_user_id end as consignor
								FROM ilance_invoices i
								where (i.p2b_user_id>1 or i.isenhancementfee=1 or i.isif =1 or i.isfvf=1)
								and i.isbuyerfee=0 
								and year(i.createdate)='2014' 
								and week(i.createdate)=7  
								group by consignor ";
$result_current_week_consignor = $ilance->db->query($current_week_consignor_query, 0, null, __FILE__, __LINE__);

if($ilance->db->num_rows($result_current_week_consignor))
{
	while($current_week_user_details=$ilance->db->fetch_array($result_current_week_consignor))
	{
		$current_week_user_details['consignor'];
		$current_week_user_detail[]=$current_week_user_details['consignor'];		
	}
	
}

$except_current_week_consignor_query = "SELECT case when p2b_user_id=0 then user_id else p2b_user_id end as consignor
								FROM ilance_invoices
								where (p2b_user_id>1 or isenhancementfee=1 or isif =1 or isfvf=1) 
								and isbuyerfee=0 
								and year(createdate)='2014' 
								and week(createdate)<7  
								group by consignor ";
$result_except_current_week_consignor = $ilance->db->query($except_current_week_consignor_query, 0, null, __FILE__, __LINE__);

if($ilance->db->num_rows($result_except_current_week_consignor))
{
	while($except_current_week_user_details=$ilance->db->fetch_array($result_except_current_week_consignor))
	{
		$except_current_week_user_details['consignor'];
		$except_current_week_user_detail[]=$except_current_week_user_details['consignor'];
	}
	
}

$result = array_diff($current_week_user_detail, $except_current_week_user_detail);

echo "<pre>";
print_r($result);
exit;

foreach($result as $conclude_user)
{
	$get_user_details_query = "SELECT user_id,first_name,email
	                           FROM ilance_users
							   WHERE user_id = '".$conclude_user."' ";
	$result_get_user_details = $ilance->db->query($get_user_details_query);
	
	if($ilance->db->num_rows($result_get_user_details))
	{
		while($get_user_details =$ilance->db->fetch_array($result_get_user_details))
		{
			$get_user_id = $get_user_details['user_id'];
			$get_user_name = $get_user_details['first_name'];
			$get_user_email = $get_user_details['email'];
		}
	}
	
}

?>