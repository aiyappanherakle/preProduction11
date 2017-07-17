<?php 

class add_hotlists_coins
{
	function add_hotlists()
	{
	global $ilance,$ilconfig,$phrase;

		$if_coins_selected_email=0;

		$sqlquery = $ilance->db->query("SELECT p.project_id,p.currentprice,p.currencyid,p.bids
				 						  FROM " . DB_PREFIX . "projects p 
										  left join  " . DB_PREFIX . "users u on  u.user_id=p.user_id and u.status='active'
										  WHERE  p.status = 'open' 
										  AND ((p.currentprice >= '500' AND p.bids > '4') OR (p.currentprice >= '1000' AND p.bids > '2') OR (p.currentprice >= '10000' AND p.bids > '1')) 
										   AND (p.filtered_auctiontype = 'regular' AND p.winner_user_id  = '0') and p.hotlists ='0'");

			if($ilance->db->num_rows($sqlquery) > 0)
			{
				$if_coins_selected_email=1;
				while($hotlists_r=$ilance->db->fetch_array($sqlquery))
				{


				
				$currencyid=$hotlists_r['currencyid'];
				$bids = ($hotlists_r['bids']>0)? $hotlists_r['bids']." ". $phrase['_bids']."\n\n":'';
				$currentbid = "Current Bid: ".$ilance->currency->format($hotlists_r['currentprice'])."";
			    $ilance->db->query("UPDATE  " . DB_PREFIX . "projects
				SET hotlists ='1'	
				WHERE  project_id = '".$hotlists_r['project_id']."'");

				$html.='Item ID: ';	
				$html.=$hotlists_r['project_id'].",";
				$html.=$currentbid.",".$bids;				

				}	

			}
 

		  
			if($if_coins_selected_email==1)
			{
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->logtype = 'Hot List automation';
			$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('adding_to_hot_List');		
			$ilance->email->set(array('{{username}}' => 'admin','{{details}}'=>$html));
			$ilance->email->send();
			
			 // email admin
			$ilance->email->logtype = 'Hot List automation';
			$ilance->email->mail = $ilconfig['globalserversettings_testemail'];    
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('adding_to_hot_List');		
			$ilance->email->set(array('{{username}}' => 'admin','{{details}}'=>$html));
			$ilance->email->send();
			
			
			 // email admin
			$ilance->email->logtype = 'Hot List automation';
			$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];    
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('adding_to_hot_List');		
			$ilance->email->set(array('{{username}}' => 'admin','{{details}}'=>$html));
			$ilance->email->send();
			}
			
		  			
			return 1;
		 
		 
	}
}
?>