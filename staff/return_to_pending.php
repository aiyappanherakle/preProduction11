<?php
// #### load required phrase groups ############################################
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

// #### require backend ########################################################
require_once('./../functions/config.php');
$ilance->return_to_pending = construct_object('api.return_to_pending');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{ 

	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'coin_details')
	{
		$show['showsearch'] = true;
	
        $filterby = (isset($ilance->GPC['filterby']) 
        		AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : 'coin_id';
        $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->db->escape_string($ilance->GPC['filtervalue']) : '';										
		$get_filtervalue =  $ilance->GPC['filtervalue'];


        $get_coin_details = $ilance->return_to_pending->get_coin_info($get_filtervalue);

		if($ilance->db->num_rows($get_coin_details) > 0)
		{	
			while($res_regardlist = $ilance->db->fetch_array($get_coin_details))
			{
				$res_regardlist['checkval'] = '<input type="checkbox" name="val[]" id="my" 
						value="'.$res_regardlist['coin_id'].','.$res_regardlist['user_id'].','.$res_regardlist['consign_id'].','.$res_regardlist['invoiceid'].'" onclick="return myself(this.value);" >';     

				$res_regardlist['coin_id'] ;
				$res_regardlist['user_id'] ; 
				$res_regardlist['consign_id'] ; 

				$regardlist[] = $res_regardlist;

			}
		}
		else
		{
			$show['no'] = 'list_search';
		}
				$pprint_array = array('get_filtervalue');		
				$ilance->template->fetch('main', 'return_to_pending.html',2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('regardlist'));
				$ilance->template->pprint('main', $pprint_array);
				exit();
	
	}
	else if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'selected_return_coin')
	{

	    if($ilance->GPC['val'] == '')
		{
			print_action_failed('sorry at least select one check box', 'return_to_pending.php');
			exit();
		}
		else 
		{
			$coin_id=$ilance->GPC['val'];
			foreach($coin_id as $coins){

				$data = explode(",", $coins);
				$field_coinid = $data[0]; 
				$field_sellerid = $data[1];
				$field_consignid = $data[2];
				$field_invoiceid = $data[3];

			   	$coins_id[] = $field_coinid;
			   	$seller_id[] = $field_sellerid;
			   	$consign_id[] = $field_consignid;
			   	$invoice_id[] = $field_invoiceid;
			   	
			   	

			}

			// get coin id
			$selected_coins = implode(', ', $coins_id);

			// get consign id
			$selected_consign_id = implode(', ', $consign_id);

			// get returned invoice id
			$selected_returned_invoice = implode(', ', $invoice_id);

			// get seller id
			$selected_seller_id = implode(', ', $seller_id);



			/*echo $selected_coins.'</br>';
			echo $selected_consign_id.'</br>';
			echo $selected_returned_invoice.'</br>';
			echo $selected_seller_id.'</br>';
			 */

			$ilance->return_to_pending->putback_pending_for_returned_coins($selected_coins,$selected_consign_id,
				$selected_returned_invoice, $selected_seller_id);
			
			print_action_success('Returned coins to put back pending Updated', 'return_to_pending.php');
			exit();

		}


		exit();
	}	 
	else
	{
		$form_action='return_to_pending.php';
		$pprint_array = array('show','newnumber','html','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action','get_filtervalue');
					
		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'return_to_pending.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));		
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);

	} 

}

else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();

}

?>	
