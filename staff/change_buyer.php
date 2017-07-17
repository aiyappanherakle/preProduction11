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
$ilance->change_buyer = construct_object('api.change_buyer');

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{ 

	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'all_pending_invoices')
	{
		$show['showsearch'] = true;
	
        $filterby = (isset($ilance->GPC['filterby']) 
        		AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : 'user_id';
        $filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->db->escape_string($ilance->GPC['filtervalue']) : '';										
		$get_filtervalue =  $ilance->GPC['filtervalue'];

        $filterbyc = (isset($ilance->GPC['filterbyc']) 
        		AND !empty($ilance->GPC['filterbyc'])) ? $ilance->GPC['filterbyc'] : 'user_idc';
        $filtervaluec = (isset($ilance->GPC['filtervaluec']) AND !empty($ilance->GPC['filtervaluec'])) ? $ilance->db->escape_string($ilance->GPC['filtervaluec']) : '';										
		$get_filtervaluec =  $ilance->GPC['filtervaluec'];


        $select_all_payment_pending_invoices = $ilance->change_buyer->get_all_payment_pending_invoice_list($get_filtervalue);

		if($ilance->db->num_rows($select_all_payment_pending_invoices) > 0)
		{	
			while($res_regardlist = $ilance->db->fetch_array($select_all_payment_pending_invoices))
			{
				$res_regardlist['checkval'] = '<input type="checkbox" name="val[]" id="my" 
						value="'.$res_regardlist['projectid'].','.$res_regardlist['invoiceid'].'" onclick="return myself(this.value);" >';     
				echo $res_regardlist['projectid'].',' ;
				$res_regardlist['projectid'] ;
				$res_regardlist['invoiceid'] ; 
				$res_regardlist['p2b_user_id'] ; 
				$res_regardlist['user_id'] ; 
				$res_regardlist['amount'];
				$res_regardlist['totalamount'] ;
				$res_regardlist['taxamount'] ; 	
				$res_regardlist['status']; 	

				$regardlist[] = $res_regardlist;

			}
		}
		else
		{
			$show['no'] = 'list_search';
		}
				$buyer_id = $get_filtervalue;
				$changed_buyer_id = $get_filtervaluec;
				$pprint_array = array('get_filtervalue','buyer_id','get_filtervaluec','changed_buyer_id');		
				$ilance->template->fetch('main', 'change_buyer.html',2);
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_loop('main', array('regardlist'));
				$ilance->template->pprint('main', $pprint_array);
				exit();
	
	}
	else if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'selected_invoices_list')
	{
		$old_buyer_id = $ilance->GPC['old_buyer_id'];
		$changed_buyerid = $ilance->GPC['change_buyer_id'];

	    if($ilance->GPC['val'] == '')
		{
			print_action_failed('sorry at least select one check box', 'change_buyer.php');
			exit();
		}
		else 
		{
			$coin_id=$ilance->GPC['val'];	
			foreach($coin_id as $coins){

				$data = explode(",", $coins);
				$field_coinid = $data[0]; 
				$field_invoiceid = $data[1];
			   	$coins_id[] = $field_coinid;
			   	$invoice_id[] = $field_invoiceid;
			}
			// get coin id
			$selected_coins = implode(', ', $coins_id);

			// get payment pending invoice id
			$selected_payment_invoice = implode(', ', $invoice_id);

			// get buyer fee invoice id 
    		$select_all_buyer_invoices = $ilance->change_buyer->get_all_buyerfee_invoice_list($old_buyer_id,
    			$selected_coins);

			if($ilance->db->num_rows($select_all_buyer_invoices) > 0)
			{	
				while($res_all_buyerfee_invoices = $ilance->db->fetch_array($select_all_buyer_invoices))
				{
					$res_all_buyerfee_invoices = $res_all_buyerfee_invoices['invoiceid'] ;
					$all_buyerfee_invoices[] = $res_all_buyerfee_invoices;
				}
				$selected_buyerfee_invoice = implode(', ', $all_buyerfee_invoices);
			}

			// get invoice projects id
    		$select_all_invoice_projects = $ilance->change_buyer->get_all_invoice_projects($old_buyer_id,
    			$selected_coins);

			if($ilance->db->num_rows($select_all_invoice_projects) > 0)
			{	
				while($res_all_invoice_projects = $ilance->db->fetch_array($select_all_invoice_projects))
				{
					$res_all_invoice_projects = $res_all_invoice_projects['id'] ;
					$all_invoice_projects[] = $res_all_invoice_projects;
				}
				$selected_invoice_projects = implode(', ', $all_invoice_projects);
			}

			// get buy now orders id
    		$select_all_buynow_orders = $ilance->change_buyer->get_all_buynow_order_list($old_buyer_id,
    			$selected_coins,$selected_payment_invoice);

			if($ilance->db->num_rows($select_all_buynow_orders) > 0)
			{	
				while($res_all_buynow_order = $ilance->db->fetch_array($select_all_buynow_orders))
				{
					$res_all_buynow_order = $res_all_buynow_order['orderid'] ;
					$all_buynow_order[] = $res_all_buynow_order;
				}
				$selected_buynow_order = implode(', ', $all_buynow_order);
			}

			// get bids id
    		$select_all_bids = $ilance->change_buyer->get_all_bids_list($old_buyer_id,
    			$selected_coins);

			if($ilance->db->num_rows($select_all_bids) > 0)
			{	
				while($res_all_bids = $ilance->db->fetch_array($select_all_bids))
				{
					$res_all_bids = $res_all_bids['bid_id'] ;
					$all_bids[] = $res_all_bids;
				}
				$selected_bids_id = implode(', ', $all_bids);
			}


			// get proxy bids id
    		$select_all_proxy_bids = $ilance->change_buyer->get_all_proxy_bids_list($old_buyer_id,
    			$selected_coins);

			if($ilance->db->num_rows($select_all_proxy_bids) > 0)
			{	
				while($res_all_proxy_bids = $ilance->db->fetch_array($select_all_proxy_bids))
				{
					$res_all_proxy_bids = $res_all_proxy_bids['id'] ;
					$all_proxy_bids[] = $res_all_proxy_bids;
				}
				$selected_proxy_bids_id = implode(', ', $all_proxy_bids);
			}

			// get escrow id
    		$select_escrow_id = $ilance->change_buyer->get_escorw_list($old_buyer_id,
    			$selected_coins);

			if($ilance->db->num_rows($select_escrow_id) > 0)
			{	
				while($res_escrow_id = $ilance->db->fetch_array($select_escrow_id))
				{
					$res_escrow_id = $res_escrow_id['escrow_id'] ;
					$all_escrow_id[] = $res_escrow_id;
				}
				$selected_escrow_id = implode(', ', $all_escrow_id);
			}


			$ilance->change_buyer->changed_buyer_for_all_tables($changed_buyerid,$selected_coins,
				$selected_payment_invoice, $selected_buyerfee_invoice,$selected_invoice_projects, 
				$selected_buynow_order, $selected_bids_id, $selected_proxy_bids_id,
				$selected_escrow_id);
			
			print_action_success('Buyer Changed successfully', 'change_buyer.php');
			exit();

		}


		exit();
	}
	 
	else
	{

		$form_action='change_buyer.php';
		$pprint_array = array('show','newnumber','html','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','form_action','get_filtervalue'
			,'get_filtervaluec');
					
		($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'change_buyer.html', 2);
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
