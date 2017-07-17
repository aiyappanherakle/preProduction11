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
$date=str_replace(' ','T',DATETIME24H).'-07:00';
header("Content-type: text/xml; charset=utf-8");
echo'<?xml version="1.0" encoding="UTF-8"?>';

echo'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

	echo'<sitemap>';

	echo'<loc>'.HTTP_SERVER.'sitemap_except_coins.php'.'</loc>';

	echo'<lastmod>'.$date.'</lastmod>';

	echo'</sitemap>';

  

   $result=$ilance->db->query("SELECT * FROM ".DB_PREFIX."catalog_toplevel ");

	while($row=$ilance->db->fetch_array($result))
	{		
		echo'<sitemap>';
		echo'<loc>'.HTTP_SERVER.'sitemap_coin.php?id='.$row['denomination_unique_no'].'</loc>';
		echo'<lastmod>'.$date.'</lastmod>';
		echo'</sitemap>';
	}

echo'</sitemapindex>';


?>