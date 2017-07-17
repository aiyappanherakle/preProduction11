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
//error_reporting(E_ALL);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'auction_details') 
	{       
		if(!empty($ilance->GPC['start_date']))	

		{
				$stmt_date= $ilance->GPC['start_date'];
				
				$formatted_statement_date=date('m/d/Y',strtotime($stmt_date));
				
				$project_list = $ilance->db->query("select * from " . DB_PREFIX . "projects where DATE( date_end ) = '".$stmt_date."' AND status = 'open' group by user_id   ORDER BY COUNT( project_id ) DESC");
					
					if($ilance->db->num_rows($project_list)>0)
					{

					$messagebody .= "*********************************"."\n";
					$messagebody .= "Auction Details -" .$formatted_statement_date ."\n";
					$messagebody .= "*********************************"."\n";	

					while($totallist=$ilance->db->fetch_array($project_list))
					{

					$userid=$totallist['user_id'];
					$username= fetch_user('username',$totallist['user_id']); 
					$email=fetch_user('email',$totallist['user_id']);



					$user_list = $ilance->db->query("select count(*) as list from " . DB_PREFIX . "projects where user_id = '".$totallist['user_id']."' AND date( date_end ) = '".$stmt_date."' AND status = 'open'");

					$xlist = $ilance->db->fetch_array($user_list);    
					$total = $xlist['list'];

					$total_1=$total_1+$total;

					$bids_zero = $ilance->db->query("select sum(currentprice) as crrprice from " . DB_PREFIX . "projects where user_id = '".$totallist['user_id']."' AND date( date_end ) = '".$stmt_date."' AND status = 'open' AND bids = 0 ");

					$ylist = $ilance->db->fetch_array($bids_zero); 
					$zerobids = $ylist['crrprice'];
					$total_2=$total_2+$zerobids;					

					$bids_nunzero = $ilance->db->query("select sum(currentprice) as bidcrrpric from " . DB_PREFIX . "projects where user_id = '".$totallist['user_id']."' AND date( date_end ) = '".$stmt_date."' AND status = 'open' AND bids !=0 ");

					$zlist = $ilance->db->fetch_array($bids_nunzero);
					$nonzerobids=$zlist['bidcrrpric'];
					$total_3=$total_3+$nonzerobids;  

					$ilance->email = construct_dm_object('email', $ilance);
					/*$existing = array(
					'{{username}}' => fetch_user('username',$totallist['user_id']),

					'{{xitems}}' => $xlist['kkk'],
					'{{yitems}}' => $ylist['crrprice'],
					'{{zitems}}' => $zlist['bidcrrpric'],
					);*/



					$messagebody .= "Username :". $username . "\n";
					$messagebody .= "Email :". $email . "\n";
					$messagebody .= "Total No of Item Count :" . $total . "\n";
					$messagebody .= "Total No of Unsold Items :" . ": " . $zerobids . "\n";
					$messagebody .= "Total No of Sold Items :".$nonzerobids."\n";
					$messagebody .= "*********************************"."\n";

					/*$messagebody .=$maxamt."\n";
					$messagebody .= $phrase['_time_left'] . ": " . $ilance->auction->auction_timeleft($res_new['project_id'], '', '', 0, 0, 1) . "\n\n";*/




					}

					$messagebody .= "Total Details"."\n";
					$messagebody .= "Total Item Count :". $total_1 . "\n";
					$messagebody .= "Total Unsold Price :". $total_2 . "\n";
					$messagebody .= "Total Sold Price :". $total_3 . "\n";
					



					$messagebody;
	//	echo $ilconfig['globalserversettings_adminemail'];exit;			

					$ilance->email = construct_dm_object('email', $ilance);
					$ilance->email->logtype = 'Auction Details';
					//$ilance->email->mail = $ilconfig['globalserversettings_adminemail'];
					$ilance->email->mail = 'sukumar@herakle.com';
					$ilance->email->slng = fetch_user_slng($totallist['user_id']);	

					$ilance->email->get('reports_allusers');	
					$ilance->email->set(array(
					'{{username}}' => $username,
					'{{itemcount}}' => $total,
					'{{zerobids}}' => $zerobids,
					'{{nonzerobids}}' => $nonzerobids

					));

					$ilance->email->set(array(
					'{{message}}' => $messagebody,

					));

					$ilance->email->send();

					unset($messagebody);
					}
					print_action_success('Your Auction Details Send successfully', 'Auction_report.php');
					exit();
        }
	} 
	 
	 
	$pprint_array = array('buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
    ($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
	$ilance->template->fetch('main', 'Auction_report.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	

}else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}



/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>