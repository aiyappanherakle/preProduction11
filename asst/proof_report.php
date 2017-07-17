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
	'administration',
	'accounting'
);
// #### load required javascript ###############################################
$jsinclude = array(
	'functions',
	'ajax',
	'inline',
	'cron',
	'autocomplete',
	'tabfx',
	'flashfix'
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['buyers'] => $ilcrumbs[$ilpage['buyers']]);

$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];
$navroot = '1';
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{ 	
$from_date='';
$to_date='';
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'proof_report_export') 
	{
	//$from_date=$ilance->GPC['start_date'];
	$to_date=$ilance->GPC['to_date'];
	$from_date=last_monday($to_date);
	$sql="SELECT p.project_id,p.project_title,p.date_end,p.currentprice,p.buyer_fee,ci.invoiceid as childinv,pi.scheduled_date,pi.invoiceid as buyerinvoiceid,pi.status,pi.totalamount,
	buyer.username as buyer_user_name,
	seller.username as seller_user_name
	FROM " . DB_PREFIX . "projects p
	left join " . DB_PREFIX . "users seller on seller.user_id=p.user_id
	left join " . DB_PREFIX . "users buyer on buyer.user_id=p.winner_user_id
	left join ilance_invoices ci on ci.projectid=p.project_id and ci.user_id=p.winner_user_id and ci.isbuyerfee=0
	left join ilance_invoices pi on pi.user_id=p.winner_user_id and pi.combine_project like CONCAT('%',ci.invoiceid, '%')
	WHERE p.filtered_auctiontype='regular' and date(p.date_end) between '".$from_date."' and '".$to_date."' and p.winner_user_id>0";
	
	$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($res)>0)
	{
		while($line=$ilance->db->fetch_array($res))
		{
			$m['project_id']=$line['project_id'];
			$m['project_title']= $line['project_title'];
			$m['type']= 'auction';
			$m['buyerinvoiceid']= $line['buyerinvoiceid']>0?$line['buyerinvoiceid']:'yet to checkout';
			$m['currentprice']= $line['currentprice'];
			$m['buyerid']= $line['buyer_user_name'];
			$m['sellerid']= $line['seller_user_name'];
			$m['solddate']= $line['date_end'];
			$m['enddate']= $line['date_end'];
			$m['invstatus']= $line['status'];
			$m['buyer_fee']= $line['buyer_fee'];
			$m['scheduled_date']= $line['scheduled_date']!='0000-00-00 00:00:00'?$line['scheduled_date']:'yet to checkout';
			
			$data[]=$m;
			
		}
	}
	
	$sql="SELECT c.coin_id,r.actual_end_date,l.last_listed_time,c.Title  FROM " . DB_PREFIX . "coins c 
	left join " . DB_PREFIX . "coin_relist r on r.coin_id=c.coin_id and r.filtered_auctiontype='fixed'
	left join (select coin_id,CASE WHEN count(actual_end_date)>0 THEN max(actual_end_date)   ELSE '0000-00-00 00:00:00' END as last_listed_time from ilance_coin_relist 
	where date(actual_end_date)<'".$to_date."' and DATEDIFF(enddate,startbydate)>1 group by coin_id) l on l.coin_id=c.coin_id
	where c.Buy_it_now>0 and ((date(c.End_date) between '".$from_date."' and '".$to_date."') or (date(r.actual_end_date) between '".$from_date."' and '".$to_date."'))
	group by c.coin_id";
	
	$res = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
	if($ilance->db->num_rows($res)>0)
	{
		while($line=$ilance->db->fetch_array($res))
		{
		  $sql1="SELECT p.project_id,p.amount,p.orderdate,seller.username as seller_user_name,buyer.username as  buyer_user_name,ci.invoiceid as childinv,pi.scheduled_date,pi.invoiceid as buyerinvoiceid,pi.status,pi.totalamount

		 FROM " . DB_PREFIX . "buynow_orders p
		left join " . DB_PREFIX . "users seller on seller.user_id=p.owner_id
		left join " . DB_PREFIX . "users buyer on buyer.user_id=p.buyer_id
		left join ilance_invoices ci on ci.projectid=p.project_id and ci.user_id=p.buyer_id and p.orderid=ci.buynowid and ci.isbuyerfee=0
		left join ilance_invoices pi on pi.user_id=p.buyer_id and pi.combine_project like CONCAT('%',ci.invoiceid, '%')
		 WHERE  p.project_id='".$line['coin_id']."' and date(p.orderdate) between '".$line['last_listed_time']."' and '".$to_date."'";
		
		 
			$res1 = $ilance->db->query($sql1, 0, null, __FILE__, __LINE__);
			if($ilance->db->num_rows($res1)>0)
			{
				while($line1=$ilance->db->fetch_array($res1))
				{
					
					$m['project_id']=$line1['project_id'];
					$m['project_title']= $line['Title'];
					$m['type']= 'buynow';
					$m['buyerinvoiceid']= $line1['buyerinvoiceid']>0?$line1['buyerinvoiceid']:'yet to checkout';
					$m['currentprice']= $line1['amount'];
					$m['buyerid']= $line1['buyer_user_name'];
					$m['sellerid']= $line1['seller_user_name'];
					$m['solddate']= $line1['orderdate'];
					$m['enddate']= $line['actual_end_date'];
					$m['invstatus']= $line1['status'];
					$m['buyer_fee']= 0;
					$m['scheduled_date']= $line1['scheduled_date']!='0000-00-00 00:00:00'?$line1['scheduled_date']:'yet to checkout';
					$data[]=$m;
				}
			}
		}
	}
	
	$headings[0]='ItemId';
	$headings[1]='Title';
	$headings[2]='Type';
	$headings[3]='BuyerInvoiceId';
	$headings[4]='Hammer';
	$headings[5]='BuyerId';
	$headings[6]='SellerId';
	$headings[7]='SoldDate';
	$headings[8]='EndDate';
	$headings[9]='InvoiceStatus';
	$headings[10]='BuyerFee';
	$headings[11]='CheckoutDate';
	
	
	$reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "proving_report-$timeStamp";
	$action = 'csv';
	header("Pragma: cache");
	header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
	header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
	echo $reportoutput;
	die();
	}
	
	$pprint_array = array('from_date','to_date','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer','series_prevnext1');
        
        ($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'proof_report.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_loop('main', array('invoicelist','reportlist','invoicelist1'));
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

function last_monday($anydate)
{
	list($y,$m,$d)=explode("-",$anydate);
	$h = mktime(0, 0, 0, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=6*24*60*60;
	$last_monday=date("Y-m-d",$h-$rest_sec);
	return $last_monday;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/
?>