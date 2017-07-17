<?php 

class enable_batch_bidding
{
	function enable_batch_bidding_users()
	{
	global $ilance,$ilconfig,$phrase;
	
		$cronlog = '';
		$if_enable_selected_email=0;

		$sqlquery = $ilance->db->query("SELECT u.user_id,u.email,count(p.winner_user_id) as auction_win
										FROM " . DB_PREFIX . "projects p 
										LEFT join  " . DB_PREFIX . "users u on  u.user_id=p.user_id and u.status='active'
										WHERE  p.status = 'expired'
										AND (p.filtered_auctiontype = 'regular' AND p.winner_user_id  > 0 ) 
										AND u.enable_batch_bid ='0'
										group by u.user_id
										having count(auction_win) >50");

			if($ilance->db->num_rows($sqlquery) > 0)
			{
				$if_enable_selected_email=1;
				while($enable_batch_bid=$ilance->db->fetch_array($sqlquery))
				{


				$ilance->db->query("UPDATE  " . DB_PREFIX . "users
				SET enable_batch_bid ='1'	
				WHERE  user_id = '".$enable_batch_bid['user_id']."'");

				$html.='User ID: ';
				$html.=$enable_batch_bid['user_id'].",\n";
				$html.='Email: ';
				$html.=$enable_batch_bid['email'].".\n\n";			

				}	

			}
 

		  
			if($if_enable_selected_email==1)
			{
			$ilance->email = construct_dm_object('email', $ilance);
			$ilance->email->logtype = 'Enable Batch Bidding';
			$ilance->email->mail = $ilconfig['globalserversettings_developer_email1'];
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('enable_batch_bidding_users');		
			$ilance->email->set(array('{{username}}' => 'admin','{{details}}'=>$html));
			$ilance->email->send();
			
			 // email admin
			$ilance->email->logtype = 'Enable Batch Bidding';
			$ilance->email->mail = $ilconfig['globalserversettings_testemail'];    
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('enable_batch_bidding_users');		
			$ilance->email->set(array('{{username}}' => 'admin','{{details}}'=>$html));
			//$ilance->email->send();
			
			
			 // email admin
			$ilance->email->logtype = 'Enable Batch Bidding';
			$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];    
			$ilance->email->slng = fetch_site_slng(1);
			$ilance->email->get('enable_batch_bidding_users');		
			$ilance->email->set(array('{{username}}' => 'admin','{{details}}'=>$html));
			//$ilance->email->send();
			}
			
		  	$cronlog .= 'Enabled Batch Bidding Users, ';		
			return $cronlog;
		 
		 
	}
}
?>
