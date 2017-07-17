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
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);

$area_title = $phrase['_admin_cp_dashboard'];
$page_title = SITE_NAME . ' - ' . $phrase['_admin_cp_dashboard'];

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'live_item_list')
	{
		
		$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "Live-items-PCGS-$timeStamp";
		header('Content-Type: text/csv; charset=utf-8');
		//$fields = array('ITEM','URL','TITLE','PCGS','CERTIFICATION','ENDDATE');
		$fields = array('CertNo','EndTime','Title','ListingURL');
		
		header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
		$fp = fopen('php://output', 'w');		
		fputcsv($fp, $fields);		
		
		$sql = $ilance->db->query("SELECT p.project_id AS Item, CONCAT(  'http://www.greatcollections.com/Coin/', p.project_id ) AS URL, p.project_title AS TITLE, p.pcgs PCGS,c.Certification_No AS Certification, DATE_FORMAT( date_end,  '%m/%d/%Y %r' ) AS EndDate
									FROM  " . DB_PREFIX . "projects p
									join " . DB_PREFIX . "coins c on c.coin_id=p.project_id
									WHERE  p.status =  'open'
									AND  p.Grading_Service =  'PCGS'
									ORDER BY p.date_end ASC ");
		while($res = $ilance->db->fetch_array($sql))
		{	
			/*$user_detail['ITEM']=trim($res['Item']);
			$user_detail['URL']=$res['URL'];
			$user_detail['TITLE']=$res['TITLE'];			
			$user_detail['PCGS']=$res['PCGS'];			
			$user_detail['CERTIFICATION']=$res['Certification'];
			$user_detail['ENDDATE']=$res['EndDate'];
			*/
			$user_detail['CertNo']=$res['Certification'];
			$user_detail['EndTime']=$res['EndDate'];
			$user_detail['Title']=$res['TITLE'];
			$user_detail['ListingURL']=$res['URL'];
			
			fputcsv($fp, $user_detail);
		}

		exit();
	}
	else if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'live_item_list_pcgs_ngc')
	{
		$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "Live-items-PCGS-NGC-$timeStamp";
		//$fields = array('ITEM','ENDDATE','FILEHASH','TITLE','URL','GRADING','CERTIFICATION','PCGS','GRADE');
		$fields = array('CoinID','EndTime','ImageURL','Title','ListingURL','GradingService','PCGSCertificationNo','PCGSNo','Grade');
		
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
		$fp = fopen('php://output', 'w');				
		fputcsv($fp, $fields);
		
		$r="SELECT p.project_id AS Item, CONCAT(  'http://www.greatcollections.com/Coin/', p.project_id ) AS URL, p.project_title AS TITLE, p.pcgs PCGS,c.Certification_No AS Certification, DATE_FORMAT( date_end,  '%m/%d/%Y %h:%i:%S %p' ) AS EndDate, a.filehash,c.grade,p.Grading_Service
			FROM  " . DB_PREFIX . "projects p
			left join " . DB_PREFIX . "coins c on c.coin_id=p.project_id
			left join ".DB_PREFIX."attachment a on a.project_id=p.project_id and a.attachtype='itemphoto'
			WHERE  p.status =  'open'
			and (p.Grading_service='PCGS' or p.Grading_service='NGC')
			ORDER BY p.date_end ASC ";

		$sql = $ilance->db->query($r);
		
		while($res = $ilance->db->fetch_array($sql))
		{
			
			
			$user_detail['ITEM']=trim($res['Item']);
			$user_detail['ENDDATE']=$res['EndDate'];
			$user_detail['filehash']='http://www.greatcollections.com/image.php?id='.$res['filehash'].'&w=170&h=140';
			$user_detail['TITLE']=$res['TITLE'];
			$user_detail['URL']=$res['URL'];
			$user_detail['Grading']=$res['Grading_Service'];			
			$user_detail['CERTIFICATION']=$res['Certification'];			
			$user_detail['PCGS']=$res['PCGS'];			
			$user_detail['grade']=$res['grade'];
			
			
			fputcsv($fp, explode(',',$user_detail));
            
		}
		
		exit;
       
	}
       
        if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ngc_prices_realized') 
	{
		//error_reporting(E_ALL);
	 
		$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "NGC-Prices-Realized-$timeStamp";
		header('Content-Type: text/csv; charset=utf-8');
		$fields = array('AuctionName','SaleNumber','AuctionDate','AuctionEndDate','AuctionURL','LotNumber','LotURL','Grade','Service','Certification Number','PCGS Coin Number','Plus','Star','Multiple Coins','PricesRealized','Description','ImageUrl1','ImageUrl2','ImageUrl3','ImageUrl4','ImageUrl5');
		
                header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
		
                $fp = fopen('php://output', 'w');
		
                fputcsv($fp, $fields);		
                $from = $ilance->GPC['from_date'];
                $to   =  $ilance->GPC['to_date'];
                if($ilance->GPC['display']=='auctions')
                {
                    $status = "AND p.status = 'expired'";
                    $con="AND p.filtered_auctiontype = 'regular' AND p.haswinner='1'  AND p.winner_user_id = u.user_id";
                }
                else
                {
                    $status = "AND (p.status = 'expired' OR p.status = 'closed')";
                    $con='AND(p.haswinner=1 OR p.hasbuynowwinner=1)';
                }
                                        $sql = "SELECT DATE_FORMAT(p.date_end, '%m/%d/%Y %H:%i') as date_end,
                                        (p.currentprice + p.buyer_fee) as currentprice,
                                        p.project_title,p.project_id,p.Grade,p.Grading_Service,p.pcgs,
                                        CONCAT('GreatCollections Coin Auctions ',DATE_FORMAT(p.date_end, '%m/%d/%Y')) as AuctionName,
                                        CONCAT('http://www.greatcollections.com/Coin/',p.project_id,'/') as new_title,
                                        p.filtered_auctiontype ,p.grade,
                                        c.star,c.plus,a1.filehash as ImageUrl1,
                                        a2.filehash as ImageUrl2,
                                        a3.filehash as ImageUrl3,
                                        a4.filehash as ImageUrl4,
                                        a5.filehash as ImageUrl5
                                         FROM ilance_projects p
                                            left join ilance_users u on  p.winner_user_id = u.user_id 
                                            left join ilance_users us on  p.user_id = us.user_id 
                                            left join ilance_coins c on  p.project_id = c.coin_id
                                            left join ilance_attachment a1 on p.project_id=a1.project_id and a1.filename like '%-1.%'
                                            left join ilance_attachment a2 on p.project_id=a2.project_id and a2.filename like '%-2.%'
                                            left join ilance_attachment a3 on p.project_id=a3.project_id and a3.filename like '%-3.%'
                                            left join ilance_attachment a4 on p.project_id=a4.project_id and a4.filename like '%-4.%'
                                            left join ilance_attachment a5 on p.project_id=a5.project_id and a5.filename like '%-5.%'
                                                WHERE   p.visible = '1'
						$status
						$con
						AND u.isexclude_pricesrealized !=1
                                                 AND us.isexclude_pricesrealized !=1
						AND (date(p.date_end) between '".$from."' and '".$to."')
						AND p.project_id=c.project_id
						AND c.pcgs NOT BETWEEN 6000000 AND 6000500 
						group by p.project_id
						order by p.date_end
						
						";
			
				$sel = $ilance->db->query($sql);
				if($ilance->db->num_rows($sel)>0)
				{
				while($test = $ilance->db->fetch_array($sel))
				{
					
					$data['AuctionName']    =$test['AuctionName'];
					$data['SaleNumber']     =$test['AuctionName'];
					$data['AuctionDate']    =$test['date_end'];
					$data['AuctionEndDate'] =$test['date_end'];
					$data['AuctionURL']     =HTTP_SERVER.'Coin/'.$test['project_id'].'/'.construct_seo_url_name($test['project_title']);
					$data['LotNumber']      =$test['project_id'];
					$data['LotURL']         =$data['AuctionURL'];
					$data['Grade']          =$test['Grade'];
					$data['Service']        =$test['Grading_Service'];
					$data['Certification']  ='';
					$data['PCGS_CoinNumber']=$test['pcgs'];
					$data['Plus']           =$test['plus'];
					$data['Star']           =$test['star'];
					$data['Multiple_Coins'] =strstr($test['project_title'],'Coins)')?'Yes':'No';;
					$data['PricesRealized'] =$test['currentprice'];
					$data['Description']    =$test['project_title'];
						//bug#3725 w=680 and h=420  
					$data['ImageUrl1']      =(strlen($test['ImageUrl1'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=680&h=420&id='.$test['ImageUrl1']:'';
					$data['ImageUrl2']      =(strlen($test['ImageUrl2'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=680&h=420&id='.$test['ImageUrl2']:'';
					$data['ImageUrl3']      =(strlen($test['ImageUrl3'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=680&h=420&id='.$test['ImageUrl3']:'';
					$data['ImageUrl4']      =(strlen($test['ImageUrl4'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=680&h=420&id='.$test['ImageUrl4']:'';
					$data['ImageUrl5']      =(strlen($test['ImageUrl5'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=680&h=420&id='.$test['ImageUrl5']:'';
					
                                        $res[] = $data;
					


    fputcsv($fp, $data);
	

				}
				}
				exit();
	}
        if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ngc_prices_realized_end_date') 
	{
		//error_reporting(E_ALL);
	 
		$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "NGC-Prices-Realized-End-date-$timeStamp";
		header('Content-Type: text/csv; charset=utf-8');
		$fields = array('AuctionName','SaleNumber','AuctionDate','AuctionEndDate','AuctionURL','LotNumber','LotURL','Grade','Service','Certification Number','PCGS Coin Number','Plus','Star','Multiple Coins','PricesRealized','Description','ImageUrl1','ImageUrl2','ImageUrl3','ImageUrl4','ImageUrl5');
		
                header('Content-Disposition: attachment; filename='.$fileName.'.csv');		
		
                $fp = fopen('php://output', 'w');
		
                fputcsv($fp, $fields);		
                
                $to   =  $ilance->GPC['to_date'];
               // $from = last_monday($to);
                if($ilance->GPC['display']=='auctions')
                {
                    $status = "AND p.status = 'open'";
                    $con="AND p.filtered_auctiontype = 'regular' AND p.haswinner='1'  AND p.winner_user_id = u.user_id";
                }
                else
                {
                    $status = "AND (p.status = 'expired' OR p.status = 'closed')";
                    $con='AND(p.haswinner=1 OR p.hasbuynowwinner=1)';
                }
                $status='';
                $con='';
                                         $sql = "SELECT DATE_FORMAT(p.date_end, '%m/%d/%Y %H:%i') as date_end,
                                        (p.currentprice + p.buyer_fee) as currentprice,
                                        p.project_title,p.project_id,p.Grade,p.Grading_Service,p.pcgs,
                                        CONCAT('GreatCollections Coin Auctions ',DATE_FORMAT(p.date_end, '%m/%d/%Y')) as AuctionName,
                                        CONCAT('http://www.greatcollections.com/Coin/',p.project_id,'/') as new_title,
                                        p.filtered_auctiontype ,p.grade,
                                        c.star,c.plus,a1.filehash as ImageUrl1,
                                        a2.filehash as ImageUrl2,
                                        a3.filehash as ImageUrl3,
                                        a4.filehash as ImageUrl4,
                                        a5.filehash as ImageUrl5
                                         FROM ilance_projects p
                                            left join ilance_users u on  p.user_id = u.user_id 
                                            left join ilance_coins c on  p.project_id = c.coin_id
                                            left join ilance_attachment a1 on p.project_id=a1.project_id and a1.filename like '%-1.%'
                                            left join ilance_attachment a2 on p.project_id=a2.project_id and a2.filename like '%-2.%'
                                            left join ilance_attachment a3 on p.project_id=a3.project_id and a3.filename like '%-3.%'
                                            left join ilance_attachment a4 on p.project_id=a4.project_id and a4.filename like '%-4.%'
                                            left join ilance_attachment a5 on p.project_id=a5.project_id and a5.filename like '%-5.%'
                                                WHERE   p.visible = '1'
						$status
						$con
						AND u.isexclude_pricesrealized !=1
						AND date(p.date_end) = '".$to."'
						AND p.project_id=c.project_id
						AND c.pcgs NOT BETWEEN 6000000 AND 6000500 
						group by p.project_id
						order by p.date_end
						
						";
			
				$sel = $ilance->db->query($sql);
				if($ilance->db->num_rows($sel)>0)
				{
				while($test = $ilance->db->fetch_array($sel))
				{
					
					$data['AuctionName']    =$test['AuctionName'];
					$data['SaleNumber']     =$test['AuctionName'];
					$data['AuctionDate']    =$test['date_end'];
					$data['AuctionEndDate'] =$test['date_end'];
					$data['AuctionURL']     =HTTP_SERVER.'Coin/'.$test['project_id'].'/'.construct_seo_url_name($test['project_title']);
					$data['LotNumber']      =$test['project_id'];
					$data['LotURL']         =$data['AuctionURL'];
					$data['Grade']          =$test['Grade'];
					$data['Service']        =$test['Grading_Service'];
					$data['Certification']  ='';
					$data['PCGS_CoinNumber']=$test['pcgs'];
					$data['Plus']           =$test['plus'];
					$data['Star']           =$test['star'];
					$data['Multiple_Coins'] =strstr($test['project_title'],'Coins)')?'Yes':'No';;
					$data['PricesRealized'] =$test['currentprice'];
					$data['Description']    =$test['project_title'];
					$data['ImageUrl1']      =(strlen($test['ImageUrl1'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=170&h=105&id='.$test['ImageUrl1']:'';
					$data['ImageUrl2']      =(strlen($test['ImageUrl2'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=170&h=105&id='.$test['ImageUrl2']:'';
					$data['ImageUrl3']      =(strlen($test['ImageUrl3'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=170&h=105&id='.$test['ImageUrl3']:'';
					$data['ImageUrl4']      =(strlen($test['ImageUrl4'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=170&h=105&id='.$test['ImageUrl4']:'';
					$data['ImageUrl5']      =(strlen($test['ImageUrl5'])>0)?HTTP_SERVER.'/image.php?cmd=thumb&subcmd=itemphoto&w=170&h=105&id='.$test['ImageUrl5']:'';
					
                                        $res[] = $data;
					


    fputcsv($fp, $data);
	

				}
				}
				exit();
	}
	else
	{
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','remote_addr','rid','referfrom','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
         ($apihook = $ilance->api('admincp_subscribers_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'export_live_items_list.html', 2);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
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


function last_monday($anydate)
{
	list($y,$m,$d)=explode("-",$anydate);
	$h = mktime(0, 0, 0, $m, $d, $y);
	$w= date("w", $h) ;
	$rest_sec=6*24*60*60;
	$last_monday=date("Y-m-d",$h-$rest_sec);
	return $last_monday;
}
?>
