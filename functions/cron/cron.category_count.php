<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright 20002010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

if (!isset($GLOBALS['ilance']->db))
{
    die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

	//require_once('../config.php');



	//update
	$firsttop=$ilance->db->query("UPDATE " . DB_PREFIX . "catalog_toplevel set auction_count='0'");

	$secondlevel=$ilance->db->query("UPDATE " . DB_PREFIX . "catalog_second_level set auction_count='0'");
	//category_update
	$ilance->db->query("update  " . DB_PREFIX . "categories set auctioncount='0'");
	
	$level5=$ilance->db->query("select cid from " . DB_PREFIX . "categories ");
		while($row5=$ilance->db->fetch_array($level5))
		{
		 
			$level6=$ilance->db->query("select count(*) as procount from " . DB_PREFIX . "projects where status='open' and visible=1 and cid='".$row5['cid']."' ");
			while($row6=$ilance->db->fetch_array($level6))
			{
			 	$ilance->db->query("update  " . DB_PREFIX . "categories set auctioncount='".$row6['procount']."' where cid='".$row5['cid']."'");
			 
			}
			
		}
	13
																		
		//update
		$level1=$ilance->db->query("select * from " . DB_PREFIX . "catalog_toplevel");
			while($row=$ilance->db->fetch_array($level1))
			{
		 
				$denomunino=$row['denomination_unique_no'];
				 
				$toplavel_total_sum=0;
				
				   $level2=$ilance->db->query("select * from " . DB_PREFIX . "catalog_second_level where coin_series_denomination_no= '".$row['denomination_unique_no']."'");
					while($row1=$ilance->db->fetch_array($level2))
					{
					 	$coinseriesno=$row1['coin_series_unique_no'];
						$sum1=0;
					   
						$level3=$ilance->db->query("select * from " . DB_PREFIX . "catalog_coin where coin_series_unique_no='".$row1['coin_series_unique_no']."'");
							while($row2=$ilance->db->fetch_array($level3))
							{
							   	$pcgs=$row2['PCGS'];
							   
							   	$eversum=$ilance->db->query("select cid,auctioncount from " . DB_PREFIX . "categories where cid='".$pcgs."' and auctioncount>0 ");
								while($row3=$ilance->db->fetch_array($eversum))
								{
									$eachsum=$row3['auctioncount']; 
								    
									$secondlevel=$ilance->db->query("UPDATE " . DB_PREFIX . "catalog_second_level set auction_count=auction_count+".$eachsum." where coin_series_unique_no= '".$coinseriesno."' ");
								 	$toplevel=$ilance->db->query("UPDATE " . DB_PREFIX . "catalog_toplevel set auction_count=auction_count+".$eachsum."  where denomination_unique_no= '".$denomunino."'");
								}
							 } 
					}
			}

exit;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>