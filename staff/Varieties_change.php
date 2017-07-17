<?php 
/* vijay */
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

if((isset($ilance->GPC['submit']) && $ilance->GPC['submit']=='Change') || (isset($ilance->GPC['change_variety']) && $ilance->GPC['change_variety']=='Change') ){
		

		
		$count=0;
		$changed='';
		$changed_only_coin='';
		$not_changed='';
		$donot_exist='';
		
	
		
	
		if((isset($ilance->GPC['change_variety']) && $ilance->GPC['change_variety']=='Change')){
		
		

			$percent_item_arr=explode("\r\n",rtrim($ilance->GPC['coin_list_percent']));
			$variety= $ilance->GPC['Variety'];
			
			if(count($percent_item_arr > 0)){
				
				foreach($percent_item_arr as $id)
				{

					$count++;
					
					$sqlquery = $ilance->db->query("select * from ".DB_PREFIX."projects where project_id=$id ");
					if($ilance->db->num_rows($sqlquery) > 0)
					{
						if($ilance->db->num_rows($sqlquery) > 0)
						{
							$ilance->db->query("UPDATE  " . DB_PREFIX . "coins SET Variety = ".$variety." WHERE  coin_id = '".$id."'");	
								
								
								$ilance->db->query("UPDATE  " . DB_PREFIX . "projects SET  Variety = ".$variety." WHERE  project_id = '".$id."'");
													
								
														   
							$changed[]=$id;
							
							
						}
					else
						{
							//With bids
							$not_changed[]=$id;
						}
					}
					else
					{
						$sqlquery2 = $ilance->db->query("select Variety,coin_id
                                                 from ".DB_PREFIX."coins where coin_id=$id");
						if($ilance->db->num_rows($sqlquery2) > 0)
							{
							
							$res_coin=$ilance->db->fetch_array($sqlquery2);
							if($res_coin > 0)
                             {
								$ilance->db->query("UPDATE  " . DB_PREFIX . "coins SET Variety = ".$variety." WHERE  coin_id = '".$id."'");	
							
							
							}
							
							$changed_only_coin[]=$id;
							}
						else
						{			
							$donot_exist[]=$id;
							
						}
						
					}	
					

				}			
				
			}
			else{
				
				print_action_failed('Please give valid input details','Varieties_change.php','Retry');
				exit();  
			
			}
			
			
		}

		
		$changed=empty($changed)?0:implode(",",$changed);
		$changed_only_coin=empty($changed_only_coin)?0:implode(",",$changed_only_coin);
		$not_changed=empty($not_changed)?0:implode(",",$not_changed);
		$donot_exist=empty($donot_exist)?0:implode(",",$donot_exist);
		$html='</br>';
		$html.='<strong>Varieties Change Report</strong>';
		$html.='</br></br>';
		$html.='Total count of items- '.$count;
		$html.='</br></br>';
		$html.='Changed in both projects & coins table - '.$changed;
		$html.='</br></br>';
		$html.='Changed only in coins table - '.$changed_only_coin;
		$html.='</br></br>';
		$html.='Not changed items - '.$not_changed;
		$html.='</br></br>';
		$html.='Do not exist - '.$donot_exist;
		$html.='</br>';
		
		$show['change_bid_result'] = 'show';
		
			
		print_action_success($html, 'Varieties_change.php', 'Change Variety flag for more coins');
		exit();
		
		
	}
	else{
	
		
        
		$show['change_bid_form'] = 'holding';
		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
	
		$ilance->template->fetch('main', 'Varieties_change.html', 2);
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
	$html.='<strong>Varieties Change Report</strong>';
	$html.='</br></br>';
	$html.='Total count of items- '.$count;
	$html.='</br></br>';
	$html.='Changed in both projects & coins table - '.$changed;
	$html.='</br></br>';
	$html.='Changed only in coins table - '.$changed_only_coin;
	$html.='</br></br>';
	$html.='Not changed items- '.$not_changed;
	$html.='</br></br>';
	$html.='Do not exist - '.$donot_exist;
	$html.='</br>';
	
	$show['change_bid_result'] = 'show';
	
		
	print_action_success($html, 'Varieties_change.php', 'Change Variety flag for more coins');
	exit();
}
 else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*  Tamil */


?>