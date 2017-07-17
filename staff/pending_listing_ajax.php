<?php

define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
$ilance->GPC['pending_autobuild_user']=trim($ilance->GPC['pending_autobuild_user'], ",");
	if(isset($_GET['pending_autobuild_con_new']))
	{
		switch($ilance->GPC['pending_autobuild_con_new'])
		{
		case 'autobuild_all':
		$SQL="	SELECT *
					FROM " . DB_PREFIX . "coins
					WHERE user_id in(".$ilance->GPC['pending_autobuild_user'].")
					AND coin_listed = 'c' AND (End_Date = '0000-00-00' OR pending = '1') AND project_id  = '0' AND status = '0'
					group by user_id,Title,pcgs,Grade,nocoin order by pcgs desc,Grade desc;
					";

		break;
		case 'autobuild_imaged':
		$SQL="	SELECT c.user_id,c.coin_id 
					FROM " . DB_PREFIX . "coins c
					join " . DB_PREFIX . "attachment a on a.project_id=c.coin_id
					WHERE c.user_id in(".$ilance->GPC['pending_autobuild_user'].")
					AND c.coin_listed = 'c' AND (c.End_Date = '0000-00-00' OR c.pending = '1') AND c.project_id  = '0' AND c.status = '0'
					group by c.user_id,c.Title,c.pcgs,c.Grade,c.nocoin order by c.pcgs desc,c.Grade desc;
					";
		break;
		case 'autobuild_buynow':
		$SQL="	SELECT *
					FROM " . DB_PREFIX . "coins c
					WHERE c.user_id in(".$ilance->GPC['pending_autobuild_user'].")
					AND c.coin_listed = 'c' AND (c.End_Date = '0000-00-00' OR c.pending = '1') AND c.project_id  = '0' AND c.status = '0'
					AND c.Buy_it_now!='' AND c.Minimum_bid ='' 
					group by c.user_id,c.Title,c.pcgs,c.Grade,c.nocoin order by c.pcgs desc,c.Grade desc;
					";
		break;

		}
		$con_listing_co = $ilance->db->query($SQL);
		if($ilance->db->num_rows($con_listing_co) > 0)
		{
		
			while($row_list = $ilance->db->fetch_array($con_listing_co))
			{
				$row[$row_list['user_id']][] = $row_list['coin_id'];
			}
			echo json_encode($row);
		}
		else
		{
			echo '';
		}

	}

}
?>