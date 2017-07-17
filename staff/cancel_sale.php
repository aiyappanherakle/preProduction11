<?php
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
	'flashfix',
	'jquery',
	'jquery_custom_ui',
    'modal',
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
require_once('./../functions/config.php');
error_reporting(E_ALL);

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
     
	if(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']="cancel_sale")
	{
		if(isset($ilance->GPC['step']) and $ilance->GPC['step']==1)
		{
			$emails=explode("\n\t",$ilance->GPC['email']);
			foreach($emails as $email)
			{
				$user_id=fetch_user('user_id','','',$email);
				if($user_id>0)
				{
					$cancel_list=get_pending_items($user_id);
					print_r($cancel_list);
				}else
				{
					print_action_failed('System cannot find an email address '.$email, 'cancel_sale.php');
				}
			}
			$pprint_array = array('area_title','page_title','site_name','https_server','http_server','ilanceversion');
			$ilance->template->fetch('main', 'cancel_sale1.html', 2);
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', array('cancel_list')); 
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit;
		}
	}
	$pprint_array = array('area_title','page_title','site_name','https_server','http_server','ilanceversion');
	$ilance->template->fetch('main', 'cancel_sale.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
function get_pending_items($user_id)
{
	global $ilance;
	$query1="SELECT * FROM " . DB_PREFIX . "invoices  where user_id='".$user_id."' and status='unpaid' and combine_project='' ";
	$sql1 = $ilance->db->query($query1, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($sql1))
	{
		while($line1 = $ilance->db->fetch_array($sql1))
		{
		$row['coin_id']				= $line1['projectid'];
		$row['date_of_purchase']	= $line1['createdate'];
		$row['type']				= ($line1['buynowid']>0)?'buynow':'bid';
		$row['hammer']				= $line1['amount'];
		$row['buyerfee']			= $line1['buyerfee'];
		$row['total']				= $line1['amount'] + $line1['buyerfee'];
		$coins[]=$row;
		}
		return $coins;
	}
}
?>