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
	'administration','accounting'
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
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - Returned Consignments';

$navroot = '1';

//error_reporting(E_ALL);


// #### require shipping backend ###############################################
require_once(DIR_CORE . 'functions_shipping.php');
$ilance->subscription = construct_object('api.subscription');
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[invoicepayment]" => $ilcrumbs["$ilpage[invoicepayment]"]);
// #### build our encrypted array for decoding purposes
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();

if (!empty($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{

	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'add-tracking_no' )
	{
		if (isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
		{
			
			if (isset($ilance->GPC['tracking_no']) AND $ilance->GPC['tracking_no'] != '' AND isset($ilance->GPC['shippserviece']) AND $ilance->GPC['shippserviece'] != '' AND count($ilance->GPC['coin_id'])>0 )
			{
				$coinids = $ilance->GPC['coin_id'];
				
				if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']))
				{
				$validdate = intval($ilance->GPC['year']) . '-' . $ilance->GPC['month'] . '-' . $ilance->GPC['day'];
				}
				else
				{
				  $validdate = '0000-00-00';
				}
				
				$email = 'YES';
				foreach($coinids as $key=>$val)
				{
					
					$ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "shippnig_details
                                (coin_id, cust_id,buyer_id, shipper_id, track_no, shipment_date, email,invoice_id,item_id,if_returned)
                                VALUES (
                                '" . $ilance->db->escape_string($val) . "',
								'" . $ilance->db->escape_string($ilance->GPC['uid']) . "',
								'',
                                '" . $ilance->db->escape_string($ilance->GPC['shippserviece']) . "',
								'" . $ilance->db->escape_string($ilance->GPC['tracking_no']) . "',
								'".$ilance->db->escape_string($validdate)."',									
								'" . $ilance->db->escape_string($email) . "',
								'',
								'" . $ilance->db->escape_string($val) . "',
								'1') ");
					
				}
				
				
				
				
				
				$sql_query =  $ilance->db->query("SELECT * FROM " . DB_PREFIX . "shippers where shipperid='".$ilance->GPC['shippserviece']."'");	
				while ($result = $ilance->db->fetch_array($sql_query, DB_ASSOC))
				{				
					$shipping_service_name = $result['title'];
					$shipper_id = $result['shipper_id'];
				}
						
				$html = 'https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1='.$ilance->GPC['tracking_no'].'';
																
				if($shipper_id==35)
				{
				$html = 'https://www.fedex.com/fedextrack/WTRK/index.html?action=track&trackingnumber='.$res['tracking_no'].'&cntry_code=us&fdx=1490 ';
				}
				
				$first_name = fetch_user('first_name',$ilance->GPC['uid']);
				$last_name = fetch_user('last_name',$ilance->GPC['uid']);
				$seller_email= fetch_user('email',$ilance->GPC['uid']);
				$coinids = implode(', ', $coinids);
			
                $existing = array(
									'{{first_name}}'=>$first_name,
									'{{last_name}}'=>$last_name,
									'{{shipping_service_name}}'=>$shipping_service_name,	
									'{{tracking_no}}'=>$ilance->GPC['tracking_no'],
									'{{tracking_link}}'=>$html,
									'{{coins_list}}'=>$coinids,
									'{{emailaddress}}' => $seller_email,									
								);
        
                // #### email admin
                $ilance->email = construct_dm_object('email', $ilance);
				$ilance->email->logtype = 'Returned Consignment Coin Shipping';
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_user_slng(1);
			    $ilance->email->get('inform_shipping_tracking_returned_consign_coins_staff');		
                $ilance->email->set($existing);
                $ilance->email->send();


				
				// // #### Test email developer
                $ilance->email->mail = $ilconfig['globalserversettings_developer_email'];
                $ilance->email->logtype = 'Returned Consignment Coin Shipping';
				$ilance->email->slng = fetch_user_slng(1);
                $ilance->email->get('inform_shipping_tracking_returned_consign_coins_staff');		
                $ilance->email->set($existing);
                $ilance->email->send();

         	
 
				print_action_success("Shipping Details added Successfully", 'users.php?subcmd=_update-customer&id='.$ilance->GPC['uid']);

				
			}
			else
			{
				print_action_failed('Please enter the correct value try again', "users.php?cmd=view&date=".$ilance->GPC['date']."&uid=".$ilance->GPC['uid']."");
			}
		}
		
	}
	
	//pending invoice starts
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'view' AND $ilance->GPC['date'] != '' )
	{
		
		$retnd_uid = isset($ilance->GPC['uid']) ? intval($ilance->GPC['uid']) : 0;
		$retnd_username = fetch_user('username', intval($ilance->GPC['uid']));
		
		
		
		$shipped_by = $track_no = '';
		$retnd_query=$ilance->db->query("SELECT r.return_id,r.user_id,r.coin_id,r.consign_id,r.return_date,r.notes,s.title
		FROM " . DB_PREFIX . "coin_return r
		LEFT JOIN " . DB_PREFIX . "shippers s ON r.shipper_id = s.shipperid
		WHERE r.user_id = '".$retnd_uid."' AND r.return_date = '".$ilance->GPC['date']."' ORDER BY r.coin_id ASC", 0, null, __FILE__, __LINE__) ;
		
		
		if($ilance->db->num_rows($retnd_query) > 0)
		{
		   while($res = $ilance->db->fetch_array($retnd_query))
		   {
				$item_det = array();
				$item_det['coin_id'] = $res['coin_id'];
				$item_det['consign_id'] = $res['consign_id'];
				$item_det['return_date'] = $res['return_date'];
				
				
				$sql_ship = $ilance->db->query(" SELECT s.shipment_date,s.track_no,sh.title  FROM " . DB_PREFIX . "shippnig_details s 
												LEFT JOIN " . DB_PREFIX . "shippers sh ON s.shipper_id = sh.shipperid
												WHERE s.coin_id = '".$res['coin_id']."' AND s.cust_id = '".$res['user_id']."' AND s.track_no != '' AND s.if_returned = '1' ORDER BY s.ship_id ");
				//$number = (int)$ilance->db->num_rows($sql_ship);
				if($ilance->db->num_rows($sql_ship)>0)
				{
					while($ship = $ilance->db->fetch_array($sql_ship))
					{
						$item_det['title'] =  $ship['title'];
						$item_det['shipped_date'] = $ship['shipment_date'];
						$item_det['track_no'] = $ship['track_no'];
						$item_det['yes_no'] = 'Y';
					}
				}
				else
				{
					$item_det['title'] = $item_det['shipped_date'] = $item_det['track_no'] = '-';
					$item_det['yes_no'] = 'N';
				}
				$returned_list[] = $item_det;

		   }
		   $coinss_lists = implode(',', $coinss_list);
		}
		
		
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
		
				
				$shipper .='<select name="shippserviece" id="shippserviece"> 
								<option value=""></option>';
				
				$sql_query=  $ilance->db->query("SELECT *
                								FROM " . DB_PREFIX . "shippers order by visible desc,sort asc");	
					while ($result = $ilance->db->fetch_array($sql_query, DB_ASSOC))
					{								
						$shipper .= '<option value="'.$result['shipperid'].'">'.$result['title'].'</option>';
					}
						
				$shipper .='</select>';
		//echo $track_no.$shipped_by;exit;
		$pprint_array = array('daylist','monthlist','yearlist','track_no', 'shipper','coinss_lists','retnd_uid','retnd_username','buildversion','ilanceversion','login_include_admin','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','cbaddons','page','catid','module','input_style','remote_addr','rid','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
		$ilance->template->fetch('main', 'users_returned.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('returned_list'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
		
	}

	
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

exit;
?>