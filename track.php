<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L
|| # -------------------------------------------------------------------- # ||
|| # Copyright 20002010 ILance Inc. All Rights Reserved.                # ||
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
error_reporting(0);
// #### require backend ########################################################
require_once('./functions/config.php');
$show['widescreen'] = true;
$inline_script='';
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
elseif(isset($ilance->GPC['cmd']) and $ilance->GPC['cmd']=='view_detail' and $ilance->GPC['consignment']>0)
{
		$nav_url=$ilconfig['globalauctionsettings_seourls']?HTTP_SERVER .'Consignmentsreceived':'track.php?cmd=received';
		$navcrumb[$nav_url] = "Tracking Shipments";
		$nav_url=$ilconfig['globalauctionsettings_seourls']?HTTP_SERVER .'Consignmentsreceived/?cmd=view_detail&consignment='.$ilance->GPC['consignment'].'':'track.php?cmd=view_detail&consignment='.$ilance->GPC['consignment'].'';
		$navcrumb[$nav_url] = "Consignment Details";
		$area_title = 'Consignment Details';
		$page_title = SITE_NAME . ' - ' . 'Consignment Details';
		
		$sql="SELECT consignid FROM " . DB_PREFIX . "consignments WHERE consignid = '".$ilance->db->escape_string($ilance->GPC['consignment'])."'  and user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'";
		$result = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($result)>0)
		{
			$sql2="select * from (SELECT c.coin_id,c.Title,c.status,p.project_id,
						(case 
							when c.Buy_it_now>0 and p.filtered_auctiontype='fixed'
							then
								(case
									when p.buynow_qty=0 and p.buynow_purchases>0
									then
										'Buynow Sold'
									else
										(case
											when p.buynow_qty>0 and p.buynow_purchases>0
											then
												'Partially Sold'
											else
												(case 
														when p.date_end>'".DATETIME24H."' 
															THEN 
																'Buynow Currently Selling' 
															else 
																'Buynow Unsold' 
												end)
										end)	
								end) 
							else
							(case 
								when p.project_id is not null 
									then 
										(case 
											when p.haswinner=1 
												THEN 
													'Sold' 
												else 
													(case 
														when p.date_end>'".DATETIME24H."' 
															THEN 
																'Currently Selling' 
															else 
																'Unsold' 
													end)
										end) 
									else 
										'Item Pending To List' 
							end)
						end) as coin_status

			    		FROM " . DB_PREFIX . "coins c
						left join  " . DB_PREFIX . "projects p on p.project_id=c.coin_id
						WHERE c.consignid = '".$ilance->db->escape_string($ilance->GPC['consignment'])."' and c.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
						union
						SELECT r.coin_id, r.Title, r.status, r.project_id,  'Returned Consignor' AS coin_status
			    		FROM " . DB_PREFIX . "coins_retruned r
			    		where r.consignid = '".$ilance->db->escape_string($ilance->GPC['consignment'])."' and r.user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'
						)o order by o.coin_id";
			$result2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($result2)>0)
			{
				while($line2= $ilance->db->fetch_array($result2))
				{
					$data['coin_id']=$line2['coin_id'];
					$data['Title']=($ilconfig['globalauctionsettings_seourls'])
								? construct_seo_url('productauction', 0, $line2['coin_id'], htmlspecialchars_uni($line2['Title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0)
								: '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $line2['coin_id'] . '">' . htmlspecialchars_uni($line2['Title']) . '</a>';
					$data['Status']=$line2['coin_status'];
					/*
					Currently Selling
					Sold
					Unsold
					Item Pending To List
					Returned to Consignor
					*/
					
					$coins_list[] = $data;
				}
			}
		}else
		{
			print_notice("Consignment not found", "Consignment not found", $ilpage['main'] . '?cmd=cp', $phrase['_my_cp']);
    		exit;
		}

		$show['show_message']=false;
		$sql2="SELECT *,date_format(created_on,'%D %b %Y %h:%i %p')as created_on FROM " . DB_PREFIX . "consignment_messages WHERE visible =1 and consignment_id='".$ilance->db->escape_string($ilance->GPC['consignment'])."' order by created_on";
		$result2 = $ilance->db->query($sql2, 0, null, __FILE__, __LINE__);
		if($ilance->db->num_rows($result2)>0)
		{
			while($line2= $ilance->db->fetch_array($result2))
			{
				$data1['created_on']=$line2['created_on'];
				$data1['message']=$line2['message'];
				
				$message_list[]=$data1;

				$show['show_message']=true;
			}
		}
		$consignid=$ilance->db->escape_string($ilance->GPC['consignment']);
		$pprint_array = array('consignid','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		$ilance->template->fetch('main', 'track_consignment_detail.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('coins_list','message_list'));	
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
}
else
{
		$nav_url=$ilconfig['globalauctionsettings_seourls']?HTTP_SERVER .'Consignmentsreceived':'track.php?cmd=received';
		$navcrumb[$nav_url] = "Tracking Shipments";
		$area_title = 'Track Shipments';
		$page_title = SITE_NAME . ' - ' . 'Track Shipments';
		
		$show['no_statement'] = false;
		  $sql="SELECT s.shipper_id,s.shipment_date,s.shipper_id,s.track_no,s.final_invoice_id,i.invoiceid,i.createdate,i.paiddate,j.transactionid,count(*) as tot
                FROM " . DB_PREFIX . "shippnig_details s
				left join  " . DB_PREFIX . "invoices i on i.invoiceid=s.invoice_id
				left join  " . DB_PREFIX . "invoices j on j.invoiceid=s.final_invoice_id
                WHERE s.buyer_id = '".$_SESSION['ilancedata']['user']['userid']."' AND s.track_no !='' and s.coin_id>0 group by s.final_invoice_id order by i.createdate desc";
			$sql_wonship = $ilance->db->query($sql);
		
				if ($ilance->db->num_rows($sql_wonship) > 0)
				{
				$show['statement'] = true;
				while ($res = $ilance->db->fetch_array($sql_wonship))
					{
					$res['invoiceid'] = '<span class="blue"><a href="buyer_invoice.php?cmd=view&amp;txn='.$res['transactionid'].'">'.$res['final_invoice_id'].'</a></span>';
					$res['createdate'] = date('m-d-Y',strtotime($res['createdate']));
					$res['paiddate'] = date('m-d-Y',strtotime($res['paiddate']));
					$res['coincount'] = $res['tot'];
					$res['shipdate'] = date('m-d-Y',strtotime($res['shipment_date']));
					$res['shipservice'] = fetch_shipper('title',$res['shipper_id']);
					$res['trackno'] = '<a href="https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1='.$res['track_no'].'" target="_blank">'.$res['track_no'].'</a>';
					if($res['shipper_id']==35)
					{
					$res['trackno'] = '<a href="https://www.fedex.com/fedextrack/WTRK/index.html?action=track&trackingnumber='.$res['track_no'].'&cntry_code=us&fdx=1490" target="_blank">'.$res['track_no'].'</a>';
					}
					$purchase_ship[] = $res;
					}
				}
				else
				{				
					$show['no_statement'] = true;
				}
				
				
				$sql1="SELECT c.consignid,date(c.receive_date) AS end_date,c.coins,c.consign_type,m.message,msgcount,cn.coincount
                FROM " . DB_PREFIX . "consignments c
                left join (SELECT * , count( o.message ) AS msgcount from (select * from " . DB_PREFIX . "consignment_messages  order by id desc) o group by consignment_id ) m on m.consignment_id=c.consignid
				left join (select count(*) as coincount,consignid
				from " . DB_PREFIX . "coins group by consignid) cn on cn.consignid=c.consignid
				WHERE c.user_id = '".$_SESSION['ilancedata']['user']['userid']."'									
				ORDER BY c.consignid DESC";
				
				$sql_consign = $ilance->db->query($sql1);
				
				$consign_typess = array(1=>'Certified Coins',2=>'Uncertified Coins',3=>'Certified Currency',4=>'Uncertified Currency',5=>'Other Items',0=>'Coins');
				
				if ($ilance->db->num_rows($sql_consign) > 0)
                {
					while ($res_consign = $ilance->db->fetch_array($sql_consign))
					{
						
						if($res_consign['msgcount']==1)
						{
							$res_consign['fmsg']=1;
							$res_consign['msgcount']='1 Note';
							
							if(!is_null($res_consign['message']))
							{
								
								$res_consign['full_message']=$res_consign['message'];
								$res_consign['message']=strlen($res_consign['message'])>20?substr($res_consign['message'],0,strpos($res_consign['message'], ' ', 20)).'...':$res_consign['message']; 
							}else
							{
								$res_consign['fmsg']=0;
								$res_consign['message']='';
								$res_consign['full_message']='';
								
							}
						
						}
						else if($res_consign['msgcount']>1)
						{
							$res_consign['fmsg']=0;
							$res_consign['msgcount']=$res_consign['msgcount'].' Notes';	
							
							
						}
						else
						{
							$res_consign['fmsg']=0;
							$res_consign['msgcount']='';
							
							
						}
						
						if(($res_consign['coincount'])> 0 )
						{
						$res_consign['details_link']='<a href="'.HTTPS_SERVER.'Consignmentsreceived?cmd=view_detail&consignment='.$res_consign['consignid'].'">Details</a>';
						}
						else
						{
						$res_consign['details_link']='';
						}
						
						
						
						$res_consign['end_date'] = date('m-d-Y',strtotime($res_consign['end_date']));
						$res_consign['consign_type'] = $consign_typess[$res_consign['consign_type']];	
						$consign_receive[] = $res_consign;
					}
				}
		// Iwon End

		//TAMIL FOR BUG 2203 * 2/01/12 * START	
	
		$request_uri_arr=explode("/",$_SERVER['REQUEST_URI']);
		$request_uri= mb_strtolower(end($request_uri_arr));
		echo '<!--'.$request_uri.'-->';
		switch($request_uri)
		{
			case 'track':
				$hv1='class="on"';
				$hv2='class=""';
				$hv3='class=""';
                                 $onload.="toggle_tab('homepagetabs2', 'Purchases_Shipped', 'purchases_ship', 'new3')";
				break;
			case 'consignmentsreceived':
				$hv1='class=""';
				$hv2='class="on"';
				$hv3='class=""';
                                 $onload.="toggle_tab('homepagetabs2', 'Consignments_Received', 'received', 'new3')";
				break;
			case 'consignmentsreturned':
				$hv1='class=""';
				$hv2='class=""';
				$hv3='class="on"';
                                 $onload.="toggle_tab('homepagetabs2', 'Items_Returned', 'returned', 'new3')";
				break;
			default:
				$hv1='class=""';
				$hv2='class=""';
				$hv3='class=""';	
                                 $onload.="toggle_tab('homepagetabs2', 'Purchases_Shipped', 'purchases_ship', 'new3')";		
		}	
		
	
	$pprint_array = array('hv1','hv2','hv3','php_self2','sub','bidsub','servicetabs','producttabs','activebids','awardedbids','archivedbids','invitedbids','expiredbids','retractedbids','productescrow','buynowproductescrow','activerfps','draftrfps','archivedrfps','delistedrfps','pendingrfps','serviceescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpescrow','rfpvisible','countdelisted','prevnext','prevnext2','input_style','redirect','referer','login_include','headinclude','onload','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','inline_script');
		
	//TAMIL FOR BUG 2203 * 2/01/12 * END
	
	$ilance->template->fetch('main', 'main_track.html');
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