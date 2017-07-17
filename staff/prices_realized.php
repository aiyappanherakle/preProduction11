<?php

/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 3.2.0 Build 1352							||
|| # This File Created By Herakle Team On Nov 24 For Taking Reports		  # ||
|| # -------------------------------------------------------------------- # ||
|| # Customer License # LuLTJTmo23V1ZvFIM-KH-jOYZjfUFODRG-mPkV-iVWhuOn-b=L  ||
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2010 ILance Inc. All Rights Reserved.                # ||
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

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isstaff'] == '1')
{
	if(isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'prices_realized')
	{
		$html='AuctionName,SaleNumber,AuctionDate,AuctionEndDate,AuctionURL,LotNumber,LotURL,Grade,Service,Certification Number,PCGS Coin Number,Plus,Star,Multiple Coins,PricesRealized,Description,ImageURL1,ImageURL2,ImageURL3,ImageURL4,ImageURL5,';
	

		$html.=LINEBREAK;
		
		
		$sql=$ilance->db->query("SELECT p.project_id,p.project_title,p.date_end,p.currentprice,
							c.Grade,c.Grading_Service,c.Certification_No,c.pcgs,
							c.Plus,c.Star,c.nocoin,a1.filehash as im1,
							a2.filehash as im2,
							a3.filehash as im3,
							a4.filehash as im4,
							a5.filehash as im5
							
							FROM " . DB_PREFIX . "projects as p
							
							LEFT JOIN " . DB_PREFIX . "coins as c ON c.coin_id=p.project_id
							
							LEFT JOIN " . DB_PREFIX . "attachment as a1 ON a1.project_id=p.project_id AND cast(SUBSTR(a1.filename from LOCATE('-',a1.filename)+1 for LOCATE('.',a1.filename)-LOCATE('-',a1.filename)-1) as UNSIGNED) = 1
							
							LEFT JOIN " . DB_PREFIX . "attachment as a2 ON a2.project_id=p.project_id AND 
							cast(SUBSTR(a2.filename from LOCATE('-',a2.filename)+1 for LOCATE('.',a2.filename)-LOCATE('-',a2.filename)-1) as UNSIGNED) = 2
							
							LEFT JOIN " . DB_PREFIX . "attachment as a3 ON a3.project_id=p.project_id AND cast(SUBSTR(a3.filename from LOCATE('-',a3.filename)+1 for LOCATE('.',a3.filename)-LOCATE('-',a3.filename)-1) as UNSIGNED) = 3
							
							LEFT JOIN " . DB_PREFIX . "attachment as a4 ON a4.project_id=p.project_id AND cast(SUBSTR(a4.filename from LOCATE('-',a4.filename)+1 for LOCATE('.',a4.filename)-LOCATE('-',a4.filename)-1) as UNSIGNED) = 4
							
							LEFT JOIN " . DB_PREFIX . "attachment as a5 ON a5.project_id=p.project_id AND cast(SUBSTR(a5.filename from LOCATE('-',a5.filename)+1 for LOCATE('.',a5.filename)-LOCATE('-',a5.filename)-1) as UNSIGNED) = 5
							
							WHERE p.status='expired'
							ORDER BY p.project_id							
							
							");
						
		while($res=$ilance->db->fetch_array($sql))
		{
			$html.= 'GreatCollections Coin Auctions'.',';
			$html.= trim(date("m/d/Y", strtotime($res['date_end']))).' Auctions'.',';
			$html.= trim(date("m/d/Y", strtotime($res['date_end']))).',';
			$html.= trim(date("m/d/Y", strtotime($res['date_end']))).',';
			$html.= HTTP_SERVER.'Coin/'.$res['project_id'].'/'.construct_seo_url_name($res['project_title']).',';
			$html.= $res['project_id'].',';
			$html.= HTTP_SERVER.'Coin/'.$res['project_id'].'/'.construct_seo_url_name($res['project_title']).',';
			$html.= $res['Grade'].',';
			$html.= $res['Grading_Service'].',';
			$html.= $res['Certification_No'].',';
			$html.= $res['pcgs'].',';
			$html.= ($res['Plus']=='1')?'TRUE,':'FALSE,';
			$html.= ($res['Star']=='1')?'TRUE,':'FALSE,';
			$html.= ($res['nocoin'] >1)?'Yes,':'No,';
			$html.= $res['currentprice'].',';
			$html.= str_replace(","," ",$res['project_title']).',';
			$html.=($res['im1']=='')?' ,':HTTP_SERVER.'image.php?id='.$res['im1'].',';
			$html.=($res['im2']=='')?' ,':HTTP_SERVER.'image.php?id='.$res['im2'].',';
			$html.=($res['im3']=='')?' ,':HTTP_SERVER.'image.php?id='.$res['im3'].',';
			$html.=($res['im4']=='')?' ,':HTTP_SERVER.'image.php?id='.$res['im4'].',';
			$html.=($res['im5']=='')?' ,':HTTP_SERVER.'image.php?id='.$res['im5'].',';
			
			$html.=LINEBREAK;
			
		}
		
		
		
		$timeStamp = date("Y-m-d-H-i-s");
		$fileName = "PricesRealized-$timeStamp";
		$action = 'csv';
		
		
		if ($action == 'csv')
		{
			header("Pragma: cache");
			header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
			header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
			echo $html;
			die();
		}
	}
	else
	{
		$pprint_array = array('buildversion','ilanceversion','login_include_admin','remote_addr','rid','referfrom','login_include','headinclude','area_title','page_title','site_name','https_server','http_server','lanceads_header','lanceads_footer');
        
        ($apihook = $ilance->api('admincp_subscribers_end')) ? eval($apihook) : false;
	
		$ilance->template->fetch('main', 'prices_realized.html', 2);
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

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Wed, Jun 2nd, 2010
|| ####################################################################
\*======================================================================*/

?>
