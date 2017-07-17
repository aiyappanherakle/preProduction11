<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

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

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['consigner'] => $ilcrumbs[$ilpage['consigner']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

$navroot = '1';


if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{      
    if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'consigner' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
	{
		$startDate = print_array_to_datetime($ilance->GPC['range_start']);
		$startDate = substr($startDate, 0, -9);
		
		$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
		$endDate = substr($endDate, 0, -9);
	
		
	    $sql =  $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
		WHERE (isfvf ='0' AND isif = '0' AND isportfoliofee = '0' AND isenhancementfee = '0' AND isescrowfee = '0' AND iswithdrawfee = '0' AND isp2bfee = '0')
		AND p2b_user_id != ''
		AND ".$ilance->GPC['searchby']." = '".$ilance->GPC['searchkey']."'
		AND (createdate <= '" . $endDate . "' AND createdate >= '" . $startDate . "')
		");
		if($ilance->db->num_rows($sql) > 0)
		{		   
		   while($res = $ilance->db->fetch_array($sql))
		   {	
		   		 
			 if($res['status'] == 'paid')
			 {
			   $res['status'] = $res['status'];
			 }
			if($res['status'] != 'paid')
			 {
			  $res['status'] = '<a href='.$ilpage['consigner']. '?cmd=consigner&amp;subcmd=update&amp;invoiceid=' . intval($res['invoiceid']) . '&amp;user_id='.intval($res['user_id']).'>' . Unpaid . '</a>';
			 }
			 $res['user_name'] = fetch_user('username',$res['user_id']);
			 $invoicelist[] = $res;
		   }
		}
	
	}
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'consigner' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update')
	{	
	 $selectinvoice = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."invoices
					WHERE invoiceid = '".$ilance->GPC['invoiceid']."'");
	 $result = $ilance->db->fetch_array($selectinvoice);	
	 $pay_invoice_id = $result['invoiceid'];
	 $pay_first_name = fetch_user('first_name',$result['user_id']);
	 $pay_last_name = fetch_user('last_name',$result['user_id']);
	 $pay_username = fetch_user('username',$result['user_id']);
	 $pay_email = fetch_user('email',$result['user_id']);
	 $pay_address = fetch_user('city',$result['user_id']);
	 $pay_phone = fetch_user('phone',$result['user_id']);
	 $pay_amount = $result['amount'];
	 //$payment_pulldown = print_paymethod_pulldown('withdraw', 'account_id', $result['user_id'], $javascript = '');	 
	}
	//csvgenerate
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'consigner' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'paymentpaid')
	{
		$updateinvoice = $ilance->GPC['invoiceid'];
		$ilance->db->query("UPDATE ".DB_PREFIX."invoices 
							SET status = 'paid'
							paiddate = '".DATETIME24H."'
							WHERE invoiceid = '".$updateinvoice."' ");
		print_action_success("Invoice Status Marked As Paid", $ilpage['consigner']);
                        exit();
	}
	
	
	else
	{  
          
	$selectinvoice = $ilance->db->query("
					SELECT * FROM ".DB_PREFIX."invoices 
					WHERE (isfvf ='0' AND isif = '0' AND isportfoliofee = '0' AND isenhancementfee = '0' AND isescrowfee = '0' AND iswithdrawfee = '0' AND isp2bfee = '0')
					AND p2b_user_id != ''
					");
	if($ilance->db->num_rows($selectinvoice) > 0)
	{
	   while($res = $ilance->db->fetch_array($selectinvoice))
	   {
		 	if($res['status'] == 'paid')
			 {
			   $res['status'] = $res['status'];
			 }
			if($res['status'] != 'paid')
			 {
			  $res['status'] = '<a href='.$ilpage['consigner']. '?cmd=consigner&amp;subcmd=update&amp;invoiceid=' . intval($res['invoiceid']) . '&amp;user_id='.intval($res['user_id']).'>' . Unpaid . '</a>';
			 }
		 $res['user_name'] = fetch_user('username',$res['user_id']);
		 $invoicelist[] = $res;
	   }
	}
	}
	
	
	// Date Month Year Start
	
		$daylist = '';
				$monthlist = '';
				$yearlist = '';
				$daylist .='<select name="day" id="day"><option value="">DATE</option>';

						$day = date('d');
						for($i=1; $i<=31; $i++)
		  				if($day == $i)
						$daylist .= "<option value='$i' selected>$i</option>";
						else
						$daylist .= "<option value='$i'>$i</option>";
	
				$daylist .='</select>';
				
				$monthlist .='<select name="month" id="month"><option value="">MONTH</option>';

				$month = date('m');
						for($j=1; $j<=12; $j++)
						
						if($month == $j)
						$monthlist .= "<option value='$j' selected>$j</option>";
						else
						$monthlist .= "<option value='$j'>$j</option>";
						
						
				$monthlist .= '</select>';
				
				$yearlist .= '<select name="year" id="year"><option value="">YEAR</option>';
				
					$year = date('Y');;
					for($k=date("Y"); $k<=date("Y")+5; $k++)
					if($year == $k)
					$yearlist .= "<option value='$k' selected>$k</option>";
					else
					$yearlist .= "<option value='$k'>$k</option>";
				
				$yearlist .='</select>';
				// Date Month Year End
	
	$arreport = $ilance->db->query("
						SELECT user_id FROM " . DB_PREFIX . "invoices
						WHERE (isfvf ='0' OR isif = '0' OR isportfoliofee = '0' OR isenhancementfee = '0' OR isescrowfee = '0' OR iswithdrawfee = '0' OR isp2bfee = '0')
						GROUP BY p2b_user_id 					
						");
	if($ilance->db->num_rows($arreport) > 0)
	{
	
	   while($reres = $ilance->db->fetch_array($arreport))
	   {		 	
		 $arreportamt = $ilance->db->query("
						SELECT sum(amount) as amount FROM " . DB_PREFIX . "invoices
						WHERE (isfvf ='0' OR isif = '0' OR isportfoliofee = '0' OR isenhancementfee = '0' OR isescrowfee = '0' OR iswithdrawfee = '0' OR isp2bfee = '0')
						AND user_id = '".$reres['user_id']."'
						ORDER BY amount					
						");
		 $resamt = $ilance->db->fetch_array($arreportamt);
		 $arreportcount = $ilance->db->query("
						SELECT count(*) AS count FROM " . DB_PREFIX . "invoices
						WHERE (isfvf ='0' OR isif = '0' OR isportfoliofee = '0' OR isenhancementfee = '0' OR isescrowfee = '0' OR iswithdrawfee = '0' OR isp2bfee = '0')
						AND user_id = '".$reres['user_id']."'											
						");
		 $rescount = $ilance->db->fetch_array($arreportcount);
		 
		 $reres['username'] = fetch_user('username',$reres['user_id']);
		 $reres['num_invoice'] = $rescount['count'];
		 $reres['amount'] = $resamt['amount'];
		 $reres['email_notify'] = 'YES';
		
		 $reportlist[] = $reres;
	   }
	}
	
	
	
	$pprint_array = array('daylist','monthlist','yearlist','searchprevnext','hiddenid','hiddendo','pay_first_name','pay_last_name','pay_username','pay_email','pay_address','pay_phone','pay_invoice_id','pay_amount','payment_pulldown','reportfromrange','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'consigner.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('invoicelist','reportlist'));
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
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