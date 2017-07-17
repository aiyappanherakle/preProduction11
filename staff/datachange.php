<?php 
/* Tamil */
$phrase['groups'] = array(
	'administration'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
    'tabfx',
	'jquery',
	'jquery_custom_ui',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
require_once('./../functions/config.php');
error_reporting(e_all);
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{	


	if((isset($ilance->GPC['submit']) && $ilance->GPC['submit']=='Change') || (isset($ilance->GPC['submit_percent']) && $ilance->GPC['submit_percent']=='Change') ){
		
		$count=0;
		$changed='';
		$changed_only_coin='';
		$not_changed='';
		$donot_exist='';
		
		if((isset($ilance->GPC['submit']) && $ilance->GPC['submit']=='Change')){
		
			$item_arr=explode("\r\n",rtrim($ilance->GPC['coin_list']));
			$item_price_arr=explode("\r\n",rtrim($ilance->GPC['price_list']));	
  
						
			if(count($item_arr) == count($item_price_arr)){
				
				for($i=0;$i< count($item_arr); $i++){
				
					$entire_item_price_arr[$item_arr[$i]] = $item_price_arr[$i];
				}
				
				foreach($entire_item_price_arr as $id=>$bid)
				{
					
				//	echo $id."<br>";
				//	echo $bid."<br>";
					$count++;					
					$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id=$id and status='open'");
					if($ilance->db->num_rows($sqlquery) > 0)
					{	
						
						
						$sqlquery1 = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id=$id and status='open'");
						
				 
						if($ilance->db->num_rows($sqlquery1) > 0)
						{
							//No bids

					 
                                                  
	
							echo "UPDATE  " . DB_PREFIX . "projects SET date_end = CONCAT(DATE(date_end),' ".$bid."')
																								  WHERE  project_id = '".$id."'";
							
							$ilance->db->query("UPDATE  " . DB_PREFIX . "projects SET date_end = CONCAT(DATE(date_end),' ".$bid."')
																								  WHERE  project_id = '".$id."'");
												
							$changed[]=$id;
							
							
						
						}						 
						else
						{
							//With bids
							$not_changed[]=$id;
						}
						
					}
				 	
					

				}
				
			  $result = array_unique($changed);
		      $live_coin = implode(",",$result);
		      $coin_id = $live_coin;
		      $result1 = array_unique($not_changed);
		      $sold_coin = implode(",",$result1);		   
		      $tot_coins=count($itemss);
		
		 	  			 
				print_action_failed("Changed coin: ".$coin_id." <br><br>Not changed coin: ".$sold_coin." ", $_SERVER['PHP_SELF']);exit();
			  



				
			}
		 
			
			
		}
		
 

		
		$changed=empty($changed)?0:implode(",",$changed);
		$changed_only_coin=empty($changed_only_coin)?0:implode(",",$changed_only_coin);
		$not_changed=empty($not_changed)?0:implode(",",$not_changed);
		$donot_exist=empty($donot_exist)?0:implode(",",$donot_exist);
		$html='</br>';
		$html.='<strong>Minimum Bid Change Report</strong>';
		$html.='</br></br>';
		$html.='Total count of items- '.$count;
		$html.='</br></br>';
		$html.='Changed in both projects & coins table - '.$changed;
		$html.='</br></br>';
		$html.='Changed only in coins table - '.$changed_only_coin;
		$html.='</br></br>';
		$html.='Not changed items(i.e)With bids - '.$not_changed;
		$html.='</br></br>';
		$html.='Do not exist - '.$donot_exist;
		$html.='</br>';
		
		$show['change_bid_result'] = 'show';
		
			
		print_action_success($html, 'datachange.php', 'Change Minimum bid for more coins');
		exit();
		
		
	}
	else{
	
		
        
		$show['change_bid_form'] = 'holding';
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
		$ilance->template->fetch('main', 'datachange.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));	
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	$changed=empty($changed)?0:implode(",",$changed);
	$changed_only_coin=empty($changed_only_coin)?0:implode(",",$changed_only_coin);
	$not_changed=empty($not_changed)?0:implode(",",$not_changed);
	$donot_exist=empty($donot_exist)?0:implode(",",$donot_exist);
	$html='</br>';
	$html.='<strong>Minimum Bid Change Report</strong>';
	$html.='</br></br>';
	$html.='Total count of items- '.$count;
	$html.='</br></br>';
	$html.='Changed in both projects & coins table - '.$changed;
	$html.='</br></br>';
	$html.='Changed only in coins table - '.$changed_only_coin;
	$html.='</br></br>';
	$html.='Not changed items(i.e)With bids - '.$not_changed;
	$html.='</br></br>';
	$html.='Do not exist - '.$donot_exist;
	$html.='</br>';
	
	$show['change_bid_result'] = 'show';
	
		
	print_action_success($html, 'datachange.php', 'Change Minimum bid for more coins');
	exit();
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*  Tamil */


?>
