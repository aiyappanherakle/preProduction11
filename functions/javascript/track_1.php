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
        'buying',
        'selling',
        'rfp',
        'search',
        'feedback',
        'accounting',
        'javascript'
);

// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'countries',
        'tabfx',
	'flashfix',
	'jquery'
);

// #### setup script location ##################################################
define('LOCATION', 'buying');

// #### require backend ########################################################
require_once('./functions/config.php');
$show['widescreen'] = true;
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[track]" => $ilcrumbs["$ilpage[track]"]);

$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();

$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
$ilance->GPC['subcmd'] = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';

if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['track'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}
else
{
		$area_title = 'Track Shipments';
		$page_title = SITE_NAME . ' - ' . 'Track Shipments';
		
		$show['no_statement'] = false;
		
		/*$sql_wonship = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "projects p,
				" . DB_PREFIX . "project_bids b,
				" . DB_PREFIX . "buynow_orders bo
                WHERE p.status != 'open'
				AND p.winner_user_id = '".$_SESSION['ilancedata']['user']['userid']."' OR bo.buyer_id = '".$_SESSION['ilancedata']['user']['userid']."'				
				AND p.project_id = b.project_id OR p.project_id = bo.project_id				
				ORDER BY p.id ASC                 
                ");*/
			$sql_wonship = $ilance->db->query("
                SELECT *,count(*) as tot
                FROM " . DB_PREFIX . "shippnig_details 				
                WHERE buyer_id = '".$_SESSION['ilancedata']['user']['userid']."'
				AND track_no !=''	
				group by track_no						
				ORDER BY ship_id ASC                 
                ");
		
				if ($ilance->db->num_rows($sql_wonship) > 0)
				{
				$show['statement'] = true;
				while ($res = $ilance->db->fetch_array($sql_wonship))
					{
					
					$sql_inv = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
                                               WHERE projectid = '".$res['item_id']."'
                                               AND user_id ='".$_SESSION['ilancedata']['user']['userid']."'
											   AND (invoicetype = 'p2b' OR invoicetype = 'buynow' OR invoicetype = 'escrow')
                                               
                       ");
					  
					   if($ilance->db->num_rows($sql_inv) > 0)
					   {
					    	$fetch_inv=$ilance->db->fetch_array($sql_inv);
							/*$res['invoiceid'] = '<span class="blue"><a href="invoicepayment.php?cmd=view&amp;txn='.$fetch_inv['transactionid'].'">'.$fetch_inv['invoiceid'].'</a></span>';*/
							
							 $final=$ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoice_projects
                                               WHERE invoice_id = '".$fetch_inv['invoiceid']."'
                                                         
                     ");
					 //feb 7
					 $fetch_inv1=$ilance->db->fetch_array($final);
					 
					 
					 $sql_inv2 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "invoices
                                               WHERE invoiceid='".$fetch_inv1['final_invoice_id']."'
                                               
                       ");
					   
					    $fetch_inv2=$ilance->db->fetch_array($sql_inv2);
					 
					 $res['invoiceid'] = '<span class="blue"><a href="buyer_invoice.php?cmd=view&amp;txn='.$fetch_inv2['transactionid'].'">'.$fetch_inv2['invoiceid'].'</a></span>';
					 
							//feb 7
							
							/*$testdate = explode(' ',$fetch_inv['createdate']);
							$testdate1 = explode(' ',$fetch_inv['paiddate']);*/
							$res['createdate'] = date('m-d-Y',strtotime($fetch_inv['createdate']));
							$res['paiddate'] = date('m-d-Y',strtotime($fetch_inv['paiddate']));
					   }
					   else
					   {
					    	$res['invoiceid'] = 'Offline Payment';
							$res['createdate'] = 'Offline Payment';
							$res['paiddate'] = 'Offline Payment';
					   } 	  
					
					/*$res['coincount'] = '1';*/
					$res['coincount'] = $res['tot'];
					$res['shipdate'] = date('m-d-Y',strtotime($res['shipment_date']));
					$res['shipservice'] = fetch_shipper('title',$res['shipper_id']);
					$res['trackno'] = $res['track_no'];
					
					$purchase_ship[] = $res;
					}
				}
				else
				{				
					$show['no_statement'] = true;
				}
				
				$sql_consign = $ilance->db->query("
                SELECT consignid,date(receive_date) AS end_date,coins
                FROM " . DB_PREFIX . "consignments 				
                WHERE user_id = '".$_SESSION['ilancedata']['user']['userid']."'										
				ORDER BY consignid ASC                 
                ");
				if ($ilance->db->num_rows($sql_consign) > 0)
				{
					while ($res_consign = $ilance->db->fetch_array($sql_consign))
					{
					  $res_consign['end_date'] = date('m-d-Y',strtotime($res_consign['end_date']));
					  $consign_receive[] = $res_consign;
					}
				}
// Iwon End

		$hv1='class=""';
		$hv2='class="on"';
		$hV3='class=""';
	
	$pprint_array = array('hv1','hv2','hv3','php_self2','sub','bidsub','servicetabs','producttabs','activebids','awardedbids','archivedbids','invitedbids','expiredbids','retractedbids','productescrow','buynowproductescrow','activerfps','draftrfps','archivedrfps','delistedrfps','pendingrfps','serviceescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpescrow','rfpvisible','countdelisted','prevnext','prevnext2','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
	$ilance->template->fetch('main', 'main_track_1.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('purchase_ship','consign_receive','item_return'));	
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}


/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>