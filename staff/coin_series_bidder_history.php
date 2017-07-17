<?php

// #### load required phrase groups ############################################
$phrase['groups'] = array(
	'administration',
	
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'search',
	'tabfx',
	'jquery',
	'jquery_custom_ui',
	'modal',
	'yahoo-jar',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');

$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	 	
	$filterby=$whereclass='';
  	if(isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search_list')
   	{
		$filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : '';
		$filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->GPC['filtervalue'] : '';
   	}

  	switch ($filterby)
   	{
	   	case "select_coin_series":
		  	$whereclass=" where c.Coin_Series='".$ilance->db->escape_string($filtervalue)."'";
	   	break;
	   	default:
			$whereclass='';
   		break;
   	}
	
	$query_string='&subcmd=search_list&filterby='.$filterby.'&filtervalue='.$filtervalue;   
	 
    echo "SELECT *
			FROM " . DB_PREFIX . "coins c 
			left join " . DB_PREFIX . "projects p on p.project_id = c.coin_id
			".$whereclass."
			ORDER BY c.coin_id desc";
    //exit;

    $SQL="SELECT *
			FROM " . DB_PREFIX . "coins c 
			left join " . DB_PREFIX . "projects p on p.project_id = c.coin_id
			".$whereclass."
			ORDER BY c.coin_id desc ";

			
	$SQL1="SELECT count(cn.consignid) as count
			FROM " . DB_PREFIX . "consignments cn 
			left join " . DB_PREFIX . "users u on u.user_id=cn.user_id
			".$whereclass."
			ORDER BY cn.consignid desc ";
			
			
	$ilpage['consignments']=HTTP_SERVER.'/staff/consignments.php';		
	$temp=$ilance->db->fetch_array($ilance->db->query($SQL1));
	$number = $temp['count'];
	$ilance->GPC['page'] = isset($ilance->GPC['page'])?$ilance->GPC['page']:1; 
	$counter = ($ilance->GPC['page'] - 1) * 50;
	$scriptpageprevnext = $ilpage['consignments']. '?cmd=listing'.$query_string;
	
		$result=$ilance->db->query($SQL." LIMIT " . $counter . ",50", 0, null, __FILE__, __LINE__);
		$ilance->db->num_rows($result);
		if($ilance->db->num_rows($result))
		{
			while($line=$ilance->db->fetch_array($result))
			{
				$row_list['username'] 	= '<a href="' . $ilpage['users'] . '?subcmd=_update-customer&id=' . $line['user_id'] . '">' . $line['username'] . '</a>';
			    $row_list['firstname']	= $line['first_name'];
				$row_list['lastname'] 	= $line['last_name'];
				$row_list['consignid'] 	= $line['consignid'];
				$row_list['View'] 		= '<a href="consignments_fast.php?cmd=coin_list&user_id='.$line['user_id'].'&consignid='.$line['consignid'].'">View</a>'; 
				$row_list['edit'] 		= '<a href="consignments_fast.php?cmd=consign_edit&user_id='.$line['user_id'].'&consignid='.$line['consignid'].'">Edit</a>'; 
				$row_list['posted']    	= $line['coincount']>0?$line['coincount']:0; 
				$row_list['coins'] 		= $line['coins'];
				$row_list['receive_date']= $line['receive_date'];
				$row_list['total_cost'] = $line['consignment_cost']>0?$line['consignment_cost']:0;
				$total_value			= $line['consignment_cost'];
				$listed_count			= $row_list['posted'];

				$show['gcho_con_rec_date']=true;
				$show['search_list']	= 'search_list';
				
				$row_list['coinorder'] 	= '<span style="cursor:pointer;"  class="blue" onClick="window.open(\'consignments_pdf.php?cmd=pdf&subcmd=coinord&user_id='.$line['user_id'].'&consignid='.$line['consignid'].'&nocoin='.$line['coins'].'&noposted='.$total_value.'&list='.$listed_count.'\',\'mywindow\',\'width=100,height=200\')">PDF</span>
				&nbsp;
				<span style="cursor:pointer;"  class="blue" onClick="window.open(\'consignments_pdf_v.php?cmd=pdf&subcmd=coinord&user_id='.$line['user_id'].'&consignid='.$line['consignid'].'&nocoin='.$line['coins'].'&noposted='.$total_value.'&list='.$listed_count.'&apr=1\',\'mywindow\',\'width=400,height=200\')">APR</span>';
				
				$consignment_listing_search[] = $row_list;		
				
				
			}
		}


	$pprint_array = array('number_search','number','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','listing_pagnation');
        
    ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'coin_series_bidder_history.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('consignment_listing_search'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();

}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
