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
	'autocomplete'
        
	
);

// #### setup script location ##################################################


// #### require backend ########################################################
require_once('./functions/config.php');			
//////////************** COINS ONLY **********************//////////

if(isset($ilance->GPC['id']) AND ($ilance->GPC['id']> 0))
{

	$date=str_replace(' ','T',DATETIME24H).'-07:00';
header("Content-type: text/xml; charset=utf-8");
	echo '<?xml version="1.0" encoding="UTF-8"?>
	<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

	$result=$ilance->db->query("SELECT * FROM ".DB_PREFIX."projects WHERE coin_series_denomination_no='".$ilance->GPC['id']."' ");

	while($row=$ilance->db->fetch_array($result))
	{			
		echo '<url>';				
		echo '<loc>';
		echo HTTP_SERVER.'Coin/'.$row['project_id'].'/'.construct_seo_url_name($row['project_title']);
		echo '</loc>';			
		echo '<lastmod>'.$date.'</lastmod>';
		echo '<changefreq>weekly</changefreq><priority>0.8</priority>';			
		echo '</url>';		

	}
	echo '</urlset>';
}



?>