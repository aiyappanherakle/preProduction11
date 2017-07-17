<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352							||
|| # This File Created By Herakle Team On Nov 24 For Taking Reports		  # ||
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||
|| # -------------------------------------------------------------------- # ||
|| # Copyright 20002010 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.herakle.com | http://www.ilance.com/eula	| info@ilance.com # ||
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
ini_set('memory_limit', '5024M');
set_time_limit(0);

// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
//print_r($_SESSION['ilancedata']['user']);
//echo DIR_TEMPLATES_ADMINSTUFF;
$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
  
		if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'consignorstatus')
		{
			if($ilance->GPC['filterby']=='username')
			{
				$sql = $ilance->db->query("
					SELECT user_id, username, email
					FROM " . DB_PREFIX . "users
					WHERE  username = '".$ilance->GPC['filtervalue']."'
					");
				$res = $ilance->db->fetch_array($sql);
				$userid = $res['user_id'];
				$user_name = $res['username'];
				$email_id = $res['email'];			
			}		 
			else
			{
				$userid = $ilance->GPC['filtervalue'];
			}
			
			if(!empty($ilance->GPC['end_date']))
			{
				$condition="AND date(c.End_Date) = '".$ilance->GPC["end_date"]."'";
				$end_date = "End Date:".date('d-m-Y',strtotime($ilance->GPC["end_date"]));
				
			}
			else
			{
				$condition='';
				$end_date='';
			}
			
			


			
      $sql="SELECT c.coin_id,c.Title,p.currentprice,c.Alternate_inventory_No,c.pcgs as pcgs,c.Grade,c.Grading_Service,c.Cac,c.Star,c.plus,p.coin_series_unique_no,p.coin_series_denomination_no,count(w.user_id) as watchcount 
			FROM " . DB_PREFIX . "coins c 
			left join " . DB_PREFIX . "projects p on c.coin_id=p.project_id AND p.status='open' AND ((p.filtered_auctiontype = 'regular' AND p.winner_user_id  = '0') OR (p.buynow = '1' AND p.filtered_auctiontype = 'fixed'  AND p.buynow_qty > '0'))
			left join " . DB_PREFIX . "watchlist w on c.coin_id=w.watching_project_id
			left join " . DB_PREFIX . "catalog_coin cc on c.pcgs = cc.PCGS 
			WHERE c.user_id = '".$userid."' 
			$condition
			group by c.coin_id
			ORDER BY  cc.Orderno ,(CASE WHEN (c.pcgs = '6000120' OR c.pcgs = '6000127' OR c.pcgs = '6000128' OR c.pcgs = '6000129') THEN c.title END) ASC,c.grade DESC";

			

			
			$consinorslist = $ilance->db->query($sql);

			$listing_items = '<table border="0"><tr>
			<td width="70px">Seller Name:'.$user_name.'</td>
			<td width="70px">Seller Email:'.$email_id.'</td>
			<td width="50px">'.$end_date.'</td>
			
			</tr>
			</table>
			<table border="1">
			<tr>
			<td>Item ID</td>
			<td>Title</td>
			<td>PCGS</td>
			<td>Current Bid</td><td width="20px">Secret Max Bid</td><td>Alt Inventory</td><td width="20px>APR</td><td>Watchers</td>
			';
			
			
			if($ilance->db->num_rows($consinorslist)>0)
			{
				while($listing = $ilance->db->fetch_array($consinorslist))
				{
					
					$projectid = $listing['coin_id'];
					$projecttitle = $listing['Title'];
					$current_price = $listing['currentprice']; 
					$Alt_inventory_No = $listing['Alternate_inventory_No'];
					if($listing['watchcount']==0)
					{
						$Watchers ="";
					}else
					{
						$Watchers = $listing['watchcount'];
					}

					$sql1="SELECT MAX(maxamount) as high,project_id 
					FROM " . DB_PREFIX . "proxybid pr
					where pr.project_id ='".$projectid."'";
					

					$consinorslist_max = $ilance->db->query($sql1);
					$max_sec_bid = $ilance->db->fetch_array($consinorslist_max);
					
					
					$coin_apr=get_coin_history_price($listing['coin_id'],$listing['pcgs'],$listing['Grade'],$listing['Grading_Service'],$listing['Cac'],$listing['Star'],$listing['plus'],$listing['coin_series_unique_no'],$listing['coin_series_denomination_no']);
					
					
					

					
					$listing_items.= '<tr><td>'.$projectid.'</td><td>'.$projecttitle.'</td><td>'.$listing['pcgs'].'</td>
					<td>'.$current_price.'</td><td>'.$max_sec_bid['high'].'</td><td>'.$Alt_inventory_No.'</td><td>'.$coin_apr.'</td>
					<td>'.$Watchers.'</td>';
					
					
					
				}
			}
			
			
			
			
			$listing_items.='</table>';

			$listing_items.='<br/><br/></table>';
			
			
			
			define('FPDF_FONTPATH','../font/');
			
			require('pdftable_1.9/lib/pdftable.inc.php');
			
			$p = new PDFTable();
			
			$p->AddPage();
			
			$p->setfont('times','',10);
			
			$p->htmltable($listing_items);
			
			$p->output('consignor_status_'.date('Y-m-d h-i-s').'.pdf','D');  
			
			
		}
		

		
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','remote_addr','rid','login_include','headinclude',
			'area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
		
		($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'consignor_status.html', 2);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('reports','statement'));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}



	function get_coin_history_price($coin_id,$pcgs,$grade,$grading_service,$cac,$star,$plus,$seried_id,$denomination_id)
	{
		global $ilance;
	 //except denomination Raw Collections & Lots , World and Ancient Coins
		$excepmt_array=array();
		$excepmt_array[]=30;
		$excepmt_array[]=33;
		$excepmt_array[]=29; //except Error Coins by Bug #8397
		if(in_array($denomination_id,$excepmt_array))
		{
			return '';
		}
		
		$query="SELECT p.project_id,p.date_end,p.grade,p.grading_service,p.currentprice,p.Cac
				FROM " . DB_PREFIX . "projects p
				LEFT JOIN " . DB_PREFIX . "coins c ON c.coin_id = p.project_id
				WHERE p.cid='$pcgs' 
				and p.date_end>= DATE_SUB(NOW(),INTERVAL 2 YEAR)
				and p.status='expired' 
				and p.grade='".$grade."'
				and p.project_title not like '%DETAILS%'
				and (p.haswinner=1 or p.hasbuynowwinner=1)  
				AND c.Plus = '".$plus."'
				order by 
				case when p.grading_service='".$grading_service."' then 1 else 2 end, 
				p.date_end desc, p.currentprice desc limit 5";
		
		$result=$ilance->db->query($query);
		if($ilance->db->num_rows($result))
		{
			while($line=$ilance->db->fetch_array($result))
			{
				//$list[]='<a href="'.HTTP_SERVER.'coins/'.$line['project_id'].'">'.$line['currentprice'].substr($line['grading_service'],0,1).'</a>';

				if($line['Cac'])
					$list[]=$line['currentprice'].substr($line['grading_service'],0,1).'C';
				else	
					$list[]=$line['currentprice'].substr($line['grading_service'],0,1);
			}
			return implode(', ',$list);
		}
	}


	/*======================================================================*\
	|| ####################################################################
	|| # Downloaded: Wed, Jun 2nd, 2010
	|| ####################################################################
	\*======================================================================*/
	?>